<?php
abstract class ArchonObject
{
   public function callOverridden()
   {
      global $_ARCHON;

      $methodInfo = end($_ARCHON->Callstack);
      $method = $methodInfo->Method;
      $args = func_get_args();
      $MixinClass = prev($methodInfo->Classes);

      // Simulate mixing after.
      if($_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->MixOrder == MIX_AFTER)
      {
         $prevresult = call_user_func_array(array($this, 'callOverridden'), $args);
      }

      $closure = $this->__getMixinClosure($MixinClass, $method);
      $result = call_user_func_array($closure->bindTo($this), $args);

      // Simulate mixing before.
      if($_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->MixOrder == MIX_BEFORE)
      {
         $nextresult = call_user_func_array(array($this, 'callOverridden'), $args);
      }

      // Use callback if mixing before and after.
      if(isset($prevresult) && $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback)
      {
         $Callback = $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback;

         $result = $Callback($prevresult, $result);
      }
      elseif(isset($nextresult) && $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback)
      {
         $Callback = $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback;

         $result = $Callback($result, $nextresult);
      }

      next($methodInfo->Classes);

      return $result;
   }
    Public function bbcode_to_html($bbtext){
        $bbtags = array(

            //'[b]' => "<span style='font-weight:bold'>","[\/b]" => "<\/span>",
            //'[i]' => "<span style='font-style:italic'>","[\/i]" => "<\/span>",
            //'[u]' => "<span style='text-decoration:underline'>","[\/u]" => "<\/span>",
            //'[sup]'=> "<span style='vertical-align:super;font-size:.8em'>","[\/sup]"=>"<\/span>",
            //'[sub]'=> "<span style='vertical-align:sub;font-size:.8em'>","[\/sub]"=>"<\/span>",

			'[b]' => "<emph render='bold'>","[\/b]" => "</emph>",
            '[i]' => "<emph render='italic'>","[\/i]" => "</emph>",
            '[u]' => "<emph render='underline'>","[\/u]" => "</emph>",
            '[sup]'=> "<emph render='super'>","[\/sup]"=>"</emph>",
            '[sub]'=> "<emph render='sub'>","[\/sub]"=>"</emph>",

        );

        $bbtext = str_ireplace(array_keys($bbtags), array_values($bbtags), $bbtext);

       $bbextended = array(


           "/\[url=(http:\\\\\/\\\\\/.*?)\](.*?)\[\\\\\/url\]/i" => "<extref href='$1'>$2<\\\\/extref>",
           "/\[url=(mailto:.*?)\](.*?)\[\\\\\/url\]/i" => "<extref='mailto:$1'>$2<\\\\/a>",
           "/\[email=(.*?)\](.*?)\[\\\\\/email\]/i" => "<a href='$1'>$2<\\\\/a>",
           "/\[mail=(.*?)\](.*?)\[\/mail\\\\\]/i" => "<a href='mailto:$1'>$2<\\\\/a>",

        );

        foreach($bbextended as $match=>$replacement){
            //echo $match ."\n";
            $bbtext = preg_replace($match, $replacement, $bbtext);
        }
        return $bbtext;
    }



   /**
    * getString escapes any special XML characters unless EscapeXML is set to false.
    *
    * @param string $Variable
    * @return string
    */
   public function getString($Variable, $MaxLength = 0, $HTMLLineBreaks = true, $DecodeBB = NULL)
   {
      global $_ARCHON;

      if(!isset($DecodeBB))
      {
         $DecodeBB = $_ARCHON->PublicInterface ? true : false;
      }

      $String = $this->$Variable;

      if($_ARCHON->db->ServerType == 'MSSQL')
      {
         $String = encoding_convert_encoding($String, 'UTF-8', 'ISO-8859-1');
      }

      $String = trim($String);

      if ($MaxLength != 0)
      {
         $String = caplength($String, $MaxLength);
      }

      if(CONFIG_CORE_ESCAPE_XML)
      {
         if($DecodeBB)
         {
            $String = encode($String, ENCODE_BBCODE);
         }
         else
         {
            $String = $HTMLLineBreaks ? ptag(encode($String, ENCODE_HTML)) : encode($String, ENCODE_HTML);
         }
      }


      return $String;
   }




