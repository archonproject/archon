<?php

/**
 * Description of LiveSession
 *
 * @author Paul Sorensen
 */
class LiveSession extends Session
{

   function __construct()
   {   
      ini_set('session.auto_start', '0');
      ini_set('session.save_handler', 'files');
      ini_set('url_rewriter.tags', '');
      ini_set('session.use_trans_sid', '0');
      ini_set('session.use_only_cookies', '1');
      ini_set('session.gc_probability', '1');
      ini_set('session.gc_divisor', '10');

      if(ini_get(session.gc_maxlifetime) < SESSION_EXPIRATION)
      {
         ini_set('session.gc_maxlifetime', SESSION_EXPIRATION);
      }


      $this->CookiePath = preg_replace('/[\w.]+php/u', '', $_SERVER['SCRIPT_NAME']);


      if(session_save_path())
      {
         strstr(strtoupper(substr($_SERVER["OS"], 0, 3)), "WIN") ? $sep = "\\" : $sep = "/";
         $sessdir = session_save_path().$sep."archon_sessions";

         if (!@is_dir($sessdir))
         {
            @mkdir($sessdir, 0700);
         }
         if(@is_dir($sessdir))
         {
            session_save_path($sessdir);
         }
      }

      session_name('archon');

      if(isset($_COOKIE[session_name()]))
      {
         session_id($_COOKIE[session_name()]);
      }

      $this->_setCookieParams();

      session_cache_limiter('nocache');
      session_start();

      $this->Hash = session_id();

      $this->_garbageCollection();

      // this is temporary
      if(defined('PACKAGE_COLLECTIONS'))
      {
         $this->ResearchCart = New ResearchCart();
      }

      return $this;
   }




   function __destruct()
   {
      $this->close();
   }




   private function _dbLoad()
   {
      global $_ARCHON;

      // this should ALWAYS be set by __construct()
      if(!isset($this->Hash))
      {
         return false;
      }

      $prep = $_ARCHON->mdb2->prepare('SELECT * FROM tblCore_Sessions WHERE Hash = ?', array('text'), MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->Hash);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      if(!$row['ID'])
      {
         return false;
      }

      $row = array_change_key_case($row);
      $arrVariables = get_object_vars($this);
      foreach($arrVariables as $name => $defaultvalue)
      {
         if(isset($row[strtolower($name)]))
         {
            $this->$name = $row[strtolower($name)];
         }
      }

      if(!$this->User && $this->UserID)
      {
         if(!$this->dbLoadUser())
         {
            return false;
         }
      }

      return true;
   }




   private function _setCookieParams()
   {
      $cookie = session_get_cookie_params();
      if($this->isSecureConnection())
      {
         $cookie['secure'] = true;
      }

      if(isset($_COOKIE[session_name().'_persistent']) && $_COOKIE[session_name().'_persistent'] == 1)
      {
         $cookie['lifetime'] = COOKIE_EXPIRATION;
      }

      session_set_cookie_params($cookie['lifetime'], $this->CookiePath, $cookie['domain'], $cookie['secure']);
   }




   private function _garbageCollection()
   {
      srand(time());
      //gc_probability/gc_divisor
      if ((rand() % 10) < 1)
      {
         $this->_clearExpiredSessions();
      }
   }




   private function _clearExpiredSessions()
   {
      global $_ARCHON;

      $prep = $_ARCHON->mdb2->prepare('DELETE FROM tblCore_Sessions WHERE Expires <= ?', array('integer'), MDB2_PREPARE_MANIP);
      $affected = $prep->execute(time());
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();
   }




   public function dbStore($Persistent)
   {

      global $_ARCHON;

      if(!isset($this->User) || ($this->UserID <= 0 && $this->UserID != -1) || !isset($this->Hash))
      {
         return false;
      }

      $Persistent = ($this->UserID != -1) ? $Persistent : 0;

      $this->Persistent = $Persistent ? 1 : 0;

      $this->Expires = ($this->Persistent) ? time() + COOKIE_EXPIRATION : time() + SESSION_EXPIRATION;

      $requireSecureConnection = ($_ARCHON->config->ForceHTTPS && $this->User->IsAdminUser);

      $prep = $_ARCHON->mdb2->prepare('DELETE FROM tblCore_Sessions WHERE Hash = ?', 'text', MDB2_PREPARE_MANIP);
      $affected = $prep->execute($this->Hash);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      $prep = $_ARCHON->mdb2->prepare('INSERT INTO tblCore_Sessions (Hash, UserID, RemoteHost, Expires, Persistent, SecureConnection) VALUES (?, ?, ?, ?, ?, ?)', array('text', 'integer', 'text', 'integer', 'boolean', 'boolean'), MDB2_PREPARE_MANIP);
      $affected = $prep->execute(array($this->Hash, $this->UserID, $_SERVER['REMOTE_ADDR'], $this->Expires, $this->Persistent, $requireSecureConnection));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();


      return true;
   }




