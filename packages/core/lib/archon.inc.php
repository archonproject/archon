<?php

abstract class Core_Archon
{

   /**
    * Adds a database import utility
    *
    * @param string $UtilityName
    * @param string $Description
    * @param integer $PackageID
    * @param string $UtilityCode
    * @param string $PackageVersion
    * @param string[] $Extensions
    * @param boolean $InputFile[optional]
    *
    * @return boolean
    */
   public function addDatabaseImportUtility($PackageID, $UtilityCode, $PackageVersion, $Extensions, $InputFile = true)
   {
      if(!$UtilityCode)
      {
         $this->declareError("Could not add DatabaseImportUtility: Utility Code not defined.");
         return false;
      }
      elseif(!$this->Packages[$PackageID])
      {
         $this->declareError("Could not add DatabaseImportUtility: Package $PackageID is not installed.");
         return false;
      }
      elseif(version_compare($this->Packages[$PackageID]->DBVersion, $PackageVersion) != 0)
      {
         $this->declareError("Could not add DatabaseImportUtility: Package {$this->Packages[$PackageID]->APRCode} version $PackageVersion must be installed (installed version is {$this->Packages[$Package]->DBVersion}).");
         return false;
      }

      isset($this->db->ImportUtilities[$PackageID][$UtilityCode]) or
         $this->db->ImportUtilities[$PackageID][$UtilityCode] = new stdClass();

      if(file_exists("packages/{$this->Packages[$PackageID]->APRCode}/db/interfaces/import-{$UtilityCode}-interface.inc.php"))
      {
         $this->db->ImportUtilities[$PackageID][$UtilityCode]->InterfaceFile = "packages/{$this->Packages[$PackageID]->APRCode}/db/interfaces/import-{$UtilityCode}-interface.inc.php";
      }

      $this->db->ImportUtilities[$PackageID][$UtilityCode]->Extensions = $Extensions;
      $this->db->ImportUtilities[$PackageID][$UtilityCode]->InputFile = $InputFile;

      return true;
   }

   public function addDatabaseExportUtility($PackageID, $UtilityCode, $PackageVersion)
   {
      if(!$UtilityCode)
      {
         $this->declareError("Could not add DatabaseExportUtility: Utility Code not defined.");
         return false;
      }
      elseif(!$this->Packages[$PackageID])
      {
         $this->declareError("Could not add DatabaseExportUtility: Package $PackageID is not installed.");
         return false;
      }
      elseif(version_compare($this->Packages[$PackageID]->DBVersion, $PackageVersion) != 0)
      {
         $this->declareError("Could not add DatabaseExportUtility: Package {$this->Packages[$PackageID]->APRCode} version $PackageVersion must be installed (installed version is {$this->Packages[$Package]->DBVersion}).");
         return false;
      }

      isset($this->db->ExportUtilities[$PackageID][$UtilityCode]) or
         $this->db->ExportUtilities[$PackageID][$UtilityCode] = new stdClass();

      if(file_exists("packages/{$this->Packages[$PackageID]->APRCode}/db/interfaces/export-{$UtilityCode}-interface.inc.php"))
      {
         $this->db->ExportUtilities[$PackageID][$UtilityCode]->InterfaceFile = "packages/{$this->Packages[$PackageID]->APRCode}/db/interfaces/export-{$UtilityCode}-interface.inc.php";
      }
      else
      {
         $this->db->ExportUtilities[$PackageID][$UtilityCode]->InterfaceFile = false;
      }

      return true;
   }

   /**
    * Adds a package dependency
    *
    * First two parameters are the APRCodes of the Packages
    *
    * @param string $Package
    * @param string $DependsUpon
    * @param string $DependsUponVersion
    */
   public function addPackageDependency($Package, $DependsUpon, $DependsUponVersion = 0.01)
   {

      $this->Packages[$Package]->DependsUpon[$DependsUpon] = $DependsUponVersion;

      if($this->Packages[$DependsUpon])
      {
         $this->Packages[$DependsUpon]->DependedUponBy[$Package] = true;
      }

      if(!$this->Packages[$Package] || !$this->Packages[$DependsUpon] || version_compare($this->Packages[$Package]->DBVersion, $DependsUponVersion) == -1)
      {
         return false;
      }
   }

   /**
    * Adds a package enhancement
    *
    * First two parameters are the APRCodes of the Packages
    *
    * @param string $Package
    * @param string $Enhances
    * @param string $EnhancesVersion
    */
   public function addPackageEnhancement($Package, $Enhances, $EnhancesVersion = 0.01)
   {
      $this->Packages[$Package]->Enhances[$Enhances] = $EnhancesVersion;

      if($this->Packages[$Enhances])
      {
         $this->Packages[$Enhances]->EnhancedBy[$Package] = true;
      }

      if(!$this->Packages[$Package] || !$this->Packages[$Enhances] || version_compare($this->Packages[$Package]->DBVersion, $EnhancesVersion) == -1)
      {
         return false;
      }
   }

   /**
    * Adds a search function for use in Archon's public search page
    *
    * @param string $FunctionName
    * @param integer $DisplayOrder
    * @return boolean
    */
   public function addPublicSearchFunction($FunctionName, $DisplayOrder = 0)
   {
      if(!function_exists($FunctionName))
      {
         $this->declareError("Could not add PublicSearchFunction: Function $FunctionName does not exist.");
         return false;
      }
      elseif(!$this->PublicInterface)
      {
         $this->declareError("Could not add PublicSearchFunction: The PublicInterface is not active.");
         return false;
      }

      $this->PublicInterface->PublicSearchFunctions[$FunctionName]->FunctionName = $FunctionName;
      $this->PublicInterface->PublicSearchFunctions[$FunctionName]->DisplayOrder = $DisplayOrder;

      return true;
   }

   /**
    * Calls the object method specified in $strObjectMethod
    * on every object in $arrObjects with the parameters
    * specified in $arrParameters.  Note that if the method
    * does not exist for any of the objects, the object will
    * be ignored, and the next iteration will begin.  Each object method's
    * return value is passed as the 2nd argument to the
    * callback function specified in $callbackRetval.
    * The callback function's first argument is the return
    * value of the callback function from the previous
    * iteration (the base case value is specified by $mixedBaseRetval).
    * If $boolReturnOnNotBaseRetval is true, the function will return
    * as soon as the callback function returns a value not equal
    * to $mixedBaseRetval.
    * Otherwise, the object method will be called on
    * all objects in $arrObjects regardless of the callback
    * function's return value.  If $strObjectProperty is set,
    * the object method will be called on the specified
    * property of the object (which also must be an object),
    * rather than the object itself.  All objects which
    * were successfully called will be stored in $arrCalledObjects.
    * This function will return whatever the callback function
    * returns in the final iteration of the loop.
    *
    * @param Object[] $arrObjects
    * @param string $strObjectMethod
    * @param mixed[] $arrParameters[optional]
    * @param callback $callbackRetval[optional]
    * @param mixed $mixedBaseRetval[optional]
    * @param boolean $boolReturnOnNotBaseRetval[optional]
    * @param boolean $InputFile[optional]
    * @param string $strObjectProperty[optional]
    * @param Object[] $arrCalledObjects[optional]
    *
    * @return mixed
    */
   public function callObjectMethodOnObjectArray($arrObjects, $strObjectMethod, $arrParameters = NULL, $callbackRetval = 'boolean_and', $mixedBaseRetval = true, $boolReturnOnNotBaseRetval = false, $strObjectProperty = NULL, &$arrCalledObjects = NULL)
   {
      if(!isset($arrParameters))
      {
         // If no parameters needed, make a blank array.
         $arrParameters = array();
      }
      elseif(!is_array($arrParameters))
      {
         // If a single (non-array) parameter was passed
         // make it into an array.
         $arrParameters = array($arrParameters);
      }

      if(!is_callable($callbackRetval))
      {
         $this->declareError("Could not CallObjectMethodOnObjectArray: Callback function $callbackRetval does not exist.");
         return false;
      }

      if(!isset($arrCalledObjects) || !is_array($arrCalledObjects))
      {
         $arrCalledObjects = array();
      }

      $retval = $mixedBaseRetval;

      foreach($arrObjects as $Object)
      {
         if(!is_object($Object))
         {
            if(isset($Object))
            {
               $this->declareError("Could not CallObjectMethodOnObjectArray: Object Property $strObjectProperty is not an Object (Type: " . gettype($Object) . ".");
               return false;
            }
            else
            {
               $this->declareError("Could not CallObjectMethodOnObjectArray: Object Property $strObjectProperty does not exist.");
               return false;
            }
         }

         if($strObjectProperty)
         {
            if(!is_object($Object->$strObjectProperty))
            {
               if(property_exists($Object, $strObjectProperty))
               {
                  $this->declareError("Could not CallObjectMethodOnObjectArray: Object Property $strObjectProperty is not an Object (Type: " . gettype($Object->$strObjectProperty) . ".");
                  return false;
               }
               else
               {
                  $this->declareError("Could not CallObjectMethodOnObjectArray: Object Property $strObjectProperty does not exist.");
                  return false;
               }
            }
            else
            {
               $Object = $Object->$strObjectProperty;
            }
         }

         // If the method exists, call it passing along $arrParameters.
         // Then call $strRetvalCallback with the existing return value and
         // the new return value as parameters.
         if($this->methodExists($Object, $strObjectMethod))
         {
            $retval = call_user_func($callbackRetval, $retval, call_user_func_array(array($Object, $strObjectMethod), $arrParameters));

            if($boolReturnOnNotBaseRetval && $retval != $mixedBaseRetval)
            {
               return $retval;
            }
         }

         $arrCalledObjects[] = $Object;
      }

      return $retval;
   }

   /**
    * Sets Memory Cache of the entry denoted by $Object if it isn't already set
    *
    * @param object $Object
    * @return boolean
    */
   public function cacheObject($Object)
   {
      if(!is_object($Object) || !$Object->ID)
      {
         return false;
      }

      if(!isset($this->MemoryCache['Objects'][get_class($Object)][$Object->ID]))
      {
         $this->MemoryCache['Objects'][get_class($Object)][$Object->ID] = $Object;
      }

      return true;
   }

   /**
    * Clears Memory Cache of the entry denoted by $ClassName
    *
    * @param string $ClassName
    * @return boolean
    */
   public function clearCacheObjectEntry($ClassName, $ID)
   {
      unset($this->MemoryCache['Objects'][$ClassName][$ID]);

      return true;
   }

   /**
    * Clears Memory Cache of the entry denoted by $Table
    *
    * @param string $Table
    * @return boolean
    */
   public function clearCacheTableEntry($Table)
   {
      unset($this->MemoryCache['Tables'][$Table]);

      return true;
   }

   /**
    * Returns the value of $_ARCHON->Error and empties it.
    *
    * @return unknown
    */
   public function clearError()
   {
      $Error = $this->Error;
      $this->Error = '';

      return $Error;
   }

   /**
    * Fully traverses two variables and returns a "consensus" variable
    *
    * If the argument variables are objects or arrays, the function will
    * return a variable of the same type with any member variables that are
    * not equal in both set to variables set to the value specified by
    * MULTIPLE_VALUES.
    *
    * Note that this function is unable to precisely compare
    * objects that have private member variables, since it cannot access them.
    *
    * @param mixed $var1
    * @param mixed $var2
    * @return mixed
    */
   public function createConsensusVariable($var1, $var2)
   {
      if(gettype($var1) != gettype($var2))
      {
         $this->declareError("Could not create ConsensusVariable: Variables are not of the same type.");
         return false;
      }

      if(is_object($var1))
      {
         $classtype = get_class($var1);

         // If the object is a class, make sure the consensus is of the same class.
         // Hopefully the constructor will not require any arguments.
         if($classtype)
         {
            $consensus = New $classtype();
            $arrVariables = array_keys(get_object_vars($var1));
         }
         else
         {
            $arrVariables = array_keys(array_merge(get_object_vars($var1), get_object_vars($var2)));
         }

         foreach($arrVariables as $name)
         {
            if(is_object($var1->$name))
            {
               if(is_object($var2->$name))
               {
                  $consensus->$name = $this->createConsensusVariable($var1->$name, $var2->$name);
               }
               else
               {
                  $consensus->$name = MULTIPLE_VALUES;
               }
            }
            elseif(is_array($var1->$name))
            {
               if(is_array($var2->$name))
               {
                  $consensus->$name = $this->createConsensusVariable($var1->$name, $var2->$name);
               }
               else
               {
                  $consensus->$name = MULTIPLE_VALUES;
               }
            }
            elseif($var1->$name !== $var2->$name)
            {
               $consensus->$name = MULTIPLE_VALUES;
            }
            else
            {
               $consensus->$name = $var1->$name;
            }
         }
      }
      elseif(is_array($var1))
      {
         $consensus = array();

         $arrKeys = array_keys($var1 + $var2);

         foreach($arrKeys as $key)
         {
            if(is_object($var1[$key]))
            {
               if(is_object($var2[$key]))
               {
                  $consensus[$key] = $this->createConsensusVariable($var1[$key], $var2[$key]);
               }
               else
               {
                  $consensus[$key] = MULTIPLE_VALUES;
               }
            }
            elseif(is_array($var1[$key]))
            {
               if(is_array($var2[$key]))
               {
                  $consensus[$key] = $this->createConsensusVariable($var1[$key], $var2[$key]);
               }
               else
               {
                  $consensus[$key] = MULTIPLE_VALUES;
               }
            }
            elseif($var1[$key] !== $var2[$key])
            {
               $consensus[$key] = MULTIPLE_VALUES;
            }
            else
            {
               $consensus[$key] = $var1[$key];
            }
         }
      }

      return $consensus;
   }

   /**
    * Takes a string representing a range or list of numbers and creates and
    * array containing all numbers the string represents.
    *
    * {@example
    * If $String == '1 - 7, 9, 14, 19, 4, 10 - 16'
    * the function would return an array containing the following elements:
    * 1,2,3,4,5,6,7,9,10,11,12,13,14,15,16,19
    * }
    *
    * @param string $String
    * @return integer[]
    */
   public function createNumericArrayFromString($String)
   {
      $String = str_replace(" ", "", $String);

      $arrDiscretes = explode(",", $String);
      foreach($arrDiscretes as $discrete)
      {
         if(encoding_strpos($discrete, "-") !== false)
         {
            list($first, $last) = explode("-", $discrete);

            for($i = intval($first); $i <= $last; $i++)
            {
               $arrNumbers[$i] = $i;
            }
         }
         else
         {
            $arrNumbers[$discrete] = intval($discrete);
         }
      }

      sort($arrNumbers);

      return $arrNumbers;
   }

   /**
    * Takes a string representing a range or list of floats and creates and
    * array containing all numbers the string represents.
    *
    * {@example
    * If $String == '1 - 7, 9, 14, 19, 4, 10.1 - 11.2'
    * the function would return an array containing the following elements:
    * 1,2,3,4,5,6,7,9,10.1,10.2,10.3,10.4,10.5,10.6,10.7,10.8,10.9,11,11.1,11.2,14,19
    * }
    *
    * @param string $String
    * @return float[]
    */
   public function createFloatArrayFromString($String)
   {
      $String = str_replace(' ', '', $String);

      $arrDiscretes = explode(',', $String);
      foreach($arrDiscretes as $discrete)
      {
         if(encoding_strpos($discrete, '-') !== false)
         {
            list($first, $last) = explode('-', $discrete);

            // need to do fine grain over units/tenths/hundredths as applicable
            $pos = encoding_strpos($first, '.');
            $declength = encoding_strlen($first) - $pos - 1;
            if($pos === false || $declength >= 3 || $declength <= 0)
            {
               $granule = 1;
            }
            elseif($declength == 2)
            {
               $granule = 0.01;
            }
            else
            {
               $granule = 0.1;
            }

            for($i = floatval($first); $i <= $last; $i += $granule)
            {
               $arrNumbers[strval($i)] = $i;
            }
            if(($i - $last) < $granule)
            {
               $arrNumbers[strval($i)] = $i;
            }
         }
         else
         {
            $arrNumbers[strval($discrete)] = floatval($discrete);
         }
      }

      sort($arrNumbers);

      return $arrNumbers;
   }