   /**
    * Generic constructor for ArchonObjects
    *
    * If an integer is passed in, object will be constructed with the integer in its ID field.
    *
    * If an array if passed in, object will be constructed by filling in instance variables
    * with values from the array where the instance variable name and array key match.
    *
    * Lowercase versions of instance variable names will be references to uppercase versions.
    *
    * @param mixed $ID_or_Row
    */
   public function __construct($ID_or_Row = 0)
   {
      global $_ARCHON;

      //echo("Constructing " . get_class($this) . "<br>\n");

      if(!$_ARCHON->Includes[get_class($this)]->Constructed)
      {
         $_ARCHON->Includes[get_class($this)]->Constructed = true;

         if(!empty($_ARCHON->Includes[get_class($this)]->FilesAndMixinClassNames))
         {
            foreach($_ARCHON->Includes[get_class($this)]->FilesAndMixinClassNames as $FileOrMixinClassName)
            {
               if($FileOrMixinClassName->FileName)
               {

                  $cwd = getcwd();
                  chdir($FileOrMixinClassName->FileDirectory);
                  require_once($FileOrMixinClassName->FileName);
                  chdir($cwd);
               }
               else
               {
                  $_ARCHON->mixClasses(get_class($this), $FileOrMixinClassName->MixinClassName);
               }
            }
         }
      }

      $isRow = is_array($ID_or_Row);

      if($isRow)
      {
         $ID_or_Row = array_change_key_case($ID_or_Row);
      }
      else
      {
         $this->ID = $ID_or_Row;
      }

      if(!empty($_ARCHON->Mixins[get_class($this)]->Variables))
      {
         foreach($_ARCHON->Mixins[get_class($this)]->Variables as $VariableName => $DefaultValue)
         {
            if($isRow && isset($ID_or_Row[strtolower($VariableName)]))
            {
               $this->$VariableName = $ID_or_Row[strtolower($VariableName)];
            }
            elseif(encoding_strtoupper($VariableName) != 'ID' && !isset($this->$VariableName))
            {
               $this->$VariableName = $DefaultValue;
            }
         }
      }

      if(!empty($_ARCHON->Mixins[get_class($this)]->Methods['construct']->Classes))
      {
         $this->__call('construct', $ID_or_Row);
      }
   }





   /**
    * Calls all mixed in functions for ArchonObject
    *
    * @param string $method
    * @param mixed[] $args
    * @return mixed
    */
   public function __call($method, $args)
   {
      global $_ARCHON;

      $args = is_array($args) ? $args : array($args);

      if(!empty($_ARCHON->Mixins[get_class($this)]->Methods[$method]->Classes))
      {
         isset($stackmember) or
            $stackmember = new stdClass();
         $stackmember->Method = $method;
         $stackmember->Classes = $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Classes;
         $_ARCHON->Callstack[] = $stackmember;
         $MixinClass = end(end($_ARCHON->Callstack)->Classes);

         // Simulate mixing after.
         if($_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->MixOrder == MIX_AFTER)
         {
            $prevresult = call_user_func_array(array($this, 'callOverridden'), $args);
         }

         $closure = $this->__getMixinClosure($MixinClass, $method);
         $result = call_user_func_array($closure->bindTo($this), $args);

         // Simulate mixing before.
         if($_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->MixOrder == MIX_BEFORE)
         {
            $nextresult = call_user_func_array(array($this, 'callOverridden'), $args);
         }

         // Use callback if mixing before and after.
         if(isset($prevresult) && $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback)
         {
            $Callback = $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback;

            $result = $Callback($prevresult, $result);
         }
         elseif(isset($nextresult) && $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback)
         {
            $Callback = $_ARCHON->Mixins[get_class($this)]->Methods[$method]->Parameters[$MixinClass]->Callback;

            $result = $Callback($result, $nextresult);
         }

         array_pop($_ARCHON->Callstack);

         return $result;
      }
      else
      {
         $backtrace = debug_backtrace();
         $message =
            'Warning: Call to undefined function ' . get_class($this) . '::' . $method .
            ' in ' . $backtrace[1]['file'] . ' on line ' . $backtrace[1]['line'];
         error_log($message);
         return NULL;
      }
   }





   /**
    * Returns ArchonObject as a string
    *
    * @return string
    */
   public function __toString()
   {
      global $_ARCHON;

      if($_ARCHON->methodExists($this, 'toString'))
      {
         return $this->toString();
      }
      else
      {
         return get_class($this);
      }
   }


   private function __getMixinClosure($MixinClass, $mixinMethod) {
      $method = new ReflectionMethod($MixinClass, $mixinMethod);
      $start = $method->getStartLine() - 1;
      $length = $method->getEndLine() - $start;

      $source = file($method->getFileName());
      $method_body = implode("", array_slice($source, $start, $length));
      $method_body = preg_replace("/$mixinMethod/", "", $method_body, 1);
      $method_body = preg_replace("/public/", "", $method_body, 1);
      eval('$closure = '.$method_body.';');

      return $closure;
   }

}
?>