   public function close()
   {
      session_write_close();
   }




   /**
    * Destroys live session
    *
    * @return boolean
    */
   public function destroy()
   {
      global $_ARCHON;

      $prep = $_ARCHON->mdb2->prepare('DELETE FROM tblCore_Sessions WHERE Hash = ?', 'text', MDB2_PREPARE_MANIP);
      $affected = $prep->execute($this->Hash);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      $_SESSION = array();

      if(isset($_COOKIE[session_name()]))
      {
         setcookie(session_name(), '', time()-86400, $this->CookiePath);
      }
      if(isset($_COOKIE[session_name().'_persistent']))
      {
         setcookie(session_name().'_persistent', '', time()-86400, $this->CookiePath);
      }


      $this->ID = 0;

      session_destroy();

      return true;
   }







   /**
    * Returns language id according to preference of current Archon user and
    * the default language in the configuration.
    *
    */
   public function getLanguageID()
   {
      if($this->User->LanguageID)
      {
         $LanguageID = $this->User->LanguageID;
      }
      elseif($this->getRemoteVariable('LanguageID'))
      {
         $LanguageID = $this->getRemoteVariable('LanguageID');
      }
      else
      {
         $LanguageID = CONFIG_CORE_DEFAULT_LANGUAGE;
      }

      return $LanguageID;
   }





   /**
    * Returns a remote variable according to Name
    *
    * @param string $Name
    * @return mixed
    */
   public function getRemoteVariable($Name)
   {
      return isset($_SESSION["Archon_$Name"]) ? $_SESSION["Archon_$Name"] : $_COOKIE["Archon_$Name"];
   }




   /**
    * Returns a $_SESSION variable according to Name
    *
    * This is for session data that should be strictly private
    * instead of using the RemoteVariable methods
    *
    * Also, headers_sent() isn't checked so this will throw
    * an error if used incorrectly
    *
    * @param string $Name
    * @return mixed
    */
   public function getSessionVariable($Name)
   {
      return $_SESSION["Archon_{$Name}"];
   }




   /**
    * Sets a $_SESSION variable to Value
    *
    * This is for session data that should be strictly private
    * instead of using the RemoteVariable methods
    *
    * @param string $Name
    * @param string $Value
    */
   public function setSessionVariable($Name, $Value)
   {
      $_SESSION["Archon_{$Name}"] = $Value;
   }




   /**
    * Checks to see if current session is over a secure connection
    *
    * @return boolean
    */
   public function isSecureConnection()
   {
      if($_SERVER['HTTPS'] == 1 || $_SERVER['HTTPS'] == 'on' || $_SERVER['SERVER_PORT'] == 443)
      {
         return true;
      }
      else
      {
         return false;
      }
   }




   /**
    * Checks to see if current session is over a secure connection
    *
    * @return boolean
    */
   public function requireSecureConnection()
   {
      global $_ARCHON;

      if($this->isSecureConnection())
      {
         return true;
      }
      elseif(!$this->Hash)
      {
         return false;
      }

      $prep = $_ARCHON->mdb2->prepare('UPDATE tblCore_Sessions SET SecureConnection = 1 WHERE Hash = ?', 'text', MDB2_PREPARE_MANIP);
      $affected = $prep->execute($this->Hash);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      return $this->verify();
   }




   /**
    * Sets a remote variable to Value
    *
    * @param string $Name
    * @param string $Value
    * @return boolean
    */
   public function setRemoteVariable($Name, $Value, $SetCookie = false)
   {
      if(headers_sent())
      {
         return false;
      }

      if($this->Persistent || $SetCookie)
      {
         setcookie("Archon_$Name", $Value, $this->Expires, $this->CookiePath);
         $_COOKIE["Archon_$Name"] = $Value;
      }

      $_SESSION["Archon_$Name"] = $Value;

      return true;
   }