   /**
    * Creates an array of words and phrases parsed from a query string
    *
    * Note: The strings in the returned array all have their slashes stripped.
    *
    * @param string $String
    * @return string[]
    */
   public function createSearchWordArray($String)
   {
      if(!$String)
      {
         return false;
      }

      // Parse Search Query
      $String = $this->mdb2->escapePattern($String);
      $String = str_replace("'", '"', $String);

      // If there are an odd number of quotes, we'll assume they
      // forgot to close the final quote.
      if((encoding_substr_count($String, '"') % 2) != 0)
      {
         $String .= '"';
      }

      // $phrases will contain an array of phrases, found
      // within quotes.
      $phrases = array();

      preg_match_all('/".*?"/iu', $String, $phrases);

      $phrases = str_replace('"', "", $phrases[0]);

      $String = preg_replace('/".*?"/iu', '', $String);
      $String = preg_replace('/\s\s+/u', ' ', $String);
      $String = trim($String);

      if($String)
      {
         $arrWords = explode(" ", $String);
      }

      if(!empty($phrases))
      {
         foreach($phrases as $phrase)
         {
            $arrWords[] = $phrase;
         }
      }

      if(is_array($arrWords))
      {
         reset($arrWords);
      }

      return $arrWords;
   }

   /**
    * Creates an formatted string from an array of Language objects
    *
    * @param Language[] $arrLanguages
    * @param string $Delimiter[optional]
    * @param integer $MakeIntoLink[optional]
    * @return string
    */
   public function createStringFromLanguageArray($arrLanguages, $Delimiter = ', ', $MakeIntoLink = LINK_NONE)
   {
      if(empty($arrLanguages))
      {
         $this->declareError("Could not create Language String: No IDs specified.");
         return false;
      }

      $objLast = end($arrLanguages);

      foreach($arrLanguages as $objLanguage)
      {
         $string .= $objLanguage->toString($MakeIntoLink);

         if($objLanguage->ID != $objLast->ID)
         {
            $string .= $Delimiter;
         }
      }

      return $string;
   }

   public function getDBStats()
   {
      if(substr_count($this->db->ServerType, 'MySQL'))
      {
         $query = "SHOW TABLE STATUS";
         $result = $this->mdb2->query($query);
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            isset($dbStats->Tables[$row['Name']]) or
               $dbStats->Tables[$row['Name']] = new stdClass();
            $dbStats->Tables[$row['Name']]->Rows = $row['Rows'];
            $dbStats->Tables[$row['Name']]->DiskUsed = formatsize($row['Data_length'] + $row['Index_length']);
            $useddiskspace += $row['Data_length'] + $row['Index_length'];
            //$freediskspace = $row['Max_data_length'];
         }
         $result->free();

         $dbStats->DiskUsed = formatsize($useddiskspace);
         $dbStats->DiskFree = formatsize($freediskspace);
      }
      elseif(substr_count($this->db->ServerType, 'MSSQL'))
      {
         $this->mdb2->loadModule('Manager');

         $query = "DBCC showfilestats;";
         $result = $this->mdb2->query($query);
         if(!pear_isError($result))
         {
            $row = $result->fetchRow();
            $result->free();
         }

         // Fixes weird issue where SQL Server doesn't like the immediate next query.
         $this->mdb2->query("SELECT * FROM tblCore_Configuration WHERE 1 = 0;");
         if(!pear_isError($result))
         {
            $row = $result->fetchRow();
            $result->free();
         }

         $mdb2Tables = $this->mdb2->listTables();

         foreach($mdb2Tables as $tblName)
         {
            $query = "sp_MStablespace {$this->mdb2->quoteIdentifier($tblName)}";
            $result = $this->mdb2->query($query);
            if(!pear_isError($result))
            {
               $usagerow = $result->fetchRow();
               $result->free();
            }

            $dbStats->Tables[$tblName]->Rows = $usagerow['Rows'];
            $dbStats->Tables[$tblName]->DiskUsed = formatsize(1024 * ($usagerow['DataSpaceUsed'] + $usagerow['IndexSpaceUsed']));
            $useddiskspace += 1024 * ($usagerow['DataSpaceUsed'] + $usagerow['IndexSpaceUsed']);
         }

         $dbStats->DiskUsed = formatsize($useddiskspace);
         //$dbStats->DiskUsed = formatsize($row['TotalExtents'] * 64 * 1024);
         //$dbStats->DiskFree = formatsize($row['UsedExtents'] * 64 * 1024);
      }

      return $dbStats;
   }

   /**
    * Returns results of calling reverse module's tableInfo() function
    */
   public function getDBStructure()
   {
      $this->mdb2->loadModule('Manager');
      $this->mdb2->loadModule('Reverse');

      $dbStructure = array();

      $mdb2Tables = $this->mdb2->listTables();
      foreach($mdb2Tables as $tblName)
      {
         isset($dbStructure[$tblName]) or
            $dbStructure[$tblName] = new stdClass();
         $dbStructure[$tblName]->Columns = array();

         $mdb2TableInfo = $this->mdb2->tableInfo($tblName);
         foreach($mdb2TableInfo as $field)
         {
            $dbStructure[$tblName]->Columns[$field['name']] = $field;
         }
      }

      return $dbStructure;
   }

   /**
    * Appends new error onto list of previous errors.
    *
    * @param string $Error
    */
   public function declareError($Error)
   {
      if(encoding_substr_count($this->Error, $Error))
      {
         return;
      }
     
      // Sanitize for XSS.
      $Error = htmlspecialchars($Error);

      $this->Error = $this->Error ? "{$this->Error}; $Error" : $Error;
   }

   /**
    * Deletes an object from the database
    *
    * @param mixed $Object, int $ModuleID, string $Table
    */
   public function deleteObject($Object, $ModuleID, $Table)
   {
      if(is_object($Object))
      {
         $strClassName = get_class($Object);
      }
      else
      {
         return false;
      }

      if(!$Object->ID)
      {
         $this->declareError("Could not delete $strClassName: $strClassName ID not defined.");
         return false;
      }

      if(!is_natural($Object->ID))
      {
         $this->declareError("Could not delete $strClassName: $strClassName ID must be numeric.");
         return false;
      }

      $ID = $Object->ID;

      if(!$this->methodExists($Object, 'verifyDeletePermissions'))
      {
         if(!$this->Security->verifyPermissions($ModuleID, DELETE))
         {
            $this->declareError("Could not delete $strClassName: Permission Denied.");
            return false;
         }
      }
      else
      {
         if(!$Object->verifyDeletePermissions())
         {
            $this->declareError("Could not delete $strClassName: Permission Denied.");
            return false;
         }
      }

      static $checkPreps = array();
      if(!isset($checkPreps[$strClassName]))
      {
         $checkPreps[$strClassName] = $this->mdb2->prepare("SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ID = ?", 'integer', MDB2_PREPARE_RESULT);
      }

      $result = $checkPreps[$strClassName]->execute($ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $this->declareError("Could not delete $strClassName: $strClassName ID $ID not found in database.");
         return false;
      }

      if($this->classVarExists($Object, 'ParentID') && $this->methodExists($Object, 'dbDelete'))
      {
         static $childdrenPreps = array();
         if(!isset($childrenPreps[$strClassName]))
         {
            $childquery = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ParentID = ?";
            $childdrenPreps[$strClassName] = $this->mdb2->prepare($childquery, 'integer', MDB2_PREPARE_RESULT);
         }

         $result = $childdrenPreps[$strClassName]->execute($ID);
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            $objChild = New $strClassName($row['ID']);
            $objChild->dbDelete();
         }
      }

      static $deletePreps = array();
      if(!isset($deletePreps[$strClassName]))
      {
         $deletePreps[$strClassName] = $this->mdb2->prepare("DELETE FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ID = ?", 'integer', MDB2_PREPARE_MANIP);
      }

      $affected = $deletePreps[$strClassName]->execute($ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if($affected < 1)
      {
         $this->declareError("Could not delete $strClassName: Unable to delete from the database table.");
         return false;
      }

      $this->log($Table, $Object->ID);

      $Object->ID = 0;

      return $ID;
   }

   public function deleteRelationship($Table, $KeyField, $KeyValue, $RelationshipType)
   {
      if(!$KeyValue || $KeyValue < 1)
      {
         $this->declareError("Could not delete relationship for Table {$Table}. Key value is invalid.");
      }
      if($RelationshipType != MANY_TO_MANY && $RelationshipType != ONE_TO_MANY)
      {
         $this->declareError("Could not delete relationship for Table {$Table}. Relationship type is invalid.");
      }

      static $preps = array();
      if(!isset($preps[$Table][$KeyField]))
      {
         switch($RelationshipType)
         {
            case(MANY_TO_MANY):
               $query = "DELETE FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($KeyField)} = ?";
               break;
            case(ONE_TO_MANY):
               $query = "UPDATE {$this->mdb2->quoteIdentifier($Table)} SET {$this->mdb2->quoteIdentifier($KeyField)} = '0' WHERE {$this->mdb2->quoteIdentifier($KeyField)} = ?";
               break;
            default:
               $this->declareError("Could not delete relationship. Uknown Error.");
               return false;
         }

         $preps[$Table][$KeyField] = $this->mdb2->prepare($query, array('integer'), MDB2_PREPARE_MANIP);

         if(pear_isError($preps[$Table][$KeyField]))
         {
            trigger_error($preps[$Table][$KeyField]->getMessage(), E_USER_ERROR);
         }
      }
      $affected = $preps[$Table][$KeyField]->execute($KeyValue);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      return true;
   }

   /**
    * Returns an array of installed admin themes.
    *
    * The names of installed themes are derived
    * from the directory names under adminthemes/
    *
    * @return string[] array of theme names
    */
   public function getAllAdminThemes()
   {
      $cwd = getcwd();

      chdir("adminthemes/");

      if($handle = opendir("./"))
      {
         while(false !== ($dir = readdir($handle)))
         {
            if(file_exists("./$dir/index.php") && $dir != ".." && $dir != '.')
            {
               $ThemeList[$dir] = $dir;
            }
         }
      }

      chdir($cwd);

      return $ThemeList;
   }

   /**
    * Retrieves all Configuration from the database
    *
    * The returned array of Configuration objects
    * is sorted by PackageID, ModuleID, Directive and has IDs as keys.
    *
    * $ExcludeNoAccessConfiguration[optional]
    * @return Repository[]
    */
   public function getAllConfiguration($ExcludeNoAccessConfiguration = true, $ExcludePasswords = true)
   {
      if($ExcludePasswords)
      {
         $arrConfiguration = $this->loadTable("tblCore_Configuration", "Configuration", "Directive", "InputType != ?", array('text'), array('password'), true);
      }
      else
      {
         $arrConfiguration = $this->loadTable("tblCore_Configuration", "Configuration", "Directive", NULL, NULL, NULL, true);
      }

      if($ExcludeNoAccessConfiguration && !$this->Security->verifyPermissions(MODULE_CONFIGURATION, FULL_CONTROL))
      {
         foreach($arrConfiguration as $objConfiguration)
         {
            if(($objConfiguration->ModuleID && !$this->Security->verifyPermissions($objConfiguration->ModuleID, FULL_CONTROL)) || (!$objConfiguration->ModuleID && !$this->Security->verifyPermissions(MODULE_CONFIGURATION, UPDATE)))
            {
               unset($arrConfiguration[$objConfiguration->ID]);
            }
            elseif($objConfiguration->Encrypted && $objConfiguration->Value)
            {
               $objCryptor = New Cryptor();
               $objConfiguration->Value = $objCryptor->decrypt(base64_decode($objConfiguration->Value));
            }
         }
      }

      return $arrConfiguration;
   }

   /**
    * Retrieves all Countries from the database
    *
    * The returned array of Countries objects
    * is sorted by CountryName and has IDs as keys.
    *
    * @return Country[]
    */
   public function getAllCountries($ReturnList = false)
   {
      return Country::getAllCountries($ReturnList);
   }

   /**
    * Returns an array of Database import utilities.
    *
    * Note: Only import utilities in packages
    * which are installed and enabled will be added
    * to the returned array
    *
    * The first dimension is the PackageID
    * The second dimension is the "Utility Code" defined
    * by the import utility
    *
    * @return object[][]
    */
   public function getAllDatabaseImportUtilities()
   {
      global $_ARCHON;

      foreach($this->Packages as $ID => $objPackage)
      {
         if(is_natural($ID))
         {
            if(file_exists("packages/$objPackage->APRCode/db/") && $handle = opendir("packages/$objPackage->APRCode/db/"))
            {
               while(false !== ($file = readdir($handle)))
               {
                  if(preg_match("/import-.*?.inc.php/u", $file))
                  {
                     require("packages/$objPackage->APRCode/db/$file");
                  }
               }
            }
         }
      }

      return $this->db->ImportUtilities;
   }

   public function getAllDatabaseExportUtilities()
   {
      global $_ARCHON;

      foreach($this->Packages as $ID => $objPackage)
      {
         if(is_natural($ID))
         {
            if(file_exists("packages/$objPackage->APRCode/db/") && $handle = opendir("packages/$objPackage->APRCode/db/"))
            {
               while(false !== ($file = readdir($handle)))
               {
                  if(preg_match("/export-.*?.inc.php/u", $file))
                  {
                     require("packages/$objPackage->APRCode/db/$file");
                  }
               }
            }
         }
      }

      return $this->db->ExportUtilities;
   }

   /**
    * Returns an array of all ARCHON::getAll*() function names.
    *
    * @return string[] array of theme names
    */
   public function getAllGetAllFunctions()
   {
      $arrFunctions = array_keys($this->Mixins[get_class($this)]->Methods);
      $arrFunctions = array_combine($arrFunctions, $arrFunctions);

      $pattern = '/^getAll/';

      foreach($arrFunctions as $strFunction)
      {
         if(!preg_match($pattern, $strFunction))
         {
            unset($arrFunctions[$strFunction]);
         }
      }

      asort($arrFunctions);

      return $arrFunctions;
   }

   /**
    * Returns an array of incoming file's names and locations
    *
    * If no 'serverfiles' are specified, it will return all files in the incoming directory
    * Otherwise, it returns only the serverfiles in the incoming directory.
    *
    * @return string[] of file locations
    */
   public function getAllIncomingFileLocations()
   {
      $arrFiles = array();

      if($_FILES['uploadfile']['tmp_name'])
      {
         $arrFiles[$_FILES['uploadfile']['tmp_name']] = $_FILES['uploadfile']['tmp_name'];
      }
      elseif(is_array($_REQUEST['serverfiles']))
      {
         $_REQUEST['serverfiles'] = preg_replace('/[\\/\\\\]/u', '', $_REQUEST['serverfiles']);

         foreach($_REQUEST['serverfiles'] as $Filename)
         {
            if(file_exists("incoming/" . $Filename))
            {
               $arrFiles[$Filename] = "incoming/$Filename";
            }
            else
            {
               $this->declareError("Could not load IncomingFile: File incoming/$Filename does not exist.");
            }
         }
      }
      else
      {
         if($handle = opendir("incoming/"))
         {
            while(false !== ($file = readdir($handle)))
            {
               if($file != '.' && $file != '..')
               {
                  $arrFiles[$file] = "incoming/$file";
               }
            }
         }
      }

      return $arrFiles;
   }

   /**
    * Returns any array of Incoming Files.
    *
    * The names of installed Incoming Files are derived
    * from the directory names under incoming/
    *
    * If $LoadFileContents is false, the array will contain
    * paths to the files instead of the FileContents.
    *
    * @param $LoadFileContents[optional]
    * @return string[] array of file contents
    */
   public function getAllIncomingFiles($LoadFileContents = true)
   {
      $arrFiles = array();

      if($_FILES['uploadfile']['tmp_name'])
      {
         if($LoadFileContents)
         {
            $arrFiles = file_get_contents_array($_FILES['uploadfile']['tmp_name']);
         }
         else
         {
            $arrFiles[$_FILES['uploadfile']['name']] = $_FILES['uploadfile']['tmp_name'];
         }
      }
      elseif(is_array($_REQUEST['serverfiles']) && !empty($_REQUEST['serverfiles']))
      {
         $_REQUEST['serverfiles'] = preg_replace('/[\\/\\\\]/u', '', $_REQUEST['serverfiles']);

         foreach($_REQUEST['serverfiles'] as $Filename)
         {
            if(file_exists("incoming/" . $Filename))
            {
               if($LoadFileContents)
               {
                  $arrFiles = array_merge($arrFiles, file_get_contents_array("incoming/" . $Filename));
               }
               else
               {
                  $arrFiles[$Filename] = "incoming/" . $Filename;
               }
            }
            else
            {
               $this->declareError("Could not load IncomingFile: File incoming/$Filename does not exist.");
            }
         }
      }

      return $arrFiles;
   }

   /**
    * Retrieves all Languages from the database
    *
    * The returned array of Language objects
    * is sorted by LanguageLong and has IDs as keys.
    *
    * @return Language[]
    */
   public function getAllLanguages($ReturnList = false)
   {
      return Language::getAllLanguages($ReturnList);
   }

   /**
    * Retrieves all Repositories from the database
    *
    * The returned array of Repository objects
    * is sorted by Name and has IDs as keys.
    *
    * @return Repository[]
    */
   public function getAllRepositories()
   {
      return $this->loadTable("tblCore_Repositories", "Repository", "Name");
   }

   /**
    * Retrieves all Scripts from the database
    *
    * The returned array of Script objects
    * is sorted by ScriptEnglishLong and has IDs as keys.
    *
    * @return Script[]
    */
   public function getAllScripts($ReturnList = false)
   {
      return Script::getAllScripts($ReturnList);
   }

   /**
    * Retrieves all Modules from the database
    *
    * The returned array of Module objects
    * is sorted by Package, Module and has IDs as keys.
    *
    * @return Module[]
    */
   public function getAllModules($ExcludeDisabledPackages = true)
   {
      $arrModules = array();

      if($ExcludeDisabledPackages)
      {
         $ExcludeDisabledPackagesQuery = ' AND tblCore_Packages.Enabled = 1';


         // Class variable Packages is set on initialize excluding disabled packages
         // so we will just return the already existing instance of the array instead of
         // running multiple queries against the database
         if($this->MemoryCache['Modules'])
         {
            return $this->MemoryCache['Modules'];
         }
      }

      $query = 'SELECT tblCore_Modules.* FROM tblCore_Modules INNER JOIN tblCore_Packages ON (tblCore_Modules.PackageID = tblCore_Packages.ID) WHERE 1=1' . $ExcludeDisabledPackagesQuery . ' ORDER BY tblCore_Packages.APRCode, tblCore_Modules.Script';

      $result = $this->mdb2->query($query);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         if(!$arrModules[$row['ID']])
         {
            // Ensure packages have not been temporarily disabled by $_ARCHON->initialize()
            if(($ExcludeDisabledPackages && $this->MemoryCache['Packages'][$row['PackageID']]->Enabled) || !$ExcludeDisabledPackages)
            {
               $this->MemoryCache['Modules'][$row['ID']] = New Module($row);
               $this->MemoryCache['Modules'][$row['ID']]->Package = $this->MemoryCache['Packages'][$row['PackageID']];
               $arrModules[$row['ID']] = $this->MemoryCache['Modules'][$row['ID']];
            }
         }
      }
      $result->free();

      reset($arrModules);

      return $arrModules;
   }

   /**
    * Retrieves all Packages from the database
    *
    * The returned array of Package objects
    * is sorted by Package and has IDs as keys.
    *
    * @param integer $LanguageID[optional]
    * @param boolean $ExcludeDisabledPackages[optional]
    *
    * @return Package[]
    */
   public function getAllPackages($ExcludeDisabledPackages = true)
   {
      $arrPackages = array();

//        if(!$LanguageID)
//        {
//            if($this->Security->Session)
//            {
//                $LanguageID = $this->Security->Session->getLanguageID();
//            }
//            else
//            {
//                $LanguageID = CONFIG_CORE_DEFAULT_LANGUAGE;
//            }
//        }
//        elseif(!is_natural($LanguageID))
//        {
//            $this->declareError("Could not get Packages: Language ID not defined.");
//        }

      if($ExcludeDisabledPackages)
      {
         $ExcludeDisabledPackagesQuery = "AND tblCore_Packages.Enabled = '1'";

         // Class variable Packages is set on initialize excluding disabled packages
         // so we will just return the already existing instance of the array instead of
         // running multiple queries against the database
         if($this->MemoryCache['Packages'])
         {
            return $this->MemoryCache['Packages'];
         }
      }

      $query = "SELECT tblCore_Packages.* FROM tblCore_Packages WHERE 1 = 1 $ExcludeDisabledPackagesQuery ORDER BY APRCode";
      $result = $this->mdb2->query($query);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         if(!$arrPackages[$row['ID']])
         {
            $this->MemoryCache['Packages'][$row['ID']] = New Package($row);
            $arrPackages[$row['ID']] = $this->MemoryCache['Packages'][$row['ID']];
         }
      }
      $result->free();

      return $arrPackages;
   }

   /**
    * Retrieves all Patterns from the database
    *
    * The returned array of Pattern objects
    * is sorted by Pattern and has IDs as keys.
    *
    * @return Pattern[]
    */
   public function getAllPatterns()
   {
      return $this->loadTable("tblCore_Patterns", "Pattern", "Name");
   }

   /**
    * Returns a static array of the allowed input types for user profile fields
    *
    * @return string[]
    */
   public function getAllUserProfileFieldInputTypes()
   {
      static $arrUserProfileFieldInputTypes = array(
  'radio' => 'radio',
  'select' => 'select',
  'textarea' => 'textarea',
  'textfield' => 'textfield',
  'timestamp' => 'timestamp'
      );

      return $arrUserProfileFieldInputTypes;
   }

   /**
    * Retrieves all Phrases from the database
    *
    * The returned array of Phrase objects
    * is sorted by Phrase and has IDs as keys.
    *
    * @return Phrase[]
    */
   public function getAllPhrases()
   {
      return $this->loadTable("tblCore_Phrases", "Phrase", "PhraseName");
   }

   /**
    * Retrieves all Phrase Types from the database
    *
    * The returned array of PhraseType objects
    * is sorted by PhraseType and has IDs as keys.
    *
    * @return PhraseType[]
    */
   public function getAllPhraseTypes()
   {
      return PhraseType::getAllPhraseTypes();
   }

   /**
    * Retrieves all Sessions from the database
    *
    * The returned array of Session objects
    * is sorted by Name and has IDs as keys.
    *
    * @return Session[]
    */
   public function getAllSessions($ActivityTimeLimit = 0)
   {
      if($ActivityTimeLimit)
      {
         $ActiveTime = $ActivityTimeLimit * 60;
         $Conditions = "(Persistent = 1 AND Expires >= " . (time() + COOKIE_EXPIRATION - $ActiveTime) . ") OR (Persistent = 0 AND Expires >= " . (time() + SESSION_EXPIRATION - $ActiveTime) . ")";
      }
      else
      {
         $Conditions = NULL;
      }

      return $this->loadTable("tblCore_Sessions", "Session", "Expires DESC", $Conditions);
   }

   /**
    * Retrieves all StateProvinces from the database
    *
    * The returned array of Countries objects
    * is sorted by CountryID, StateProvinceName and has IDs as keys.
    *
    * @return StateProvince[]
    */
   public function getAllStateProvinces($ReturnList = false)
   {
      if($ReturnList)
      {
         return $this->loadObjectList("tblCore_StateProvinces", "StateProvince", "StateProvinceName", "CountryID, StateProvinceName");
      }
      else
      {
         return $this->loadTable("tblCore_StateProvinces", "StateProvince", "CountryID, StateProvinceName");
      }
   }

   /**
    * Returns any array of installed templates.
    *
    * The names of installed templates are derived
    * from the directory names under templates/
    *
    * @return string[][] array of template names
    */
   public function getAllTemplates()
   {
      foreach($this->Packages as $ID => $objPackage)
      {
         if(is_natural($ID))
         {
            if(file_exists("packages/$objPackage->APRCode/templates") && $handle = opendir("packages/$objPackage->APRCode/templates"))
            {
               while(false !== ($dir = readdir($handle)))
               {
                  if(file_exists("packages/$objPackage->APRCode/templates/$dir/index.php") && $dir != ".." && $dir != '.')
                  {
                     $TemplateList[$dir] = $dir;
                  }
               }
            }
         }
      }
      return $TemplateList;
   }

   /**
    * Returns any array of installed templates for a single package
    *
    * The names of installed templates are derived
    * from the directory names under templates/
    * @param  string $package package for which to return templates
    * @return string[][] array of template names
    */
   public function getPackageTemplates($package = 'core')
   {
      if(file_exists("packages/$package/templates") && $handle = opendir("packages/$package/templates"))
      {
         while(false !== ($dir = readdir($handle)))
         {
            if(file_exists("packages/$package/templates/$dir/index.php") && $dir != ".." && $dir != '.')
            {
               $TemplateList[$dir] = $dir;
            }
         }
      }

      return $TemplateList;
   }

   /**
    * Returns an array of installed themes.
    *
    * The names of installed themes are derived
    * from the directory names under themes/
    *
    * @return string[] array of theme names
    */
   public function getAllThemes()
   {
      $cwd = getcwd();

      chdir("themes/");

      if($handle = opendir("./"))
      {
         while(false !== ($dir = readdir($handle)))
         {
            if(file_exists("./$dir/index.php") && $dir != ".." && $dir != '.')
            {
               $ThemeList[$dir] = $dir;
            }
         }
      }

      chdir($cwd);

      return $ThemeList;
   }

   /**
    * Retrieves all Usergroups from the database
    *
    * The returned array of Usergroup objects
    * is sorted by Usergroup and has IDs as keys.
    *
    * @return Usergroup[]
    */
   public function getAllUsergroups($ReturnList = false)
   {
      if($ReturnList)
      {
         return $this->loadObjectList("tblCore_Usergroups", "Usergroup", "Usergroup");
      }
      else
      {
         $arrUsergroups = $this->loadTable("tblCore_Usergroups", "Usergroup", "Usergroup");

         foreach($arrUsergroups as &$objUsergroup)
         {
            $objUsergroup->dbLoadPermissions();
         }

         reset($arrUsergroups);

         return $arrUsergroups;
      }
   }

   public function getAllPublicUsers()
   {
      return $this->loadTable("tblCore_Users", "User", "DisplayName", "IsAdminUser = ?", "integer", 0);
   }

   /**
    * Retrieves all Users from the database
    *
    * The returned array of User objects
    * is sorted by Login and has IDs as keys.
    *
    * @return User[]
    */
   public function getAllUsers()
   {
      $arrUsers = $this->loadTable("tblCore_Users", "User", "DisplayName");
      $arrUsergroups = $this->getAllUsergroups();

      foreach($arrUsers as &$objUser)
      {
         $objUser->dbLoadUsergroupIDs();
		 $objUser->dbLoadRepositoryIDs();
      }

      $arrUsers[-1] = New User(-1);
      $arrUsers[-1]->dbLoad();

      reset($arrUsers);

      return $arrUsers;
   }

   /**
    * Retrieves all UserProfileFieldCategories from the database
    *
    * The returned array of UserProfileFieldCategory objects
    * is sorted by UserProfileFieldCategory
    * and has IDs as keys.
    *
    * @return UserProfileFieldCategory[]
    */
   public function getAllUserProfileFieldCategories()
   {
      return $this->loadTable("tblCore_UserProfileFieldCategories", "UserProfileFieldCategory", "DisplayOrder, UserProfileFieldCategory");
   }

   /**
    * Retrieves all UserProfileFields from the database
    *
    * The returned array of UserProfileField objects
    * is sorted by PackageID, UserProfileField
    * and has IDs as keys.
    *
    * @return UserProfileField[]
    */
   public function getAllUserProfileFields($ExcludeDisabledPackageFields = true)
   {
      $Conditions = $ExcludeDisabledPackageFields ? "PackageID IN (SELECT PackageID FROM tblCore_Packages WHERE Enabled = 1)" : NULL;

      $arrUserProfileFields = $this->loadTable("tblCore_UserProfileFields", "UserProfileField", "UserProfileFieldCategoryID, DisplayOrder, UserProfileField", $Conditions, array(), array());

      $arrCountries = $this->getAllCountries();

      $prep = $this->mdb2->prepare('SELECT * FROM tblCore_UserProfileFieldCountryIndex', NULL, MDB2_PREPARE_RESULT);
      $result = $prep->execute();
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrUserProfileFields[$row['UserProfileFieldID']]->Countries[$row['CountryID']] = $arrCountries[$row['CountryID']];
         $arrUserProfileFields[$row['UserProfileFieldID']]->Countries[$row['CountryID']]->Required = $row['Required'];


         // This is primarily for the administrative interface
         if($row['Required'])
         {
            $arrUserProfileFields[$row['UserProfileFieldID']]->RequiredCountries[$row['CountryID']] = $arrUserProfileFields[$row['UserProfileFieldID']]->Countries[$row['CountryID']];
         }
      }

      $result->free();
      $prep->free();

      return $arrUserProfileFields;
   }

   /**
    * Returns CountryID value
    * when passed the Country's ISOAlpha2 string.
    *
    * @param string $ISOAlpha2
    * @return integer
    */