   /**
    * Unsets a remote variable
    *
    * @param string $Name
    * @return boolean
    */
   public function unsetRemoteVariable($Name, $UnsetCookie = false)
   {
      if(headers_sent())
      {
         return false;
      }

      if($this->Persistent || $UnsetCookie)
      {
         setcookie("Archon_$Name", '', time()-86400, $this->CookiePath);
         unset($_COOKIE["Archon_$Name"]);
      }

      unset($_SESSION["Archon_$Name"]);

      return true;
   }




   /**
    * Unsets all remote variables used to customize user interface
    *
    * @param string $Name
    * @return boolean
    */
   public function unsetAllRemoteVariables($UnsetCookie = false)
   {
      $returnVal = true;

      $returnVal = $returnVal && $this->unsetRemoteVariable('LanguageID', $UnsetCookie);
      $returnVal = $returnVal && $this->unsetRemoteVariable('Theme', $UnsetCookie);
      $returnVal = $returnVal && $this->unsetRemoteVariable('AdminTheme', $UnsetCookie);
      $returnVal = $returnVal && $this->unsetRemoteVariable('RepositoryID', $UnsetCookie);

      return $returnVal;
   }




   /**
    * Verifies live session
    *
    * @return boolean
    */
    public function verify()
    {
        global $_ARCHON;

        if(!$this->_dbLoad())
        {
            return false;
        }

        if($this->Expires <= time())
        {
            return false;
        }

        if($this->RemoteHost != $_SERVER['REMOTE_ADDR'])
        {
            return false;
        }

        $this->Expires = ($this->Persistent) ? time() + COOKIE_EXPIRATION : time() + SESSION_EXPIRATION;

        if($this->SecureConnection && !$this->isSecureConnection())
        {
            die('<html><body onLoad="location.href=\'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .'\';"></body></html>');
            //header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }

        // this will be called if user setting has changed mid-session
        if($_ARCHON->config->ForceHTTPS && $this->User->IsAdminUser && !$this->SecureConnection)
        {
            $this->requireSecureConnection();
        }


        // do we want to update every time? we might want to change this to not update the expiration
        $affected = $_ARCHON->mdb2->exec("UPDATE tblCore_Sessions SET Expires = {$this->Expires} WHERE ID = {$this->ID}");
        if(PEAR::isError($affected))
        {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        if($this->Persistent)
        {
            setcookie(session_name().'_persistent', 1, $this->Expires, $this->CookiePath);
        }
        else
        {
            setcookie(session_name().'_persistent', 0, time()-86400, $this->CookiePath);
        }

        return true;
    }


    public function verifySession($hash)
    {
        if(!$hash)
        {
            return false;
        }
        global $_ARCHON;

        $prep = $_ARCHON->mdb2->prepare('SELECT * FROM tblCore_Sessions WHERE Hash = ?', array('text'), MDB2_PREPARE_RESULT);
        $result = $prep->execute($hash);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        $row = $result->fetchRow();
        $result->free();
        $prep->free();

        if(!$row['ID'])
        {
            return false;
        }

        $row = array_change_key_case($row);
        $arrVariables = get_object_vars($this);
        foreach($arrVariables as $name => $defaultvalue)
        {
            if(isset($row[strtolower($name)]))
            {
                $this->$name = $row[strtolower($name)];
            }
        }

        if($this->Expires <= time())
        {
            return false;
        }

        if($this->RemoteHost != $_SERVER['REMOTE_ADDR'])
        {
            return false;
        }

        if($this->Persistent)
        {
            $this->Expires = time() + COOKIE_EXPIRATION;
        }
        else
        {
            $this->Expires = time() + SESSION_EXPIRATION;
        }

        if(!$this->User && $this->UserID)
        {
            $this->User = New User2x($this->UserID);
            $this->User->dbLoad();
        }

        $prep = $_ARCHON->mdb2->prepare('UPDATE tblCore_Sessions SET Expires = ? WHERE ID = ?', array('integer', 'integer'), MDB2_PREPARE_MANIP);
        $affected = $prep->execute(array($this->Expires, $this->ID));
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }
        $prep->free();


        return true;
    }




        public function dbLoad()
   {
      // stub to remove inherited functionality that is unwanted
   }




   public function dbDelete()
   {
      // stub to remove inherited functionality that is unwanted
   }




   private $Hash = NULL;

   private $CookiePath = NULL;

}
?>