//   public function getCountryIDFromISOAlpha2($ISOAlpha2)
//   {
//      // Case sensitve
//      $this->mdb2->setLimit(1);
//      $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_Countries WHERE ISOAlpha2 = ?', 'text', MDB2_PREPARE_RESULT);
//      $result = $prep->execute($ISOAlpha2);
//      if (pear_isError($result))
//      {
//         trigger_error($result->getMessage(), E_USER_ERROR);
//      }
//
//      $row = $result->fetchRow();
//      $result->free();
//      $prep->free();
//
//      $row['ID'] = $row['ID'] ? $row['ID'] : 0;
//
//      return $row['ID'];
//   }

   /**
    * Return <script> tags to open javascript files label with $Filename
    * from all enabled packages.
    *
    * $Filename should not include the .js extension
    *
    * @param string $Filename
    * @param string[] $GETData
    * @return string
    */
   public function getJavascriptTags($Filename, $GETData = array())
   {
      $Filename = preg_replace('/[^\\w\\d-_.]/u', '', encoding_strtolower($Filename));

      $tags = '';

      $arrPackages = $this->getAllPackages();

      if(!empty($GETData))
      {
         $getstring = '?';
         foreach($GETData as $key => $value)
         {
            $getstring .= $key . '=' . $value;
         }
      }

      $cwd = getcwd();

      if($this->RootDirectory)
      {
         chdir($this->RootDirectory);
      }

      foreach($arrPackages as $objPackage)
      {
         if(file_exists("packages/$objPackage->APRCode/js/$Filename.js"))
         {
            $tags .= "<script type='text/javascript' src='packages/$objPackage->APRCode/js/$Filename.js$getstring'></script>";
         }
      }

      chdir($cwd);

      return $tags;
   }

   /**
    * Returns LanguageID value
    * when passed the string value
    * for a language type.
    *
    * @param string $String
    * @return integer
    */
   public function getLanguageIDFromString($String)
   {
      return Language::getLanguageIDFromString($String);
   }

   /**
    * Returns the short name string when passed the ID value of a language.
    * This is a faster, cacheable alternative to loading a language object and
    * using dbLoad();
    *
    * @param integer $ID Language ID
    * @return string
    */
   public function getLanguageShortFromID($ID)
   {
      if(!$ID)
      {
         $ID = CONFIG_CORE_DEFAULT_LANGUAGE;
      }

      if(isset($this->MemoryCache['Objects']['Language'][$ID]))
      {
         return $this->MemoryCache['Objects']['Language'][$ID]->LanguageShort;
      }

      $objLanguage = New Language($ID);

      if(!$objLanguage->dbLoad())
      {
         if(CONFIG_CORE_DEFAULT_LANGUAGE)
         {
            return $this->getLanguageShortFromID(CONFIG_CORE_DEFAULT_LANGUAGE);
         }
         else // to avoid infinite loops, even though this shouldn't happen
         {
            return '';
         }
      }

      $this->MemoryCache['Objects']['Language'][$ID] = $objLanguage;

      return $objLanguage->LanguageShort;
   }

   /**
    * Returns the version of the latest of Archon
    *
    * @return string
    */
   public function getLatestArchonVersion()
   {
      if(CONFIG_CORE_CHECK_FOR_UPDATES)
      {
         $date = date('Y-m-d');

         $result = $this->mdb2->query("SELECT LastUpdated,VersionNumber FROM tblCore_VersionCache WHERE VersionName = 'Version';");
         
         if(!pear_isError($result) && $result->numRows())
         {
            $row = $result->fetchRow();
            if($date > $row['LastUpdated'])
            {
               $version = @file_get_contents($this->ArchonURL . 'sys/version.php?aprcode=core');
               $query = "UPDATE tblCore_VersionCache SET VersionNumber = ?, LastUpdated = ?  WHERE VersionName = ? ";
               $prep = $this->mdb2->prepare($query, array('text', 'date', 'text'), MDB2_PREPARE_MANIP);
               if(pear_isError($prep))
               {
                  trigger_error($prep->getMessage(), E_USER_ERROR);
               }

               $affected = $prep->execute(array($version, $date, 'Version'));
               if(pear_isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }

               $prep->free();
            }
            else
            {
               $version = $row['VersionNumber'];
            }
         }
         else
         {

            $version = @file_get_contents($this->ArchonURL . 'sys/version.php?aprcode=core');

            $query = "INSERT INTO tblCore_VersionCache (VersionName, VersionNumber, LastUpdated) VALUES (?, ?, ?)";
            $prep = $this->mdb2->prepare($query, array('text', 'text', 'date'), MDB2_PREPARE_MANIP);
            if(!pear_isError($prep))
            {
               $affected = $prep->execute(array('Version', $version, $date));
               if(pear_isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }
               $prep->free();
            }
         }

         return $version;
      }
      else
      {
         return $this->Version;
      }
   }

   /**
    * Returns the version of the latest of Archon
    *
    * @return string
    */
   public function getLatestArchonRevision()
   {
      if(CONFIG_CORE_CHECK_FOR_UPDATES)
      {
         $date = date('Y-m-d');

         $result = $this->mdb2->query("SELECT LastUpdated,VersionNumber FROM tblCore_VersionCache WHERE VersionName = 'Revision';");
         if($result->numRows())
         {
            $row = $result->fetchRow();
            if($date > $row['LastUpdated'])
            {
               $revision = @file_get_contents($this->ArchonURL . 'sys/version.php?type=revision');
               $query = "UPDATE tblCore_VersionCache SET VersionNumber = ?, LastUpdated = ?  WHERE VersionName = ? ";
               $prep = $this->mdb2->prepare($query, array('text', 'date', 'text'), MDB2_PREPARE_MANIP);
               if(pear_isError($prep))
               {
                  trigger_error($prep->getMessage(), E_USER_ERROR);
               }

               $affected = $prep->execute(array($revision, $date, 'Revision'));
               if(pear_isError($affected))
               {
                  trigger_error($prep->getMessage(), E_USER_ERROR);
               }

               $prep->free();
            }
            else
            {
               $revision = $row['VersionNumber'];
            }
         }
         else
         {

            $revision = @file_get_contents($this->ArchonURL . 'sys/version.php?type=revision');

            $query = "INSERT INTO tblCore_VersionCache (VersionName, VersionNumber, LastUpdated) VALUES (?, ?, ?)";
            $prep = $this->mdb2->prepare($query, array('text', 'text', 'date'), MDB2_PREPARE_MANIP);
            if(pear_isError($prep))
            {
               trigger_error($prep->getMessage(), E_USER_ERROR);
            }

            $affected = $prep->execute(array('Revision', $revision, $date));
            if(pear_isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }

            $prep->free();
         }

         return $revision;
      }
      else
      {
         return $this->Revision;
      }
   }

   /**
    * Returns the version number for the latest update of the package
    *
    * @param string $APRCode
    */
   public function getLatestPackageVersionFromAPRCode($APRCode)
   {
      if(CONFIG_CORE_CHECK_FOR_UPDATES)
      {
         $date = date('Y-m-d');

         $result = $this->mdb2->query("SELECT LastUpdated,VersionNumber FROM tblCore_VersionCache WHERE VersionName = '$APRCode';");
         if($result->numRows())
         {
            $row = $result->fetchRow();
            if($date > $row['LastUpdated'])
            {
               $version = @file_get_contents($this->ArchonURL . 'sys/version.php?aprcode=' . $APRCode);
               $query = "UPDATE tblCore_VersionCache SET VersionNumber = ?, LastUpdated = ?  WHERE VersionName = ? ";
               $prep = $this->mdb2->prepare($query, array('text', 'date', 'text'), MDB2_PREPARE_MANIP);
               if(pear_isError($prep))
               {
                  trigger_error($prep->getMessage(), E_USER_ERROR);
               }

               $affected = $prep->execute(array($version, $date, $APRCode));
               if(pear_isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }

               $prep->free();
            }
            else
            {
               $version = $row['VersionNumber'];
            }
         }
         else
         {

            $version = @file_get_contents($this->ArchonURL . 'sys/version.php?aprcode=' . $APRCode);

            $query = "INSERT INTO tblCore_VersionCache (VersionName, VersionNumber, LastUpdated) VALUES (?, ?, ?)";
            $prep = $this->mdb2->prepare($query, array('text', 'text', 'date'), MDB2_PREPARE_MANIP);
            if(pear_isError($prep))
            {
               trigger_error($prep->getMessage(), E_USER_ERROR);
            }

            $affected = $prep->execute(array($APRCode, $version, $date));
            if(pear_isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }

            $prep->free();
         }

         return $version;
      }
      else
      {
         return $this->Version;
      }
   }

   /**
    * Returns ModuleID value
    * when passed the module script string.
    *
    * @param string $Script
    * @return integer
    */
   public function getModuleIDFromScript($Script)
   {
      // Case sensitve
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_Modules WHERE Script = ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($Script);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Return PackageID value
    * when passed the package aprcode string.
    *
    * @param string $APRCode
    * @return integer
    */
   public function getPackageIDFromAPRCode($APRCode)
   {
      // Case sensitive
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_Packages WHERE APRCode = ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($APRCode);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Retrieves an array of permissions for a specified user
    *
    * @param integer $UserID
    * @return integer[]
    */
   public function getPermissionsForUser($UserID)
   {
      if(!$UserID)
      {
         $this->declareError("Could not get Permissions: User ID not defined.");
         return false;
      }


      if($UserID == $this->Security->Session->User->ID)
      {
         $objUser = $this->Security->Session->User;
      }
      else
      {
         $objUser = New User($UserID);

         if(!$objUser->dbLoad())
         {
            $this->declareError("Could not get Permissions: $this->Error");
            return false;
         }
      }
      // If no permissions are set at all, deny any request.
      $Permissions = array();


      // it's easier to iterate over an array of arrays instead of an array of objects
      $arrUsergroupPermissions = array();
      $arrUsergroupDefaultPermissions = array();
      if(!empty($objUser->Usergroups))
      {
         foreach($objUser->Usergroups as $objUsergroup)
         {
            $arrUsergroupPermissions[] = $objUsergroup->Permissions;
            $arrUsergroupDefaultPermissions[] = $objUsergroup->DefaultPermissions;
         }
      }


      $arrModules = $this->getAllModules();

      foreach($arrModules as $moduleID => $objModule)
      {
         // Custom permissions for the user have the highest priority, then custom Usergroup permissions
         // if neither are set, use the Usergroup's default permissions.  Also, permissions can be 0, so
         // we must use the identical comparison operator.
         if($objUser->Permissions[$moduleID] !== NULL)
         {
            $Permissions[$moduleID] = $objUser->Permissions[$moduleID];
         }
         else
         {
            foreach($arrUsergroupPermissions as $key => $arrPermissions)
            {
               if(array_key_exists($moduleID, $arrPermissions))
               {
                  $val = $arrPermissions[$moduleID];
               }
               else
               {
                  $val = $arrUsergroupDefaultPermissions[$key];
               }

               $Permissions[$moduleID] |= $val;
            }
         }
      }

      return $Permissions;
   }

   /**
    * Retrives permissions for a specified usergroup
    *
    * @param integer $UsergroupID
    * @return integer[]
    */
   public function getPermissionsForUsergroup($UsergroupID)
   {
      if(!$UsergroupID)
      {
         $this->declareError("Could not get Permissions: Usergroup ID not defined.");
         return false;
      }

      $objUsergroup = New Usergroup($UsergroupID);

      if(!$objUsergroup->dbLoad())
      {
         $this->declareError("Could not get Permissions: $this->Error");
         return false;
      }

      $Permissions = array();


      $arrModules = $this->getAllModules();

      foreach($arrModules as $moduleID => $objModule)
      {
         // Custom permissions for the usergroup have the highest priority, then custom Usergroupgroup permissions
         // if neither are set, use the Usergroupgroup's default permissions.  Also, permissions can be 0, so
         // we must use the identical comparison operator.
         if($objUsergroup->Permissions[$moduleID] !== NULL)
         {
            $Permissions[$moduleID] = $objUsergroup->Permissions[$moduleID];
         }
         else
         {
            // No permissions are explicitly set, so return the default permissions.
            $Permissions[$moduleID] = $objUsergroup->DefaultPermissions;
         }
      }

      return $Permissions;
   }

   /**
    * Loads the MemoryCache with Phrases for every module for each package
    *
    */
   public function loadModulePhrases()
   {
      Phrase::loadModulePhrases();
   }

   /**
    * Retrieves Phrase for Phrase specified by PhraseName and PhraseTypeID
    *
    * @param string $PhraseName
    * @param integer $PhraseTypeID[optional]
    * @param integer $LanguageID[optional]
    * @return Phrase
    */
   public function getPhrase($PhraseName, $PackageID, $ModuleID, $PhraseTypeID, $LanguageID = 0)
   {
      return Phrase::getPhrase($PhraseName, $PackageID, $ModuleID, $PhraseTypeID, $LanguageID);
   }

   /**
    * Returns PhraseTypeID value
    * when passed the string value
    * for a phrase type.
    *
    * @param string $String
    * @return integer
    */
   public function getPhraseTypeIDFromString($String)
   {
      $PhraseTypeID = PhraseType::getPhraseTypeIDFromString($String);

      return $PhraseTypeID;
   }

   /**
    * Return UsergroupID value
    * when passed the usergroup's
    * name string.
    *
    * @param string $Usergroup
    * @return integer
    */
   public function getUsergroupIDFromName($Usergroup)
   {
      // Case sensitve
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_Usergroups WHERE Usergroup = ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($Usergroup);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Returns UserID value
    * when passed the user's
    * login string.
    *
    * @param string $Login
    * @return integer
    */
   public function getUserIDFromLogin($Login)
   {
      if(encoding_strtolower($Login) == 'sa')
      {
         return -1;
      }

      // Case sensitve
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_Users WHERE Login = ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($Login);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Returns UserProfileFieldID value
    * when passed the userprofilefield's
    * userprofilefield string.
    *
    * @param string $UserProfileField
    * @return integer
    */
   public function getUserProfileFieldIDFromUserProfileField($UserProfileField)
   {
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_UserProfileFields WHERE UserProfileField = ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($UserProfileField);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   public function checkDB()
   {
      $this->mdb2->setLimit(1);
      $result = $this->mdb2->query("SELECT ID FROM tblCore_Configuration");
      if(pear_isError($result))
      {
         $this->mdb2->setLimit(1);
         $result = $this->mdb2->query("SELECT ID FROM tblArchon_Configuration");
         if(pear_isError($result))
         {
            // Most likely tblCore_Configuration does not exist, assume older than 2.00
            header("Location: index.php?p=upgrade");
            die();
         }
         elseif(file_exists('packages/core/install/install.php'))
         {
            header("Location: index.php?p=install");
            die();
         }
         else
         {
            trigger_error("Could not load Archon: There was a problem querying the database", E_USER_ERROR);
         }
      }
      else
      {
         $result->free();
      }
   }

   public function initPackages()
   {
      global $_ARCHON;

      $this->checkDB();

      foreach($this->Packages as $ID => $objPackage)
      {
         $this->Packages[$objPackage->APRCode] = & $this->Packages[$ID];
      }

      foreach($this->Packages as $ID => $objPackage)
      {
         if(is_natural($ID))
         {
            if(file_exists("packages/$objPackage->APRCode/index.php"))
            {
               $cwd = getcwd();
               chdir("packages/$objPackage->APRCode/");

               require_once("index.php");

               $this->Packages[$ID]->Version = $Version;

               if(version_compare($this->Packages[$ID]->Version, $this->Packages[$ID]->DBVersion) == 1)
               {
                  if($objPackage->APRCode == 'core')
                  {
                     header("Location: index.php?p=upgrade");
                     die();
                  }
                  if(!$_POST['upgrader'])
                  {
                     $this->Packages[$ID]->Enabled = false;
                  }
                  // @TODO: See if we can redirect to the package manager, if this isn't the upgrader
               }
               elseif(version_compare($this->Packages[$ID]->Version, $this->Packages[$ID]->DBVersion) == -1)
               {
                  trigger_error("Could not load Archon: The database version for Package {$this->Packages[$ID]->APRCode} ({$this->Packages[$ID]->DBVersion}) is newer than the Package codebase ({$this->Packages[$ID]->Version}).  Please re-install the Package.", E_USER_ERROR);
               }
               elseif(!empty($this->Packages[$ID]->DependsUpon))
               {
                  foreach($this->Packages[$ID]->DependsUpon as $APRCode => $DependsUponVersion)
                  {
                     if(!$this->Packages[$APRCode])
                     {
                        trigger_error("Could not load Archon: Package {$this->Packages[$ID]->APRCode} depends upon package $APRCode which is not installed.", E_USER_ERROR);
                     }
                     elseif(version_compare($this->Packages[$APRCode]->DBVersion, $DependsUponVersion) == -1)
                     {
                        trigger_error("Could not load Archon: Package {$this->Packages[$ID]->APRCode} requires Package $APRCode version $DependsUponVersion or newer (installed version is {$this->Packages[$APRCode]->DBVersion}).", E_USER_ERROR);
                     }
                  }
               }

               chdir($cwd);
            }
         }
      }

      $this->Version = $this->Packages['core']->Version;


      $arrDependencies = array_merge((array)$this->Packages['core']->DependedUponBy, (array)$this->Packages['core']->EnhancedBy);

      while(!empty($arrDependencies))
      {
         reset($arrDependencies);
         $Dependency = key($arrDependencies);

         if($arrTopologicalSort[$Dependency])
         {
            array_shift($arrDependencies);
         }
         elseif($arrSeen[$Dependency] || (empty($this->Packages[$Dependency]->DependedUponBy) && empty($this->Packages[$Dependency]->EnhancedBy)))
         {
            $arrTopologicalSort[$Dependency] = $Dependency;
            array_shift($arrDependencies);
         }
         else
         {
            foreach(array_keys(array_merge((array)$this->Packages[$Dependency]->DependedUponBy, (array)$this->Packages[$Dependency]->EnhancedBy)) as $ChildDependency)
            {
               if($arrSeen[$ChildDependency] && !isset($arrTopologicalSort[$ChildDependency]))
               {
                  trigger_error("Could not create package dependency topological sort: $Dependency and $ChildDependency create a cyclical dependence", E_USER_ERROR);
               }
               else
               {
                  $arrDependencies = array_merge(array($ChildDependency => true), $arrDependencies);
               }
            }
         }
         $arrSeen[$Dependency] = true;
      }

      $arrTopologicalSort['core'] = 'core';

      foreach(array_reverse($arrTopologicalSort) as $APRCode)
      {
         if(!$this->Packages[$APRCode]->Enabled)
         {
            if(!empty($this->Packages[$APRCode]->DependedUponBy))
            {
               foreach($this->Packages[$APRCode]->DependedUponBy as $DisabledAPRCode => $bool)
               {
                  $this->Packages[$DisabledAPRCode]->Enabled = false;
               }
            }
            $PackageID = $this->Packages[$APRCode]->ID;
            unset($this->Packages[$PackageID]);
            unset($this->Packages[$APRCode]);
         }
         elseif(file_exists("packages/{$APRCode}/lib/index.php"))
         {
            define("PACKAGE_" . encoding_strtoupper($APRCode), $this->Packages[$APRCode]->ID, false);

            $cwd = getcwd();
            chdir("packages/{$APRCode}/lib/");
            require_once("index.php");
            chdir($cwd);
         }
      }

      $this->TopologicalPackageKeys = array_reverse($arrTopologicalSort);
   }

   public function initModules()
   {
      $this->Modules = $this->getAllModules();
      foreach($this->Modules as $ID => $objModule)
      {
         $this->Modules[$objModule->Script] = & $this->Modules[$ID];
         define("MODULE_" . encoding_strtoupper($objModule->Script), $objModule->ID, false);
      }

      define("MODULE_NONE", 0, false);
   }

   /**
    * Initializes Archon for use
    *
    */
   public function initialize()
   {
      global $_ARCHON;

      $this->RootDirectory = getcwd();

      $this->Packages = $this->getAllPackages();

      $this->initPackages();
      $this->initModules();

      $arrConfiguration = $this->getAllConfiguration(false);

      foreach($arrConfiguration as $objConfiguration)
      {
         if($objConfiguration->Encrypted && $objConfiguration->Value)
         {
            $objCryptor = New Cryptor();
            $objConfiguration->Value = $objCryptor->decrypt(base64_decode($objConfiguration->Value));
         }

         $constname = 'CONFIG_' . encoding_strtoupper($this->Packages[$objConfiguration->PackageID]->APRCode) . '_' . encoding_strtoupper($objConfiguration->Directive);
         $constname = str_replace(' ', '_', $constname);

         @define($constname, $objConfiguration->Value, false);
      }

      $this->Security = New Security();
          
      if(defined('PACKAGE_COLLECTIONS'))
      {
         if($this->Security->Session->ResearchCart)
         {
            if($this->Security->isAuthenticated() && $this->Security->userHasAdministrativeAccess() == false)
            {                
                $arrImplodedEntries = explode(',', $this->Security->Session->getRemoteVariable('Cart'));

                if(!empty($arrImplodedEntries))
                {
                    foreach($arrImplodedEntries as $strIDs)
                    {
                        if($strIDs)
                        {
                            list($CollectionID, $CollectionContentID) = explode(':', $strIDs);                            
                            $this->Security->Session->User->dbAddToCart($CollectionID, $CollectionContentID);
                        }
                    }
                }
                
                $this->Security->Session->unsetRemoteVariable('Cart', true);
            }
         }
      }

      if(isset($_REQUEST['unsetadmintheme']))
      {
         $this->Security->Session->unsetRemoteVariable('AdminTheme');
      }

      if(isset($_REQUEST['unsettheme']))
      {
         $this->Security->Session->unsetRemoteVariable('Theme');
      }

      if(isset($_REQUEST['unsetlanguageid']))
      {
         if($this->Security->Session->User)
         {
            $this->Security->Session->User->dbSetLanguageID(0);
         }
         else
         {
            $this->Security->Session->unsetRemoteVariable('LanguageID');
         }
      }

      if(isset($_REQUEST['unsetall']))
      {
         $this->Security->Session->unsetAllRemoteVariables();
      }

      if(isset($_REQUEST['setadmintheme']))
      {
         $this->Security->Session->setRemoteVariable('AdminTheme', $_REQUEST['setadmintheme']);
      }

      if(isset($_REQUEST['settheme']))
      {
         $this->Security->Session->setRemoteVariable('Theme', $_REQUEST['settheme']);
      }

      if(isset($_REQUEST['setlanguageid']))
      {
         if($this->Security->Session->User)
         {
            $this->Security->Session->User->dbSetLanguageID($_REQUEST['setlanguageid']);
         }
         else
         {
            $this->Security->Session->setRemoteVariable('LanguageID', $_REQUEST['setlanguageid']);
         }
      }

      if(isset($_REQUEST['unsetrepositoryid']))
      {
         $this->Security->Session->unsetRemoteVariable('RepositoryID');
      }

      if(isset($_REQUEST['setrepositoryid']))
      {
         $this->Security->Session->setRemoteVariable('RepositoryID', $_REQUEST['setrepositoryid']);
      }

      if($this->Security->Session->getRemoteVariable('RepositoryID'))
      {
         $this->Repository = New Repository($this->Security->Session->getRemoteVariable('RepositoryID'));
      }
      else
      {
         $this->Repository = New Repository(CONFIG_CORE_DEFAULT_REPOSITORY);
      }

      $this->Repository->dbLoad();

      $_REQUEST['p'] = preg_replace('/[^\w^\d^-^_\/]/u', '', encoding_strtolower($_REQUEST['p']));
      $defaultPubP = preg_replace('/[^\w^\d^-^_\/]/u', '', encoding_strtolower(CONFIG_CORE_DEFAULT_PUBLIC_SCRIPT));

      $arrP = explode('/', $_REQUEST['p']);

      $arrDefaultPubP = explode('/', $defaultPubP);
      if(!$this->Packages[$arrDefaultPubP[0]])
      {
         $arrDefaultPubP[0] = 'core';
         $arrDefaultPubP[1] = 'index';
      }
      elseif(!file_exists("packages/{$arrDefaultPubP[0]}/pub/{$arrDefaultPubP[1]}.php"))
      {
         $arrDefaultPubP[0] = 'core';
         $arrDefaultPubP[1] = 'index';
      }

      if($arrP[0] == 'admin')
      {
         $Package = $arrP[1] ? $arrP[1] : $arrDefaultPubP[0];
         $Script = $arrP[2] ? $arrP[2] : $arrDefaultPubP[1];

         if(!$this->Packages[$Package])
         {
            $this->declareError("Package {$Package} is not installed.");

            $arrP[0] = $arrDefaultPubP[0];
            $arrP[1] = $arrDefaultPubP[1];
         }
         elseif(!file_exists("packages/{$Package}/admin/{$Script}.php"))
         {
            $this->declareError("Script {$Script} does not exist in Package {$Package}.");

            $arrP[0] = $arrDefaultPubP[0];
            $arrP[1] = $arrDefaultPubP[1];
         }
         else
         {
            $this->AdministrativeInterface = New AdministrativeInterface();
         }
      }
      elseif($_REQUEST['p'] == 'install' || $_REQUEST['p'] == 'upgrade')
      {
         $this->AdministrativeInterface = New AdministrativeInterface();
      }

      if(!$this->AdministrativeInterface)
      {
         $Package = $arrP[0] ? $arrP[0] : $arrDefaultPubP[0];
         $Script = $arrP[1] ? $arrP[1] : $arrDefaultPubP[1];

         if(!$this->Packages[$Package])
         {
            $this->declareError("Package {$Package} is not installed.");

            $Package = $arrDefaultPubP[0];
            $Script = $arrDefaultPubP[1];
         }
         elseif(!file_exists("packages/$Package/pub/$Script.php"))
         {
            $this->declareError("Script {$Script} does not exist in Package {$Package}.");

            $Package = $arrDefaultPubP[0];
            $Script = $arrDefaultPubP[1];
         }

         $this->Package = $this->Packages[$Package];
         $this->Script = "packages/{$Package}/pub/{$Script}.php";

         $this->PublicInterface = New PublicInterface();
         $DefaultTemplateSet = CONFIG_CORE_DEFAULT_TEMPLATE_SET;
         $this->PublicInterface->initialize(($this->Security->Session->getRemoteVariable('Theme') ? $this->Security->Session->getRemoteVariable('Theme') : CONFIG_CORE_DEFAULT_THEME), ($_REQUEST['templateset'] ? $_REQUEST['templateset'] : $DefaultTemplateSet));

         if($_REQUEST['disabletheme'] || $_REQUEST['notheme'])
         {
            $this->PublicInterface->DisableTheme = true;
         }
      }
      elseif($_REQUEST['p'] != 'install' && $_REQUEST['p'] != 'upgrade')
      {
         if(!$this->Security->userHasAdministrativeAccess())
         {
            $Package = 'core';
            $Script = 'login';
         }
         elseif($this->Security->verifyPermissions($this->Modules[$Script]->ID, READ) || ($Package == 'core' && ($Script == 'index' || $Script == 'home' || $Script == 'ajax')))
         {
            $this->Module = $this->Modules[$Script];
         }
         elseif($Script != 'login')
         {
            die('Access Denied');
         }

         $this->Package = $this->Packages[$Package];
         $this->Script = "packages/{$Package}/admin/{$Script}.php";

         $this->AdministrativeInterface->initialize($this->Security->Session->getRemoteVariable('AdminTheme') ? $this->Security->Session->getRemoteVariable('AdminTheme') : CONFIG_CORE_DEFAULT_ADMINISTRATIVE_THEME);
      }
      else
      {
         $this->AdministrativeInterface->initialize($this->Security->Session->getRemoteVariable('AdminTheme') ? $this->Security->Session->getRemoteVariable('AdminTheme') : CONFIG_CORE_DEFAULT_ADMINISTRATIVE_THEME);
      }

      if($this->Script == 'packages/core/pub/index.php' && file_exists('packages/core/install/install.php'))
      {
         header('Location: index.php?p=install');
         die();
      }

      // normal mixins with initialize don't work as they were mixed in during initialize
      foreach($_ARCHON->Mixins['Archon']->Methods['initialize']->Classes as $MixinClass)
      {
         if($MixinClass == 'Core_Archon')
         {
            continue;
         }

         //$result = call_user_func(array($MixinClass, initialize));
         eval("\$result = {$MixinClass}::initialize();");
      }
   }
   
   /**
    * Loads argument object with information from the database
    *
    * @param object $Object
    * @param string $Table
    *
    * @return boolean
    */
   public function loadObject($Object, $Table, $NoMemoryCache = false, $Fields = array())
   {
      if(is_object($Object))
      {
         $strClassName = get_class($Object);
      }
      else
      {
         return false;
      }

      if(!$Object->ID)
      {
         $this->declareError("Could not load $strClassName: $strClassName ID not defined.");
         return false;
      }

      if(!is_natural($Object->ID))
      {
         $this->declareError("Could not load $strClassName: $strClassName ID must be numeric.");
         return false;
      }

      if($this->methodExists($Object, 'verifyLoadPermissions') && !$Object->verifyLoadPermissions())
      {
         $this->declareError("Could not load $strClassName: Permission Denied.");
         return false;
      }

      if(isset($this->MemoryCache['Objects'][$strClassName][$Object->ID]) && !$NoMemoryCache)
      {
         $arrVariables = get_object_vars($Object);
         foreach($arrVariables as $name => $defaultvalue)
         {
            if(isset($this->MemoryCache['Objects'][$strClassName][$Object->ID]->$name))
            {
               $Object->$name = $this->MemoryCache['Objects'][$strClassName][$Object->ID]->$name;
            }
         }

         return true;
      }

      if(!empty($Fields) && is_array($Fields))
      {
         $badFields = array_diff($Fields, array_keys(get_object_vars($Object)));
         if(!empty($badFields))
         {
            $this->declareError("Could not load {$strClassName}: Field(s) '" . implode(',', $badFields) . "' do not exist in Class {$strClassName}.");
            return false;
         }

         $selectFields = implode(',', $Fields);
         //TODO: sort select fields?
         $fieldsPrepKey = implode('', $Fields);
      }

      $selectFields = ($selectFields) ? $selectFields : '*';
      $fieldsPrepKey = ($fieldsPrepKey) ? $fieldsPrepKey : 0;

      static $loadPreps = array();
      $loadPreps[$Table][$fieldsPrepKey] = $loadPreps[$Table][$fieldsPrepKey] ? $loadPreps[$Table][$fieldsPrepKey] : $this->mdb2->prepare("SELECT {$selectFields} FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ID = ?", 'integer', MDB2_PREPARE_RESULT);
      $result = $loadPreps[$Table][$fieldsPrepKey]->execute($Object->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $this->declareError("Could not load $strClassName: $strClassName ID $Object->ID not found in database.");
         //$this->MemoryCache['Objects'][$strClassName][$Object->ID] = false;
         return false;
      }

      $row = array_change_key_case($row);
      $arrVariables = get_object_vars($Object);
      foreach($arrVariables as $name => $defaultvalue)
      {
         if(isset($row[strtolower($name)]))
         {
            $Object->$name = $row[strtolower($name)];
         }
      }

      //TODO: Decide if this should cache
      //$this->MemoryCache['Objects'][$strClassName][$Object->ID] = $Object;

      return true;
   }

   /**
    * Loads Templates according to named Template Set
    *
    * @param string $TemplateSet
    * @return array with container types for keys and file contents for container type as values
    */
   public function loadTemplates($TemplateSet)
   {
      if(!$TemplateSet)
      {
         return array();
      }

      if(preg_match('/[\\/\\\\]/u', $TemplateSet))
      {
         $this->declareError("Could not load Templates: Invalid TemplateSet $TemplateSet.");
         return false;
      }

      $TemplateSet = encoding_strtolower($TemplateSet);

      $cwd = getcwd();

      if(!empty($this->Packages))
      {
         foreach($this->Packages as $key => $objPackage)
         {
            if(!is_natural($key))
            {
               if(file_exists("packages/$objPackage->APRCode/templates/default/index.php"))
               {
                  chdir("packages/$objPackage->APRCode/templates/default/");

                  require("index.php");

                  if(!empty($TemplateIndex))
                  {
                     foreach($TemplateIndex as $type => $file)
                     {
                        if(preg_match('/[\\/\\\\]/u', $file))
                        {
                           $this->declareError("Could not load Templates: Invalid Template file $file.");
                           return false;
                        }
                        elseif(!file_exists($file))
                        {
                           $this->declareError("Could not load Templates: Template file $file does not exist.");
                           return false;
                        }
                        else
                        {
                           $arrTemplates[$objPackage->APRCode][$type] = '?>' . file_get_contents($file);
                        }
                     }
                  }

                  unset($TemplateIndex);

                  chdir($cwd);
               }

               if($TemplateSet != 'default' && file_exists("packages/$objPackage->APRCode/templates/$TemplateSet/index.php"))
               {
                  chdir("packages/$objPackage->APRCode/templates/$TemplateSet/");

                  require("index.php");

                  if(!empty($TemplateIndex))
                  {
                     foreach($TemplateIndex as $type => $file)
                     {
                        if(preg_match('/[\\/\\\\]/u', $file))
                        {
                           $this->declareError("Could not load Templates: Invalid Template file $file.");
                           return false;
                        }
                        elseif(!file_exists($file))
                        {
                           $this->declareError("Could not load Templates: Template file $file does not exist.");
                           return false;
                        }
                        else
                        {
                           $arrTemplates[$objPackage->APRCode][$type] = '?>' . file_get_contents($file);
                        }
                     }
                  }

                  unset($TemplateIndex);

                  chdir($cwd);
               }
            }
         }
      }

      //$Templates["_TemplateIndex"] = $TemplateIndex;

      return $arrTemplates;
   }

   /**
    *
    * Clears data from the log table that was logged before the passed date
    *
    * Returns boolean representing a successful purge
    *
    * @param integer $Timestamp
    * @return boolean
    */
   function purgeLog($Timestamp)
   {
      if(!$this->mdb2 || !CONFIG_CORE_MODIFICATION_LOG_ENABLED)
      {
         return;
      }

      if(!$this->Security->verifyPermissions(MODULE_MODIFICATIONLOG, FULL_CONTROL))
      {
         $this->declareError("Could not purge modification log: Permission Denied.");
         return false;
      }

      if(!$Timestamp || !is_int($Timestamp))
      {
         $this->declareError("Could not purge modification log: Invalid timestamp.");
         return false;
      }

      $query = "DELETE FROM tblCore_ModificationLog where Timestamp <= ?";
      $prep = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      if(pear_isError($prep))
      {
         trigger_error($prep->getMessage(), E_USER_ERROR);
      }

      $affected = $prep->execute($Timestamp);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $prep->free();

      return true;
   }

   /**
    * Adds entry in modification log
    *
    * @param string $TableName
    * @param integer $ID
    */
   function log($TableName, $ID)
   {
      if(!$this->mdb2 || !CONFIG_CORE_MODIFICATION_LOG_ENABLED)
      {
         return;
      }

      $in_f = $_REQUEST['f'] ? $_REQUEST['f'] : NULL;
      $arrIn = $_REQUEST;

      foreach($arrIn as $key => $value)
      {
         if(stripos($key, 'password') !== false)
         {
            $arrIn[$key] = '(hidden)';
         }
      }

      $this->clearCacheTableEntry($TableName);

      $request = serialize($arrIn);

      $UserID = $this->Security->Session->getUserID() ? $this->Security->Session->getUserID() : 0;
      $Login = $this->Security->Session->User->Login;
      $Login = $Login ? $Login : '(Not Authenticated)';
      $ModuleID = $this->Module->ID ? $this->Module->ID : 0;

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "INSERT INTO tblCore_ModificationLog (TableName, RowID, Timestamp, UserID, Login, RemoteHost, ModuleID, ArchonFunction, RequestData) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
         $prep = $this->mdb2->prepare($query, array('text', 'integer', 'integer', 'integer', 'text', 'text', 'integer', 'text', 'text'), MDB2_PREPARE_MANIP);
      }

      $affected = $prep->execute(array($TableName, $ID, time(), $UserID, $Login, getenv('REMOTE_ADDR'), $ModuleID, $in_f, $request));
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
   }

   /**
    * Processes phrase and translates as necessary.
    *
    * @param string $Phrase
    * @return string
    */
   public function processPhrase($Phrase)
   {
      $MessagePhraseTypeID = $this->getPhraseTypeIDFromString('Message');
      $NounPhraseTypeID = $this->getPhraseTypeIDFromString('Noun');

      $LanguageID = $this->Security->Session->getLanguageID();
      $arrAllPhrases = $this->searchPhrases('', 0, 0, $MessagePhraseTypeID, $LanguageID, 0);

      $tempPhrase = str_replace('; ', ': ', $Phrase);
      $arrPhrases = explode(': ', $tempPhrase);

      $arrOldPhrase = array();
      $arrNewPhrase = array();
      if(!empty($arrPhrases))
      {
         foreach($arrPhrases as $MessagePhrase)
         {
            if(!empty($arrAllPhrases))
            {
               foreach($arrAllPhrases as $objPhrase)
               {
                  if(trim($objPhrase->RegularExpression) && preg_match("/$objPhrase->RegularExpression/iu", $MessagePhrase, $arrMatch) && strip_tags($arrMatch[0]) == strip_tags(trim($MessagePhrase)))
                  {
                     $newPhrase = $objPhrase->PhraseValue;
                     preg_match_all('/\$([\d]+)/u', $newPhrase, $arrNounMatches);
                     foreach($arrNounMatches[1] as $nounMatch)
                     {
                        if(trim($arrMatch[$nounMatch]))
                        {
                           foreach($this->Packages as $objPackage)
                           {
                              if($objNounPhrase = $this->getPhrase(encoding_strtolower(trim($arrMatch[$nounMatch])), $objPackage->ID, 0, $NounPhraseTypeID))
                              {
                                 break;
                              }
                           }
                        }
                        else
                        {
                           $objNounPhrase = NULL;
                        }

                        $strNoun = $objNounPhrase ? $objNounPhrase->PhraseValue : $arrMatch[$nounMatch];
                        if(substr($arrMatch[$nounMatch], 0, 1) == ' ')
                        {
                           $strNoun = ' ' . $strNoun;
                        }

                        $newPhrase = str_replace('$' . $nounMatch, $strNoun, $newPhrase);
                     }

                     $arrOldPhrase[] = $arrMatch[0];
                     $arrNewPhrase[] = $newPhrase;
                  }
               }
            }
         }
      }

      $Phrase = str_replace($arrOldPhrase, $arrNewPhrase, $Phrase);

      return $Phrase;
   }

   /**
    * Redirects user to specified location.
    *
    * @param string $Location
    */
   public function redirect($Location = NULL, $Params = array())
   {

      if($Location)
      {
         if(encoding_strpos($Location, ";") === false)
            $Location = "location.href = '$Location";
      }

      $Location = $_REQUEST['go'] ? "location.href = '" . encode($_REQUEST['go'], ENCODE_JAVASCRIPT) : $Location;

      if(!empty($Params))
      {
         foreach($Params as $key => $val)
         {
            $Location .= encode("&{$key}={$val}", ENCODE_JAVASCRIPT);
         }
      }

      $Location .= "';";
      ?>
      <script type="text/javascript">
         <!--
      <?php
      if($Location)
      {
         echo("$Location\n");
      }
      ?>
         -->
      </script>

      <?php
   }

   /**
    * Performs self-check on directory permissions, version number, etc. to
    * ensure Archon is being run safely and up to date.
    *
    * Variable are stored in $_SESSION variables
    */
   public function runDiagnostics()
   {
//      $dirPermissions = $this->Security->Session->getRemoteVariable('DirPermissions');
//      if(!isset($dirPermissions))
//      {
//         $this->Security->Session->setRemoteVariable('DirPermissions', substr(sprintf('%o', fileperms($this->RootDirectory)), -4));
//      }
//      $dirPermissions = substr(sprintf('%o', fileperms($this->RootDirectory)), -4);
//      if(fileperms($this->RootDirectory) & 0x0002)
//      {
//         $warning = "Warning! The public has write permissions to the Archon directory! Please correct this immediately!";
//      }
      //TODO: put the rest of the tests here.
   }

   /**
    * Searches the Configuration database
    *
    * @param string $SearchQuery
    * @param integer $PackageID[optional]
    * @param integer $ModuleID[optional]
    * @param boolean $ExcludeNoAccessConfiguration[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ProductCategory[]
    */
   public function searchConfiguration($SearchQuery, $PackageID = 0, $ModuleID = 0, $ExcludeNoAccessConfiguration = true, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $ConditionsANDTypes = array();
      $ConditionsANDVars = array();

      $ConditionsAND = NULL;

      if($PackageID && is_natural($PackageID))
      {
         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND .= "PackageID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $PackageID;
      }

      if($ModuleID && is_natural($ModuleID))
      {
         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND .= "ModuleID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $ModuleID;
      }

      $arrConfiguration = $this->searchTable($SearchQuery, 'tblCore_Configuration', 'Directive', 'Configuration', 'Directive', $ConditionsAND, $ConditionsANDTypes, $ConditionsANDVars, NULL, array(), array(), $Limit, $Offset);

      if($ExcludeNoAccessConfiguration && !$this->Security->verifyPermissions(MODULE_CONFIGURATION, FULL_CONTROL))
      {
         foreach($arrConfiguration as $objConfiguration)
         {
            if(($objConfiguration->ModuleID && !$this->Security->verifyPermissions($objConfiguration->ModuleID, FULL_CONTROL)) || (!$objConfiguration->ModuleID && !$this->Security->verifyPermissions(MODULE_CONFIGURATION, UPDATE)))
            {
               unset($arrConfiguration[$objConfiguration->ID]);
            }
            elseif($objConfiguration->Encrypted && $objConfiguration->Value)
            {
               $objCryptor = New Cryptor();
               $objConfiguration->Value = $objCryptor->decrypt(base64_decode($objConfiguration->Value));
            }
         }
      }

      return $arrConfiguration;
   }

   /**
    * Searches the Country database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Country[]
    */
   public function searchCountries($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return Country::searchCountries($SearchQuery, $Limit, $Offset);
   }

   /**
    * Searches the Language JSON file
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Language[]
    */
   public function searchLanguages($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return Language::searchLanguages($SearchQuery, $Limit, $Offset);
   }

   /**
    * Searches the Script JSON file
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Script[]
    */
   public function searchScripts($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return Script::searchScripts($SearchQuery, $Limit, $Offset);
   }

   /**
    * Searches the Repository database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Repository[]
    */
   public function searchRepositories($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCore_Repositories', 'Name', 'Repository', 'Name', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the ModificationLogEntry database
    *
    * @param string $SearchQuery
    * @param string $OrderByColumn
    * @param integer $OrderByDirection
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ModificationLogEntry[]
    */
   public function searchModificationLogEntries($SearchQuery, $OrderByColumn = 'Timestamp', $OrderByDirection = DESCENDING, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $OrderByDirection = $OrderByDirection == DESCENDING ? 'DESC' : 'ASC';

      if($OrderByColumn != 'Timestamp')
      {
         if($OrderByColumn != 'ID' && $OrderByColumn != 'TableName' && $OrderByColumn != 'RowID' && $OrderByColumn != 'Login' && $OrderByColumn != 'RemoteHost' && $OrderByColumn != 'ArchonFunction')
         {
            $OrderByColumn = 'Timestamp';
         }

         $OrderBy = "$OrderByColumn $OrderByDirection, Timestamp DESC";
      }
      else
      {
         $OrderBy = "$OrderByColumn $OrderByDirection";
      }

      return $this->searchTable($SearchQuery, 'tblCore_ModificationLog', array('TableName', 'Login', 'RemoteHost', 'ArchonFunction', 'RowID'), 'ModificationLogEntry', $OrderBy, NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the Pattern database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Pattern[]
    */
   public function searchPatterns($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCore_Patterns', 'Name', 'Pattern', 'Name', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the Phrase database
    *
    * @param string $SearchQuery
    * @param integer $PhraseTypeID[optional]
    * @param integer $LanguageID[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Phrase[]
    */
   public function searchPhrases($SearchQuery, $PackageID = 0, $ModuleID = NULL, $PhraseTypeID = 0, $LanguageID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $ConditionsANDTypes = array();
      $ConditionsANDVars = array();

      if($LanguageID && is_natural($LanguageID))
      {
         $ConditionsAND = "LanguageID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $LanguageID;
      }

      if($PackageID && is_natural($PackageID))
      {
         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND .= "PackageID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $PackageID;
      }

      if($ModuleID && is_natural($ModuleID))
      {
         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND .= "ModuleID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $ModuleID;
      }

      if($PhraseTypeID && is_natural($PhraseTypeID))
      {
         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND .= "PhraseTypeID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $PhraseTypeID;
      }

      return $this->searchTable($SearchQuery, 'tblCore_Phrases', array('PhraseName', 'PhraseValue'), 'Phrase', 'PackageID, ModuleID, PhraseTypeID, PhraseName', $ConditionsAND, $ConditionsANDTypes, $ConditionsANDVars, NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the Session database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Session[]
    */
   public function searchSessions($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCore_Sessions', 'RemoteHost', 'Session', NULL, NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the ProductCategory database
    *
    * @param string $SearchQuery
    * @param integer $ParentID[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ProductCategory[]
    */
   public function searchStateProvinces($SearchQuery, $CountryID = NULL, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $ConditionsANDTypes = array();
      $ConditionsANDVars = array();

      if(isset($CountryID) && is_natural($CountryID))
      {
         $ConditionsAND = "CountryID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $CountryID;
      }

      return $this->searchTable($SearchQuery, 'tblCore_StateProvinces', array('StateProvinceName', 'ISOAlpha2'), 'StateProvince', 'CountryID, StateProvinceName', $ConditionsAND, $ConditionsANDTypes, $ConditionsANDVars, NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the UserProfileFieldCategory database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return UserProfileFieldCategory[]
    */
   public function searchUserProfileFieldCategories($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCore_UserProfileFieldCategories', 'UserProfileFieldCategory', 'UserProfileFieldCategory', 'DisplayOrder, UserProfileFieldCategory', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the Usergroup database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Usergroup[]
    */
   public function searchUsergroups($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCore_Usergroups', 'Usergroup', 'Usergroup', 'Usergroup', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the User database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return User[]
    */
   public function searchUsers($SearchQuery, $IsAdminUser = 1, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $arrUsers = $this->searchTable($SearchQuery, 'tblCore_Users', array('Login', 'Email', 'FirstName', 'LastName', 'DisplayName'), 'User', 'Login', 'IsAdminUser = ?', array('integer'), array($IsAdminUser), NULL, array(), array(), $Limit, $Offset);

      if($IsAdminUser)
      {
         $arrUsers[-1] = New User(-1);
         $arrUsers[-1]->dbLoad();

         reset($arrUsers);
      }

      return $arrUsers;
   }

   /**
    * Searches the UserProfileField database
    *
    * @param string $SearchQuery
    * @param boolean $ExcludeDisabledPackageFields[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return UserProfileField[]
    */
   public function searchUserProfileFields($SearchQuery, $ExcludeDisabledPackageFields = true, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $Conditions = $ExcludeDisabledPackageFields ? "PackageID IN (SELECT PackageID FROM tblCore_Packages WHERE Enabled = 1)" : NULL;

      return $this->searchTable($SearchQuery, 'tblCore_UserProfileFields', 'UserProfileField', 'UserProfileField', 'UserProfileFieldCategoryID, DisplayOrder, UserProfileField', $Conditions, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Sends email from public interface to repository email address
    *
    * @param string $FromAddress
    * @param string $Message
    * @param string $Referer
    * @param string $FromName[optional]
    * @param string $Subject[optional]
    * @param string $FromPhone[optional]
    * @param string $Details[optional]
    *
    * @return boolean
    */
   public function sendEmail($FromAddress, $Message, $Referer, $FromName = NULL, $Subject = NULL, $FromPhone = NULL, $Details = NULL, $DetailsFunction = NULL, $DetailsParams = array(), $RepositoryID = NULL)
   {
      $emailValidator = new EmailAddressValidator;

      if(!$FromAddress || !$emailValidator->check_email_address($FromAddress))
      {
         $this->declareError("Could not send email: FromAddress not defined.");
         return false;
      }

      if(!$Message)
      {
         $this->declareError("Could not send email: Message not defined.");
         return false;
      }

      $DisableStyle = $this->PublicInterface->DisableTheme;
      $this->PublicInterface->DisableTheme = true;

      $FromFull = $FromName ? "{$FromName} <{$FromAddress}>" : $FromName;


      $Summary = "{$FromFull} has sent a message:\n";


      $Summary .= "\n\n";

      $Summary .= $Message;

      if(trim($Referer))
      {
         $Summary .= "\n\n";
         $Summary .= "The visitor was at " . $Referer . " when he/she clicked the link to send an email.";
      }

      if($FromPhone)
      {
         $Summary .= "\n\n";
         $Summary .= "If you would like to contact the visitor by phone, the number is {$FromPhone}.";
      }

      if($Details)
      {
         $Summary .= "\n\n";
         $Summary .= $Details;
      }
      if($DetailsFunction)
      {
         $Summary .= "\n\n";
         if($DetailsParams)
         {
            $Summary .= call_user_func_array(array($this, $DetailsFunction), $DetailsParams);
         }
         else
         {
            $Summary .= call_user_func_array(array($this, $DetailsFunction), array());
         }
      }

      $this->PublicInterface->DisableTheme = $DisableStyle;

      if($RepositoryID)
      {
         $repository = New Repository($RepositoryID);
         $repository->dbLoad();
      }
      else
      {
         $repository = $this->Repository;
      }

      if($repository->Email)
      {

         $strSubject = $Subject ? encoding_convert_encoding($repository->Name . ': ' . $Subject, 'ISO-8859-1') : encoding_convert_encoding($repository->Name . ': Materials Information Requested', 'ISO-8859-1');

         if(!mail(encoding_convert_encoding($repository->Email, 'ISO-8859-1'), $strSubject, encoding_convert_encoding($Summary, 'ISO-8859-1'), "From: $FromAddress\r\nCc: $FromAddress"))
         {
            $this->declareError("Could not send email: mail() reported an error for ArchivistMessage.");
            return false;
         }
      }
      else
      {
         $this->declareError("Could not send email: repository email not defined.");
         return false;
      }

      return true;
   }

   /**
    * Sends message to user via the message frame.
    *
    * @param string $Message
    */
   public function sendMessage($Message)
   {
      $Message = $this->processPhrase($Message);

      if($this->AdministrativeInterface)
      {
         ?>
         <script type="text/javascript">
            <!--
            var msg = '<?php echo(encode($Message, ENCODE_JAVASCRIPT)); ?>';

            if(top.frames['message'])
            {
               top.frames['message'].location.href='?p=admin/core/index&f=message&msg=' + msg;
            }
            else if(parent.window.opener && parent.window.opener.top.frames['message'])
            {
               parent.window.opener.top.frames['message'].location.href='?p=admin/core/index&f=message&msg=' + msg;
            }
            else
            {
               alert('<?php echo(encode(strip_tags($Message), ENCODE_JAVASCRIPT)); ?>');
            }
            -->
         </script>
         <?php
      }
      else
      {
         ?>
         <script type="text/javascript">
            <!--
            var msg = '<?php echo(encode(str_replace(';', "\n", $this->processPhrase($Message)), ENCODE_JAVASCRIPT)); ?>';

            if(msg)
            {
               //      if(jQuery)
               //      {
               //         $("#dialog-message").html(msg);
               //         $("#dialog-message").dialog({
               //            modal: true,
               //            buttons: {
               //               Ok: function() {
               //                  $(this).dialog('close');
               //               }
               //            }
               //         });
               //      }
               //      else
               //      {
               alert(msg);
               //      }
            }
            -->
         </script>

         <?php
      }
   }

   /**
    * Sends message to user via the message frame
    * and redirects to specified location.
    *
    * @param string $Message
    * @param string $Location
    */
   public function sendMessageAndRedirect($Message, $Location = NULL, $Params = array())
   {
      $this->sendMessage($Message);
      $this->redirect($Location, $Params);
   }

   /**
    * Sets parameters for a Mixin Method
    *
    * @param integer $ID
    * @return Classification[]
    */
   function setMixinMethodParameters($ClassName, $MixinClassName, $Method, $Callback = NULL, $MixOrder = MIX_AFTER)
   {
      if(!class_exists($ClassName))
      {
         trigger_error("Could not set Mixin method parameters: Class $ClassName does not exist", E_USER_ERROR);
      }

      if(!class_exists($MixinClassName))
      {
         trigger_error("Could not set Mixin method parameters: Mixin Class $MixinClassName does not exist", E_USER_ERROR);
      }

      $arrMethods = get_class_methods($MixinClassName);

      if(array_search($Method, $arrMethods) === false)
      {
         trigger_error("Could not set Mixin method parameters: Method $Method does not exist in Mixin Class $MixinClassName", E_USER_ERROR);
      }

      if($Callback && !is_callable($Callback))
      {
         trigger_error("Could not set Mixin method parameters: Callback Function $Callback is not callable", E_USER_ERROR);
      }

      isset($this->Mixins) or
         $this->Mixins = array();
      isset($this->Mixins[$ClassName]) or
         $this->Mixins[$ClassName] = new stdClass();
      isset($this->Mixins[$ClassName]->Methods) or
         $this->Mixins[$ClassName]->Methods = array();
      isset($this->Mixins[$ClassName]->Methods[$Method]) or
         $this->Mixins[$ClassName]->Methods[$Method] = new stdClass();
      isset($this->Mixins[$ClassName]->Methods[$Method]->Parameters) or
         $this->Mixins[$ClassName]->Methods[$Method]->Parameters = array();
      isset($this->Mixins[$ClassName]->Methods[$Method]->Parameters[$MixinClassName]) or
         $this->Mixins[$ClassName]->Methods[$Method]->Parameters[$MixinClassName] = new stdClass();
      $this->Mixins[$ClassName]->Methods[$Method]->Parameters[$MixinClassName]->Callback = $Callback;
      $this->Mixins[$ClassName]->Methods[$Method]->Parameters[$MixinClassName]->MixOrder = $MixOrder;

      return true;
   }

   /**
    * Stores an object in the database
    *
    * @param mixed $Object, integer $ModuleID, string $Table, string $CheckQuery, array $CheckTypes, array $CheckVars, string $CheckQueryError, array $ProblemFields, array $RequiredFields
    * @return boolean
    */
   public function storeObject($Object, $ModuleID, $Table, $CheckQueries, $CheckTypes, $CheckVars, $CheckQueryError, $ProblemFields = array(), $RequiredFields = array(), $IgnoredFields = array())
   {
      if(!is_array($CheckQueries))
      {
         $CheckQueries = array($CheckQueries);
         $CheckTypes = array($CheckTypes);
         $CheckVars = array($CheckVars);
         $CheckQueryError = array($CheckQueryError);
         $ProblemFields = array($ProblemFields);
      }

      if(is_object($Object))
      {
         $strClassName = get_class($Object);
      }
      else
      {
         return false;
      }

      if(!isset($Object->ID))
      {
         $this->declareError("Could not store $strClassName: $strClassName ID not defined.");
         return false;
      }

      if(!is_natural($Object->ID))
      {
         $this->declareError("Could not store $strClassName: $strClassName ID must be numeric.");
         return false;
      }

      $arrDefaultVars = $this->getClassVars($strClassName);

      // Check to see if required fields are populated and have correct data types.
      foreach($RequiredFields as $fieldName)
      {
         if(!$Object->$fieldName || (is_int($arrDefaultVars[$fieldName]) && !is_natural($Object->$fieldName)) || (is_float($arrDefaultVars[$fieldName]) && !is_numeric($Object->$fieldName)))
         {
            $this->declareError("Could not store $strClassName: $fieldName not defined.");
            $this->ProblemFields[] = $fieldName;
            return false;
         }
      }

      if(!$this->methodExists($Object, 'verifyStorePermissions'))
      {
         if(($Object->ID == 0 && !$this->Security->verifyPermissions($ModuleID, ADD)) || ($Object->ID != 0 && !$this->Security->verifyPermissions($ModuleID, UPDATE)))
         {
            $this->declareError("Could not store $strClassName: Permission Denied.");
            return false;
         }
      }
      else
      {
         if(!$Object->verifyStorePermissions())
         {
            $this->declareError("Could not store $strClassName: Permission Denied. (verifyStorePermissions)");
            return false;
         }
      }

      if($this->classVarExists($Object, 'ParentID') && !is_natural($Object->ParentID))
      {
         $this->declareError("Could not store $strClassName: Parent ID must be numeric.");
         return false;
      }

      if($this->classVarExists($Object, 'ParentID') && $Object->ParentID)
      {
         static $parentExistsPreps = array();
         if(!isset($parentExistsPreps[$strClassName]))
         {
            $query = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ID = ?";
            $parentExistsPreps[$strClassName] = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $parentExistsPreps[$strClassName]->execute($Object->ParentID);
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $numRows = $result->numRows();
         $result->free();

         if($numRows == 0)
         {
            $this->declareError("Could not store $strClassName: Parent not found.");
            return false;
         }
      }

      static $checkPreps = array();
      foreach($CheckQueries as $key => $checkquery)
      {
         if(!isset($checkPreps[$strClassName][$checkquery]))
         {
            $checkPreps[$strClassName][$checkquery] = $this->mdb2->prepare($checkquery, $CheckTypes[$key], MDB2_PREPARE_RESULT);
            if(pear_isError($checkPreps[$strClassName][$checkquery]))
            {
               trigger_error($checkPreps[$strClassName][$checkquery]->getMessage(), E_USER_ERROR);
            }
         }

         $result = $checkPreps[$strClassName][$checkquery]->execute($CheckVars[$key]);
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows() > 0)
         {
            $this->declareError("Could not store $strClassName: {$CheckQueryError[$key]}.");
            $this->ProblemFields = array_merge($this->ProblemFields, $ProblemFields[$key]);
            $result->free();
            return false;
         }
         $result->free();
      }

      if($Object->ID == 0) // Add a new object
      {
         $arrColumns = array();
         $arrQuestions = array();
         $arrTypes = array();
         $arrVars = array();

         foreach($arrDefaultVars as $varName => $defaultValue)
         {
            if($varName != 'ID' && !in_array($varName, $IgnoredFields) && (is_string($defaultValue) || is_numeric($defaultValue)))
            {
               $arrQuotedColumns[] = $this->mdb2->quoteIdentifier($varName);
               $arrQuestions[] = '?';

               if(is_string($defaultValue))
               {
                  $arrTypes[] = 'text';

                  if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
                  {
                     $Object->$varName = encoding_convert_encoding($Object->$varName, 'ISO-8859-1');
                  }
               }
               if(is_int($defaultValue))
               {
                  $arrTypes[] = 'integer';
               }
               if(is_bool($defaultValue))
               {
                  $arrTypes[] = 'boolean';
               }
               if(is_float($defaultValue))
               {
                  $arrTypes[] = 'decimal';
               }

               $arrVars[] = $Object->$varName;
            }
         }

         static $insertPreps = array();
         if(!isset($insertPreps[$strClassName][serialize($IgnoredFields)]))
         {
            $query = "INSERT INTO {$this->mdb2->quoteIdentifier($Table)} (" . implode(', ', $arrQuotedColumns) . ") VALUES (" . implode(', ', $arrQuestions) . ")";
            $insertPreps[$strClassName][serialize($IgnoredFields)] = $this->mdb2->prepare($query, $arrTypes, MDB2_PREPARE_MANIP);
            if(pear_isError($insertPreps[$strClassName][serialize($IgnoredFields)]))
            {
               echo($query);
               trigger_error($insertPreps[$strClassName][serialize($IgnoredFields)]->getMessage(), E_USER_ERROR);
            }
         }

         $affected = $insertPreps[$strClassName][serialize($IgnoredFields)]->execute($arrVars);
         if(pear_isError($affected))
         {
            print_r($insertPreps[$strClassName][serialize($IgnoredFields)]->query);
            print_r($arrVars);
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $result = $checkPreps[$strClassName][reset($CheckQueries)]->execute(reset($CheckVars));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();
         if($row['ID'])
         {
            $Object->ID = $row['ID'];
            $this->log($Table, $row['ID']);
         }
         else
         {
            $this->declareError("Could not add $strClassName: Unable to insert into the database table.");
            return false;
         }
      }
      else // Object->ID != 0 so update the object with given ID
      {

         static $existPreps = array();

         $existPreps[$strClassName] = $existPreps[$strClassName] ? $existPreps[$strClassName] : $this->mdb2->prepare("SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ID = ?", 'integer', MDB2_PREPARE_RESULT);
         $result = $existPreps[$strClassName]->execute($Object->ID);
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['ID'])
         {
            $this->declareError("Could not update $strClassName: $strClassName ID $Object->ID does not exist in the database.");
            return false;
         }

         if($this->classVarExists($Object, 'ParentID') && $Object->ParentID == $Object->ID)
         {
            $this->declareError("Could not update $strClassName: A $strClassName may not contain itself.");
            return false;
         }

         $arrColumns = array();
         $arrQuestions = array();
         $arrTypes = array();
         $arrVars = array();

         foreach($arrDefaultVars as $varName => $defaultValue)
         {
            if($varName != 'ID' && !in_array($varName, $IgnoredFields) && (is_string($defaultValue) || is_numeric($defaultValue)))
            {
               $arrQuotedColumnQuestions[] = "{$this->mdb2->quoteIdentifier($varName)} = ?";

               if(is_string($defaultValue))
               {
                  $arrTypes[] = 'text';

                  if($_ARCHON->db->ServerType == 'MSSQL')
                  {
                     $Object->$varName = encoding_convert_encoding($Object->$varName, 'ISO-8859-1');
                  }
               }
               if(is_int($defaultValue))
               {
                  $arrTypes[] = 'integer';
               }
               if(is_bool($defaultValue))
               {
                  $arrTypes[] = 'boolean';
               }
               if(is_float($defaultValue))
               {
                  $arrTypes[] = 'decimal';
               }

               $arrVars[] = $Object->$varName;
            }
         }

         $arrTypes[] = 'integer';
         $arrVars[] = $Object->ID;

         static $updatePreps = array();
         if(!isset($updatePreps[$strClassName][serialize($IgnoredFields)]))
         {
            $query = "UPDATE {$this->mdb2->quoteIdentifier($Table)} SET " . implode(', ', $arrQuotedColumnQuestions) . " WHERE ID = ?";
            $updatePreps[$strClassName][serialize($IgnoredFields)] = $this->mdb2->prepare($query, $arrTypes, MDB2_PREPARE_MANIP);
            if(pear_isError($updatePreps[$strClassName][serialize($IgnoredFields)]))
            {
               trigger_error($updatePreps[$strClassName][serialize($IgnoredFields)]->getMessage(), E_USER_ERROR);
            }
         }

         $affected = $updatePreps[$strClassName][serialize($IgnoredFields)]->execute($arrVars);
         if(pear_isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $this->log("$Table", $Object->ID);
      }

      return true;
   }

   /**
    * Loads IDs and NameFields from a table into an associative array
    *
    * Returns an array of $ClassName names/titles/etc. (specified by $NameField)
    * which is sorted by $OrderBy and has IDs as keys.
    *
    * Intended for use by inputs needing to list object titles with their IDs
    *
    * @param string $Table
    * @param string $ClassName
    * @param string $OrderBy[optional]
    * @param string $Condition[optional]
    * @return array($ID => $NameField)
    */
   public function loadObjectList($Table, $ClassName, $NameField = 'Title', $OrderBy = NULL, $Condition = NULL, $ConditionTypes = NULL, $ConditionVars = NULL)
   {
      if(!$Table)
      {
         $this->declareError("Could not load Table: Table Name not defined.");
         return false;
      }

      if(!$ClassName)
      {
         $this->declareError("Could not load Table: Class Name not defined.");
         return false;
      }

      if(!class_exists($ClassName))
      {
         $this->declareError("Could not load Table: Class {$ClassName} does not exist.");
         return false;
      }

      if(!$NameField)
      {
         $this->declareError("Could not load Table: Name Field not defined.");
         return false;
      }

      //this would be nice but the behavior is inconsistent
//        if(!$this->classVarExists($ClassName, $NameField))
//        {
//           $this->declareError("Could not load Table: Name Field {$NameField} does not exist in Class {$ClassName}.");
//           return false;
//        }



      if(isset($Condition) && (!isset($ConditionTypes) || !isset($ConditionVars)))
      {
         debug_print_backtrace();
         trigger_error("loadTable not being used correctly!", E_USER_ERROR);
      }
      elseif(!isset($Condition))
      {
         $ConditionTypes = array();
         $ConditionVars = array();
      }

      $arrNameFields = array();

      if($Condition)
      {
         $ConditionQuery = "WHERE ($Condition)";
      }

      if(!$OrderBy)
      {
         $OrderBy = $NameField;
      }

      if(isset($this->MemoryCache['Lists'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)]))
      {
         return $this->MemoryCache['Lists'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)];
      }


      $OrderByQuery = "ORDER BY {$this->mdb2->escape($OrderBy)}";


      $query = "SELECT ID,{$this->mdb2->quoteIdentifier($NameField)} FROM {$this->mdb2->quoteIdentifier($Table)} {$ConditionQuery} {$OrderByQuery}";
      $prep = $this->mdb2->prepare($query, $ConditionTypes, MDB2_PREPARE_RESULT);
      $result = $prep->execute($ConditionVars);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->MemoryCache['Lists'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)][$row['ID']] = $row[$NameField];
         $arrNameFields[$row['ID']] = $this->MemoryCache['Lists'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)][$row['ID']];
      }
      $result->free();
      $prep->free();

      reset($arrNameFields);

//        $this->MemoryCache['Lists'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)] = $arrNameFields;

      return $arrNameFields;
   }

   /**
    * Loads all rows from a table into an array of objects
    *
    * Returns an array of $ClassName objects which
    * is sorted by $OrderBy and has IDs as keys.
    *
    * If the objects are nested, loadTable will also connect
    * Parents.
    *
    * @param string $Table
    * @param string $ClassName
    * @param string $OrderBy[optional]
    * @param string $Condition[optional]
    * @param string $ConditionTypes[optional]
    * @param string $ConditionVarsl[optional]
    * @param boolean $NoMemoryCache[optional]
    * @param array $SelectFields[optional]
    * @param boolean $LoadParents[optional]
    * @return $ClassName[]
    */
   public function loadTable($Table, $ClassName, $OrderBy = NULL, $Condition = NULL, $ConditionTypes = NULL, $ConditionVars = NULL, $NoMemoryCache = false, $SelectFields = array(), $LoadParents = true)   {
      if(!$Table)
      {
         $this->declareError("Could not load Table: Table Name not defined.");
         return false;
      }

      if(!$ClassName)
      {
         $this->declareError("Could not load Table: Class Name not defined.");
         return false;
      }

      if(!class_exists($ClassName))
      {
         $this->declareError("Could not load Table: Class $ClassName does not exist.");
         return false;
      }

      if(isset($Condition) && (!isset($ConditionTypes) || !isset($ConditionVars)))
      {
         debug_print_backtrace();
         trigger_error("loadTable not being used correctly!", E_USER_ERROR);
      }
      elseif(!isset($Condition))
      {
         $ConditionTypes = array();
         $ConditionVars = array();
      }

      if(!empty($SelectFields) && is_array($SelectFields))
      {
         //TODO: implement field check?

         $selectFields = implode(',', $SelectFields);
      }

      $selectFields = ($selectFields) ? $selectFields : '*';


      if(isset($this->MemoryCache['Tables'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)]) && !$NoMemoryCache)
      {
         return $this->MemoryCache['Tables'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)];
      }

      $arrObjects = array();


      if($Condition)
      {
         $ConditionQuery = "WHERE ($Condition)";
      }

      if($OrderBy)
      {
         $OrderByQuery = "ORDER BY {$this->mdb2->escape($OrderBy)}";
      }

      $query = "SELECT {$selectFields} FROM {$this->mdb2->quoteIdentifier($Table)} {$ConditionQuery} {$OrderByQuery}";
      $prep = $this->mdb2->prepare($query, $ConditionTypes, MDB2_PREPARE_RESULT);
      $result = $prep->execute($ConditionVars);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->MemoryCache['Objects'][$ClassName][$row['ID']] = New $ClassName($row);
         $arrObjects[$row['ID']] = $this->MemoryCache['Objects'][$ClassName][$row['ID']];

         if($row['ParentID'])
         {
            $arrParents[$row['ID']] = $row['ParentID'];
         }
      }
      $result->free();
      $prep->free();

      if(!empty($arrParents) && $LoadParents == true)
      {
         foreach($arrParents as $ID => $ParentID)
         {
            if($arrObjects[$ParentID] )
            {
               if(!$NoMemoryCache)
               {
                  $this->MemoryCache['Objects'][$ClassName][$ID]->Parent = $this->MemoryCache['Objects'][$ClassName][$ParentID];
               }

               $arrObjects[$ID]->Parent = $arrObjects[$ParentID];
            }
         }
      }

      reset($arrObjects);

      if(!$NoMemoryCache)
      {
         $this->MemoryCache['Tables'][$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)] = $arrObjects;
      }

      return $arrObjects;
   }

   public function updateObjectRelations($Object, $ModuleID, $RelatedClassName, $Table, $RelatedObjectTable, $arrRelatedIDs, $Action = NULL)
   {
      if(is_object($Object))
      {
         $strClassName = get_class($Object);
      }
      else
      {
         return false;
      }

      if(!isset($Object->ID))
      {
         $this->declareError("Could not relate to {$strClassName}: {$strClassName} ID not defined.");
         return false;
      }

      if(!is_natural($Object->ID))
      {
         $this->declareError("Could not relate to {$strClassName}: {$strClassName} ID must be numeric.");
         return false;
      }

      if(!$this->methodExists($Object, 'verifyStorePermissions'))
      {
         if(($Object->ID == 0 && !$this->Security->verifyPermissions($ModuleID, ADD)) || ($Object->ID != 0 && !$this->Security->verifyPermissions($ModuleID, UPDATE)))
         {
            $this->declareError("Could not store {$strClassName}: Permission Denied.");
            return false;
         }
      }
      else
      {
         if(!$Object->verifyStorePermissions())
         {
            $this->declareError("Could not store {$strClassName}: Permission Denied.");
            return false;
         }
      }

      if(!is_array($arrRelatedIDs) || !$arrRelatedIDs)
      {
         $this->declareError("Could not relate {$RelatedClassName}: No {$RelatedClassName} IDs are defined.");
         return false;
      }

      $completeSuccess = true;


      $strRelatedClassID = $RelatedClassName . "ID";
      $strClassID = $strClassName . "ID";



      static $currentPreps = array();


      if($Action == ADD)
      {
         $arrNewRelatedIDs = $arrRelatedIDs;
         $arrNewUnrelatedIDs = array();
      }
      elseif($Action == DELETE)
      {
         $arrNewRelatedIDs = array();
         $arrNewUnrelatedIDs = $arrRelatedIDs;
      }
      else
      {
         if(!isset($currentPreps[$strClassName][$RelatedClassName]))
         {
            $query = "SELECT {$this->mdb2->quoteIdentifier($strRelatedClassID)} FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ?";
            $currentPreps[$strClassName][$RelatedClassName] = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }

         $result = $currentPreps[$strClassName][$RelatedClassName]->execute($Object->ID);
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $arrCurrentRelatedIDs = array();

         while($row = $result->fetchRow())
         {
            $arrCurrentRelatedIDs[] = $row[$strRelatedClassID];
         }
         $result->free();

         $arrNewRelatedIDs = array_diff($arrRelatedIDs, $arrCurrentRelatedIDs);
         $arrNewUnrelatedIDs = array_diff($arrCurrentRelatedIDs, $arrRelatedIDs);


         if($arrNewRelatedIDs == array(0))
         {
            $arrNewRelatedIDs = array();
         }
      }

      static $existPreps = array();
      static $checkPreps = array();
      static $insertPreps = array();
      static $deletePreps = array();


      /* check if the arrays are full to avoid preparing statements that aren't used */

      if(!empty($arrNewRelatedIDs))
      {
         // if NULL is passed as RelatedObjectTable, exists checks will not be performed
         // this to be used for relations to JSONObjects with static JSON files
         if(!isset($existPreps[$RelatedClassName]) && isset($RelatedObjectTable))
         {
            $query = "SELECT ID FROM {$this->mdb2->quoteIdentifier($RelatedObjectTable)} WHERE ID = ?";
            $existPreps[$RelatedClassName] = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }

         if(!isset($checkPreps[$strClassName][$RelatedClassName]))
         {
            $query = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ? AND {$this->mdb2->quoteIdentifier($strRelatedClassID)} = ?";
            $checkPreps[$strClassName][$RelatedClassName] = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
         }

         if(!isset($insertPreps[$strClassName][$RelatedClassName]))
         {
            $query = "INSERT INTO {$this->mdb2->quoteIdentifier($Table)} ({$this->mdb2->quoteIdentifier($strClassID)}, {$this->mdb2->quoteIdentifier($strRelatedClassID)}) VALUES (?, ?)";
            $insertPreps[$strClassName][$RelatedClassName] = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
         }
      }

      //if(!empty($arrCurrentRelatedIDs))
      if(!empty($arrNewUnrelatedIDs))
      {
         if(!isset($checkPreps[$strClassName][$RelatedClassName]))
         {
            $query = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ? AND {$this->mdb2->quoteIdentifier($strRelatedClassID)} = ?";
            $checkPreps[$strClassName][$RelatedClassName] = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
         }

         if(!isset($deletePreps[$strClassName][$RelatedClassName]))
         {
            $query = "DELETE FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ? AND {$this->mdb2->quoteIdentifier($strRelatedClassID)} = ?";
            $deletePreps[$strClassName][$RelatedClassName] = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
         }
      }




      foreach($arrNewRelatedIDs as $key => $newRelatedID)
      {

         if(isset($existPreps[$RelatedClassName]))
         {
            $result = $existPreps[$RelatedClassName]->execute($newRelatedID);
            if(pear_isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $row = $result->fetchRow();
            $result->free();

            if(!$row['ID'])
            {
               $this->declareError("Could not update {$RelatedClassName}: {$RelatedClassName} ID {$newRelatedID} does not exist in the database.");
               unset($arrNewRelatedIDs[$key]);
               $completeSuccess = false;
               continue;
            }
         }

         $result = $checkPreps[$strClassName][$RelatedClassName]->execute(array($Object->ID, $newRelatedID));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            $this->declareError("Could not relate {$RelatedClassName}: {$RelatedClassName} ID {$newRelatedID} already related to {$strClassName} ID {$Object->ID}.");
            unset($arrNewRelatedIDs[$key]);
            $completeSuccess = false;
            continue;
         }


         $affected = $insertPreps[$strClassName][$RelatedClassName]->execute(array($Object->ID, $newRelatedID));
         if(pear_isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $result = $checkPreps[$strClassName][$RelatedClassName]->execute(array($Object->ID, $newRelatedID));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['ID'])
         {
            $this->declareError("Could not relate {$RelatedClassName}: Unable to update the database table.");
            unset($arrNewRelatedIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $this->log($Table, $row['ID']);
         $this->log("tbl" . pluralize($strClassName) . "_" . pluralize($strClassName), $Object->ID);
      }

      foreach($arrNewUnrelatedIDs as $key => $newUnrelatedID)
      {


         $result = $checkPreps[$strClassName][$RelatedClassName]->execute(array($Object->ID, $newUnrelatedID));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $RowID = $row['ID'];

         if(!$row['ID'])
         {
            $this->declareError("Could not unrelate {$RelatedClassName}: {$RelatedClassName} ID {$newUnrelatedID} not related to {$strClassName} ID {$Object->ID}.");
            unset($arrNewUnrelatedIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $affected = $deletePreps[$strClassName][$RelatedClassName]->execute(array($Object->ID, $newUnrelatedID));
         if(pear_isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $result = $checkPreps[$strClassName][$RelatedClassName]->execute(array($Object->ID, $newUnrelatedID));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            $this->declareError("Could not unrelate {$RelatedClassName}: Unable to update the database table.");
            unset($arrNewUnrelatedIDs[$key]);
            $completeSuccess = false;
            continue;
         }
         else
         {

            $this->log($Table, $RowID);
            $this->log("tbl" . pluralize($strClassName) . "_" . pluralize($strClassName), $Object->ID);
         }
      }

      return $completeSuccess;
   }

   /**
    * Searches the a table in the database
    *
    * Note: The first value it $Tables will be the table
    * from which the object data will be loaded
    *
    * @param string $SearchQuery
    * @param string|string[] $Tables
    * @param string|string[] $Fields
    * @param string $ClassName
    * @param string $OrderBy[optional]
    * @param string $AdditionalConditionsAND[optional]
    * @param string $AdditionalConditionsOR[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @param string[] $SelectFields[optional]
    * @return $ClassName[]
    */
   public function searchTable($SearchQuery, $Tables, $Fields, $ClassName, $OrderBy = NULL, $AdditionalConditionsAND = NULL, $AdditionalConditionsANDTypes = array(), $AdditionalConditionsANDVars = array(), $AdditionalConditionsOR = NULL, $AdditionalConditionsORTypes = array(), $AdditionalConditionsORVars = array(), $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0, $SelectFields = array())
   {
      if(is_natural($AdditionalConditionsANDVars))
      {
         debug_print_backtrace();
         trigger_error('searchTables used incorrectly!', E_USER_ERROR);
      }

      if(!$Tables)
      {
         $this->declareError("Could not search Table: Table Names not defined.");
         return false;
      }
      /*
        if(!$Fields)
        {
        $this->declareError("Could not search Table: Table Fields not defined.");
        return false;
        }
       */
      if(!$ClassName)
      {
         $this->declareError("Could not search Table: Class Name not defined.");
         return false;
      }

      if(!class_exists($ClassName))
      {
         $this->declareError("Could not search Table: Class $ClassName does not exist.");
         return false;
      }

      if(!is_array($Tables))
      {
         $Tables = array($Tables);
      }

      if($Fields)
      {
         if(!is_array($Fields))
         {
            $Fields = array($Fields);
         }
         else
         {
            // We want the keys to just be in numerical order.
            // Sort does this, so I am just sorting for now
            // instead of writing a separate function to do this.
            sort($Fields);
         }
      }

      if(!empty($SelectFields) && is_array($SelectFields))
      {
         //TODO: implement field check?
//           $badFields = array_diff($Fields, array_keys(get_object_vars($Object)));
//           if(!empty($badFields))
//           {
//              $this->declareError("Could not load {$strClassName}: Field(s) '".implode(',', $badFields)."' do not exist in Class {$strClassName}.");
//              return false;
//           }
         $strTablePrefix = $this->mdb2->quoteIdentifier(reset($Tables)) . '.';
         $tmp = array();
         foreach($SelectFields as $fieldName)
         {
            $tmp[] = $strTablePrefix . $fieldName;
         }
         $selectFields = implode(',', $tmp);
      }

      $selectFields = ($selectFields) ? $selectFields : $this->mdb2->quoteIdentifier(reset($Tables)) . '.*';


      $arrObjects = array();

      //$SearchQuery = $this->db->escape_string($SearchQuery);
      //$Tables = $this->db->escape_string($Tables);
      //$Fields = $this->db->escape_string($Fields);
      //$OrderBy = $this->db->escape_string($OrderBy);
      //$Limit = $this->db->escape_string($Limit);
      //$Offset = $this->db->escape_string($Offset);

      $arrWords = $this->createSearchWordArray($SearchQuery);

      $wherequery = '';
      $wheretypes = array();
      $wherevars = array();

      if(!empty($arrWords))
      {
         foreach($arrWords as $word)
         {
            $i++;
            if($word{0} == '-')
            {
               $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
               $wherequery .= '(';

               foreach($Fields as $key => $Field)
               {
                  $wherequery .= "{$this->mdb2->quoteIdentifier($Field)} NOT LIKE ?";
                  $wheretypes[] = 'text';
                  $wherevars[] = "%$word%";

                  if($key + 1 < count($Fields))
                  {
                     $wherequery .= ' AND ';
                  }
               }

               $wherequery .= ')';
            }
            else
            {
               $wherequery .= '(';
               foreach($Fields as $key => $Field)
               {
                  $wherequery .= "{$this->mdb2->quoteIdentifier($Field)} LIKE ?";
                  $wheretypes[] = 'text';
                  $wherevars[] = "%$word%";

                  if($key + 1 < count($Fields))
                  {
                     $wherequery .= ' OR ';
                  }
               }

               $wherequery .= ')';
            }

            if($i < count($arrWords))
            {
               $wherequery .= " AND ";
            }
         }
      }
      else
      {

         //What are we just giving the database busy work here?
//            if(!empty($Fields))
//            {
//                foreach($Fields as $key => $Field)
//                {
//                    $wherequery .= "{$this->mdb2->quoteIdentifier($Field)} LIKE '%%'";
//
//                    if($key + 1 < count($Fields))
//                    {
//                        $wherequery .= ' OR ';
//                    }
//                }
//            }
      }

      if(!$wherequery)
      {
         $wherequery = '1 = 1';
      }

      // If our query is just a number, try to match it
      // directly to an ID from the table.
      if(is_natural($SearchQuery) && $SearchQuery > 0)
      {
         $wherequery .= " OR {$this->mdb2->quoteIdentifier(reset($Tables))}.ID = ?";
         $wheretypes[] = 'integer';
         $wherevars[] = $SearchQuery;
      }

      if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
      {
         $limitparams = array($Limit, $Offset);
      }
      elseif(is_natural($Offset) && $Offset > 0)
      {
         $limitparams = array(4294967295, $Offset);
      }
      elseif(is_natural($Limit) && $Limit > 0)
      {
         $limitparams = array($Limit);
      }
      else
      {
         $limitparams = array(4294967295);
      }


      if($AdditionalConditionsAND)
      {
         if($AdditionalConditionsOR)
         {
            $wherequery = "WHERE (($wherequery) OR ($AdditionalConditionsOR)) AND ($AdditionalConditionsAND)";
            $wheretypes = array_merge($wheretypes, $AdditionalConditionsORTypes, $AdditionalConditionsANDTypes);
            $wherevars = array_merge($wherevars, $AdditionalConditionsORVars, $AdditionalConditionsANDVars);
         }
         else
         {
            $wherequery = "WHERE ($wherequery) AND ($AdditionalConditionsAND)";
            $wheretypes = array_merge($wheretypes, $AdditionalConditionsANDTypes);
            $wherevars = array_merge($wherevars, $AdditionalConditionsANDVars);
         }
      }
      elseif($AdditionalConditionsOR)
      {
         $wherequery = "WHERE ($wherequery) OR ($AdditionalConditionsOR)";
         $wheretypes = array_merge($wheretypes, $AdditionalConditionsORTypes);
         $wherevars = array_merge($wherevars, $AdditionalConditionsORVars);
      }
      else
      {
         $wherequery = "WHERE ($wherequery)";
      }

      $orderquery = $OrderBy ? 'ORDER BY ' . $this->mdb2->escape($OrderBy) : '';

      $tablestring = $this->mdb2->escape(implode(', ', $Tables));

      $query = "SELECT " . $selectFields . " FROM $tablestring $wherequery $orderquery";
      call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
      $prep = $this->mdb2->prepare($query, $wheretypes, MDB2_PREPARE_RESULT);
      $result = $prep->execute($wherevars);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrObjects[$row['ID']] = New $ClassName($row);
      }
      $result->free();
      $prep->free();

      reset($arrObjects);

      return $arrObjects;
   }

   public function logStats($Table, $Params, $Stats)
   {
      if(!$Table || $Table == '' || !is_array($Params) || !is_array($Stats) || empty($Params) || empty($Stats))
      {
         $this->declareError("Error: logStats -- Incorrect Parameters");
         return false;
      }

      static $selectPreps = array();
      static $insertPreps = array();
      static $updatePreps = array();


      $query = "SELECT * FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->dbSerialize($Params, " AND ", false)}";
      $result = $this->mdb2->query($query);

      if(pear_isError($result))
      {
         echo($query);
         var_dump($Params);
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      if($result->numRows())
      {
         //update stats
         $row = $result->fetchRow();
         foreach($row as $key => $val)
         {
            if($Stats[$key])
            {
               $Stats[$key] += $val;
            }
         }

         $result->free();


         $query = "UPDATE {$this->mdb2->quoteIdentifier($Table)} SET {$this->dbSerialize($Stats, ", ", false)} WHERE {$this->dbSerialize($Params, " AND ", false)}";
         $affected = $this->mdb2->exec($query);


         if(pear_isError($affected))
         {
            echo($query);
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
      }
      else
      {
         $result->free();

         $data = array_merge($Params, $Stats);

         $query = "INSERT INTO {$this->mdb2->quoteIdentifier($Table)} (" . implode(", ", array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
         $affected = $this->mdb2->exec($query);
      }
      return true;
   }

   public function dbSerialize($params, $delimiter = ", ", $prepare = true)
   {
      $string = "";
      foreach($params as $key => $val)
      {
         if($string != "")
         {
            $string .= $delimiter;
         }
         $string .= $key;
         if($prepare)
         {
            $string .= " = ?";
         }
         else
         {
            $string .= " = '{$val}'";
         }
      }
      return $string;
   }

   /**
    * Contains the URL to The Archon Project website.
    *
    * @var string
    */
   public $ArchonURL = 'http://www.archon.org/';
   /**
    * Contains and array of Configuration objects,
    * each of which represents a configuration directive in
    * the Configuration table.
    *
    * @var Configuration[]
    */
   public $Configuration = NULL;
   /**
    * Configuration directives set by config.inc.php
    *
    * @var object
    */
   public $config = NULL;
   /**
    * Contains the most recent year of Archon development.
    *
    * @var int
    */
   public $CopyrightYear = 2017;
   public $db = NULL;
   /**
    * When this variable is set to true, Archon is disabled to the public.
    *
    * Typically there is no reason to modify this variable directly,
    * as there is a configuration directive that will disable Archon as well.
    *
    * @var boolean
    */
   public $Disabled = false;
   /**
    * If an error occurs within the API, this value will
    * be set to a text description of the error.
    *
    * @var string
    */
   public $Error = NULL;
   /**
    * @var array
    */
   public $MemoryCache = array();
   /**
    * @var array
    */
   public $Modules = array();
   /**
    * @var array
    */
   public $Packages = array();
   /**
    * @var array
    */
   public $ProblemFields = array();
   /**
    * @var string
    */
   public $ProductName = 'Archon';
   /**
    * @var string
    */
   public $ReleaseDate = '2017.6.26';
   /**
    * @var string
    */
   public $Revision = '3';
   /**
    * Repository object containing the default repository
    * (unless specified otherwise by setrepositoryid
    *
    * @var Repository
    */
   public $Repository = NULL;
   /**
    * @var string
    */
   public $RootDirectory = NULL;
   public $ScriptName = NULL;
   /**
    * @var Security
    */
   public $Security = NULL;
   /**
    * Contains an array of the $Packages array's associative keys sorted in
    * topological order.
    *
    * @var string[]
    */
   public $TopologicalPackageKeys = array();
   /**
    * Contains the version of the Archon codebase.
    *
    * @var string
    */
   public $Version = NULL;
   public $QueryLog = NULL;
   public $TestingError = '';

}

$_ARCHON->mixClasses('Archon', 'Core_Archon');
?>
