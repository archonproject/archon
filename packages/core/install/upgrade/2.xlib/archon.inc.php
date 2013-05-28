<?php
abstract class Core_Archon
{
    /**
     * Adds additional user interface function for extending functionality of other user interface functions
     *
     * @param string $FunctionName function being extended
     * @param string $AddedFunctionName
     */
    public function addAdministrativeUIFunction($FunctionName, $AddedFunctionName)
    {
        if(!function_exists($AddedFunctionName))
        {
            $this->declareError("Could not add AdministrativeUIFunction: Function $AddedFunctionName does not exist.");
        }

        $this->AdministrativeInterface->UIFunctions[$FunctionName][$AddedFunctionName] = $AddedFunctionName;
    }




    /**
     * Adds an administrative widget
     *
     * @param string $WidgetName
     * @param string $Description
     * @param string $UtilityCode
     * @param integer $PackageID
     * @param string $PackageVersion
     * @param string[] $Extensions
     * @param boolean $InputFile[optional]
     *
     * @return boolean
     */
    public function addAdministrativeWidget($PackageID, $WidgetCode)
    {
        return;

        if(!$WidgetCode)
        {
            $this->declareError("Could not add AdministrativeWidget: Widget Code not defined.");
            return false;
        }
        else if(!$this->Packages[$PackageID])
        {
            $this->declareError("Could not add AdministrativeWidget: Package $PackageID is not installed.");
            return false;
        }
        else if(!$this->AdministrativeInterface)
        {
            $this->declareError("Could not add AdministrativeWidget: The AdministrativeInterface is not active.");
            return false;
        }

        $this->AdministrativeInterface->Widgets[$PackageID][] = $WidgetCode;

        return true;
    }





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
        else if(!$this->Packages[$PackageID])
        {
            $this->declareError("Could not add DatabaseImportUtility: Package $PackageID is not installed.");
            return false;
        }
        else if(version_compare($this->Packages[$PackageID]->DBVersion, $PackageVersion) != 0)
        {
            $this->declareError("Could not add DatabaseImportUtility: Package {$this->Packages[$PackageID]->APRCode} version $PackageVersion must be installed (installed version is {$this->Packages[$Package]->DBVersion}).");
            return false;
        }

        $this->db->ImportUtilities[$PackageID][$UtilityCode]->Extensions = $Extensions;
        $this->db->ImportUtilities[$PackageID][$UtilityCode]->InputFile = $InputFile;

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
        else if(!$this->PublicInterface)
        {
            $this->declareError("Could not add PublicSearchFunction: The PublicInterface is not active.");
            return false;
        }

        $this->PublicInterface->PublicSearchFunctions[$FunctionName]->FunctionName = $FunctionName;
        $this->PublicInterface->PublicSearchFunctions[$FunctionName]->DisplayOrder = $DisplayOrder;

        return true;
    }





    /**
     * Clears Memory Cache of the entry denoted by $Table
     *
     * @param string $Table
     * @return boolean
     */
    public function clearCacheObjectEntry($ClassName, $ID)
    {
        unset($this->MemoryCache->Objects[$ClassName][$ID]);

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
        unset($this->MemoryCache->Tables[$Table]);

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
                else if(is_array($var1->$name))
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
                else if($var1->$name !== $var2->$name)
                {
                    $consensus->$name = MULTIPLE_VALUES;
                }
                else
                {
                    $consensus->$name = $var1->$name;
                }
            }
        }
        else if(is_array($var1))
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
                else if(is_array($var1[$key]))
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
                else if($var1[$key] !== $var2[$key])
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
                else if($declength == 2)
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
        $String = preg_replace('/%|\\\/u', "", $String);
        $String = str_replace('_', $this->mdb2->escape('_', true), $String);
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
            $arrWords = split(" ", $String);
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
            if (PEAR::isError($result)) {
                trigger_error($result->getMessage(), E_USER_ERROR);
            }

            while($row = $result->fetchRow())
            {
                $dbStats->Tables[$row['Name']]->Rows = $row['Rows'];
                $dbStats->Tables[$row['Name']]->DiskUsed = formatsize($row['Data_length'] + $row['Index_length']);
                $useddiskspace += $row['Data_length'] + $row['Index_length'];
                $freediskspace = $row['Max_data_length'];
            }
            $result->free();

            $dbStats->DiskUsed = formatsize($useddiskspace);
            $dbStats->DiskFree = formatsize($freediskspace);
        }
        else if(substr_count($this->db->ServerType, 'MSSQL'))
        {
            $this->mdb2->loadModule('Manager');

            $query = "DBCC showfilestats;";
            $result = $this->mdb2->query($query);
            if(!PEAR::isError($result))
            {
                $row = $result->fetchRow();
                $result->free();
            }

            // Fixes weird issue where SQL Server doesn't like the immediate next query.
            $this->mdb2->query("SELECT * FROM tblCore_Configuration WHERE 1 = 0;");
            if(!PEAR::isError($result))
            {
                $row = $result->fetchRow();
                $result->free();
            }

            $mdb2Tables = $this->mdb2->listTables();

            foreach($mdb2Tables as $tblName)
            {
                $query = "sp_MStablespace {$this->mdb2->quoteIdentifier($tblName)}";
                $result = $this->mdb2->query($query);
                if(!PEAR::isError($result))
                {
                    $usagerow = $result->fetchRow();
                    $result->free();
                }

                $dbStats->Tables[$tblName]->Rows = $usagerow['Rows'];
                $dbStats->Tables[$tblName]->DiskUsed = formatsize(1024 * ($usagerow['DataSpaceUsed'] + $usagerow['IndexSpaceUsed']));
            }

            $dbStats->DiskUsed = formatsize($row['TotalExtents'] * 64 * 1024);
            $dbStats->DiskFree = formatsize($row['UsedExtents'] * 64 * 1024);
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

        if(!$this->mixinMethodExists($Object, 'verifyDeletePermissions'))
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
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        $row = $result->fetchRow();
        $result->free();

        if(!$row['ID'])
        {
            $this->declareError("Could not delete $strClassName: $strClassName ID $ID not found in database.");
            return false;
        }

        if($this->mixinClassVarExists($Object, 'ParentID') && $this->mixinMethodExists($Object, 'dbDelete'))
        {
        	static $childdrenPreps = array();
        	if(!isset($childrenPreps[$strClassName]))
        	{
        		$childquery = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ParentID = ?";
        		$childdrenPreps[$strClassName] = $this->mdb2->prepare($childquery, 'integer', MDB2_PREPARE_RESULT);
        	}

        	$result = $childdrenPreps[$strClassName]->execute($ID);
            if (PEAR::isError($result)) {
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
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        if($affected < 1)
        {
        	$_ARCHON->declareError("Could not delete $strClassName: Unable to delete from the database table.");
            return false;
        }

        $this->log($Table, $Object->ID);

        $Object->ID = 0;

        return $ID;
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
        else if(is_array($_REQUEST['serverfiles']))
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
    * @return string[] array of file contents
    */
    public function getAllIncomingFiles()
    {
        $arrFiles = array();

        if($_FILES['uploadfile']['tmp_name'])
        {
            $arrFiles = file_get_contents_array($_FILES['uploadfile']['tmp_name']);
        }
        else if(is_array($_REQUEST['serverfiles']) && !empty($_REQUEST['serverfiles']))
        {
            $_REQUEST['serverfiles'] = preg_replace('/[\\/\\\\]/u', '', $_REQUEST['serverfiles']);

            foreach($_REQUEST['serverfiles'] as $Filename)
            {
                if(file_exists("incoming/" . $Filename))
                {
                    $arrFiles = array_merge($arrFiles, file_get_contents_array("incoming/" . $Filename));
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
    public function getAllLanguages()
    {
        return $this->loadTable("tblCore_Languages", "Language", "DisplayOrder, LanguageLong");
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
    public function getAllScripts()
    {
        return $this->loadTable("tblCore_Scripts", "Script", "DisplayOrder, ScriptEnglishLong");
    }


    /**
    * Retrieves all Modules from the database
    *
    * The returned array of Module objects
    * is sorted by Package, Module and has IDs as keys.
    *
    * @return Module[]
    */
    public function getAllModules($LanguageID = 0, $ExcludeDisabledPackages = true, $ExcludeNonBrowsableModules = false)
    {
        if(!$LanguageID)
        {
            if($this->Security->Session)
            {
                $LanguageID = $this->Security->Session->getLanguageID();
            }
            else
            {
                $LanguageID = CONFIG_DEFAULT_LANGUAGE;
            }
        }
        else if(!is_natural($LanguageID))
        {
            $this->declareError("Could not get Modules: Language ID not defined.");
        }

        $arrModules = array();

        $arrPackages = $this->getAllPackages($LanguageID, $ExcludeDisabledPackages);

        if(!$ExcludeNonBrowsableModules)
        {
            $prep = $this->mdb2->prepare('SELECT tblCore_Modules.* FROM tblCore_Modules WHERE tblCore_Modules.PackageID = ? ORDER BY Script', 'integer', MDB2_PREPARE_RESULT);
        }
        else
        {
            $prep = $this->mdb2->prepare('SELECT tblCore_Modules.* FROM tblCore_Modules WHERE tblCore_Modules.PackageID = ? AND Browsable = 1 ORDER BY Script', 'integer', MDB2_PREPARE_RESULT);
        }

        foreach($arrPackages as $ID => $objPackage)
        {
            if(!is_natural($ID))
            {
                continue;
            }

            $result = $prep->execute($ID);
	        if (PEAR::isError($result)) {
	            trigger_error($result->getMessage(), E_USER_ERROR);
	        }
            while($row = $result->fetchRow())
            {
                if(!$arrModules[$row['ID']])
                {
                    $objModule = New Module($row);
                    $objModule->Package = $objPackage;

                    $arrModules[$objModule->ID] = $objModule;
                }
            }
            $result->free();
        }
        $prep->free();

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
    public function getAllPackages($LanguageID = 0, $ExcludeDisabledPackages = true)
    {
        $arrPackages = array();

        if(!$LanguageID)
        {
            if($this->Security->Session)
            {
                $LanguageID = $this->Security->Session->getLanguageID();
            }
            else
            {
                $LanguageID = CONFIG_DEFAULT_LANGUAGE;
            }
        }
        else if(!is_natural($LanguageID))
        {
            $this->declareError("Could not get Packages: Language ID not defined.");
        }

        if($ExcludeDisabledPackages)
        {
            $ExcludeDisabledPackagesQuery = "AND tblCore_Packages.Enabled = '1'";
        }

        $query = "SELECT tblCore_Packages.* FROM tblCore_Packages WHERE 1 = 1 $ExcludeDisabledPackagesQuery ORDER BY APRCode";
        $result = $this->mdb2->query($query);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        while($row = $result->fetchRow())
        {
            if(!$arrPackages[$row['ID']])
            {
                $arrPackages[$row['ID']] = New Package($row);
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
        return $this->loadTable("tblCore_PhraseTypes", "PhraseType", "PhraseType");
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
    * Returns any array of installed themes.
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
    public function getAllUsergroups()
    {
        $arrUsergroups = $this->loadTable("tblCore_Usergroups", "Usergroup", "Usergroup");

        foreach($arrUsergroups as &$objUsergroup)
        {
            $objUsergroup->dbLoadPermissions();
        }

        reset($arrUsergroups);

        return $arrUsergroups;
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
            if($objUser->UsergroupID)
            {
                $objUser->Usergroup = $arrUsergroups[$objUser->UsergroupID];
            }

            $objUser->dbLoadPermissions();
        }

        $arrUsers[-1] = New User(-1);
        $arrUsers[-1]->dbLoad();

        reset($arrUsers);

        return $arrUsers;
    }





    /**
    * Retrieves all UserProfileFields from the database
    *
    * The returned array of Language objects
    * is sorted by PackageID, UserProfileField
    * and has IDs as keys.
    *
    * @return UserProfileField[]
    */
    public function getAllUserProfileFields($ExcludeDisabledPackageFields = true)
    {
    	$Conditions = $ExcludeDisabledPackageFields ? "PackageID IN (SELECT PackageID FROM tblCore_Packages WHERE Enabled = 1)" : NULL;

    	return $this->loadTable("tblCore_UserProfileFields", "UserProfileField", "PackageID, UserProfileField", $Conditions, array(), array());
    }




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
    * for a container type.
    *
    * @param string $String
    * @return integer
    */
    public function getLanguageIDFromString($String)
    {
        // Case insensitve, but exact match
        $this->mdb2->setLimit(1);
        $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_Languages WHERE LanguageLong LIKE ? OR LanguageShort LIKE ?', array('text', 'text'), MDB2_PREPARE_RESULT);
        $result = $prep->execute(array($String, $String));
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        $row = $result->fetchRow();
        $result->free();
        $prep->free();

        $row['ID'] = $row['ID'] ? $row['ID'] : 0;

        return $row['ID'];
    }





    /**
    * Returns the version of the latest of Archon
    *
    * @return string
    */
    public function getLatestArchonVersion()
    {
        return @file_get_contents($this->ArchonURL . 'sys/version.php?aprcode=core');
    }





    /**
     * Returns the version number for the latest update of the package
     *
     * @param string $APRCode
     */
    public function getLatestPackageVersionFromAPRCode($APRCode)
    {
        return @file_get_contents($this->ArchonURL . 'sys/version.php?aprcode=' . $APRCode);
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
        if (PEAR::isError($result)) {
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
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        $row = $result->fetchRow();
        $result->free();
        $prep->free();

        $row['ID'] = $row['ID'] ? $row['ID'] : 0;

        return $row['ID'];
    }





    /**
     * Retrives permissions for a specified user and module
     *
     * @param integer $UserID
     * @param integer $ModuleID
     * @return integer
     */
    public function getPermissionsForUser($UserID, $ModuleID)
    {
        if(!$UserID)
        {
            $this->declareError("Could not get Permissions: User ID not defined.");
            return false;
        }

        if(!$ModuleID)
        {
            $this->declareError("Could not get Permissions: Module ID not defined.");
            return false;
        }

        if(!is_natural($UserID))
        {
            $this->declareError("Could not get Permissions: User ID must be numeric.");
            return false;
        }

        if(!is_natural($ModuleID))
        {
            $this->declareError("Could not get Permissions: Module ID must be numeric.");
            return false;
        }

        $objUser = New User($UserID);

        if(!$objUser->dbLoad())
        {
            $this->declareError("Could not get Permissions: $this->Error");
            return false;
        }

        // Custom permissions for the user have the highest priority, then custom Usergroup permissions
        // if neither are set, use the Usergroup's default permissions.  Also, permissions can be 0, so
        // we must use the identical comparison operator.
        if(isset($objUser->Permissions[$ModuleID]))
        {
            $Permissions = $objUser->Permissions[$ModuleID];
        }
        else if($objUser->Usergroup)
        {
            if(isset($objUser->Usergroup->Permissions[$ModuleID]))
            {
                $Permissions = $objUser->Usergroup->Permissions[$ModuleID];
            }
            else
            {
                $Permissions = $objUser->Usergroup->DefaultPermissions;
            }
        }
        else
        {
            // No permissions are set at all, so deny any request.
            $Permissions = 0;
        }
        
        return $Permissions;
    }





    /**
     * Retrives permissions for a specified usergroup and module
     *
     * @param integer $UsergroupID
     * @param integer $ModuleID
     * @return integer
     */
    public function getPermissionsForUsergroup($UsergroupID, $ModuleID)
    {
        if(!$UsergroupID)
        {
            $this->declareError("Could not get Permissions: Usergroup ID not defined.");
            return false;
        }

        if(!$ModuleID)
        {
            $this->declareError("Could not get Permissions: Module ID not defined.");
            return false;
        }

        if(!is_natural($UsergroupID))
        {
            $this->declareError("Could not get Permissions: Usergroup ID must be numeric.");
            return false;
        }

        if(!is_natural($ModuleID))
        {
            $this->declareError("Could not get Permissions: Module ID must be numeric.");
            return false;
        }

        $objUsergroup = New Usergroup($UsergroupID);

        if(!$objUsergroup->dbLoad())
        {
            $this->declareError("Could not get Permissions: $this->Error");
            return false;
        }

        // Custom permissions for the usergroup have the highest priority, then custom Usergroupgroup permissions
        // if neither are set, use the Usergroupgroup's default permissions.  Also, permissions can be 0, so
        // we must use the identical comparison operator.
        if($objUsergroup->Permissions[$ModuleID] !== NULL)
        {
            $Permissions = $objUsergroup->Permissions[$ModuleID];
        }
        else
        {
            // No permissions are explicitly set, so return the default permissions.
            $Permissions = $objUsergroup->DefaultPermissions;
        }

        return $Permissions;
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
    	if(!$PhraseName)
        {
            $this->declareError("Could not get Phrase: Phrase Name not defined.");
            return false;
        }

        if(!$PackageID || !is_natural($PackageID))
        {
            $this->declareError("Could not get Phrase: Phrase Package not defined.");
            return false;
        }

        if(!is_natural($ModuleID))
        {
            $this->declareError("Could not get Phrase: Phrase Module must be numeric.");
            return false;
        }

        if(!$LanguageID)
        {
            $LanguageID = $this->Security->Session->getLanguageID();
        }

        if(!$PhraseTypeID || !is_natural($PhraseTypeID))
        {
            $this->declareError("Could not get Phrase: PhraseType not defined.");
            return false;
        }

        $tolerance = 1;

        // For safety with sizeof.
        $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID] = isset($this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID]) ? $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID] : array();
        $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE] = isset($this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE]) ? $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE] : array();

        static $prepAll = NULL;
        static $prepOne = NULL;
        if(!isset($prepAll))
        {
            $query = "SELECT * FROM tblCore_Phrases WHERE LanguageID = ? AND PhraseTypeID = ? AND PackageID = ? AND (ModuleID = ? OR ModuleID = '0') ORDER BY ModuleId";
            $prepAll = $this->mdb2->prepare($query, array('integer', 'integer', 'integer', 'integer', MDB2_PREPARE_RESULT));
        }
        if(!isset($prepOne))
        {
        	$query = "SELECT * FROM tblCore_Phrases WHERE LanguageID = ? AND PhraseName LIKE ? AND PhraseTypeID = ? AND PackageID = ? AND (ModuleID = ? OR ModuleID = '0') ORDER BY ModuleID DESC";
            $this->mdb2->setLimit(1);
            $prepOne = $this->mdb2->prepare($query, array('integer', 'text', 'integer', 'integer', 'integer', MDB2_PREPARE_RESULT));
        }

        // Try to set using language cache.
        $objPhrase = $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName];


        // Maybe cache is too fresh?
        if(!$objPhrase && sizeof($this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID]) <= $tolerance)
        {
            if(sizeof($this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID]) == $tolerance)
            {
                // Load all phrases with same package id, module id, phrase type id, and language id.
                $result = $prepAll->execute(array($LanguageID, $PhraseTypeID, $PackageID, $ModuleID));
                if (PEAR::isError($result)) {
                    trigger_error($result->getMessage(), E_USER_ERROR);
                }

                while($row = $result->fetchRow())
                {
                    $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$row['PhraseName']] = New Phrase($row);
                }
                $result->free();

                $objPhrase = $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName];
            }
            else
            {
            	$result = $prepOne->execute(array($LanguageID, $PhraseName, $PhraseTypeID, $PackageID, $ModuleID));
                if (PEAR::isError($result)) {
                    trigger_error($result->getMessage(), E_USER_ERROR);
                }

                if($row = $result->fetchRow())
                {
                    $objPhrase = New Phrase($row);
                }
                $result->free();

                $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName] = $objPhrase;
            }
        }

        // Try default language cache.
        if(!$objPhrase)
        {
            $objPhrase = $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE][$PhraseName];
        }

        // Maybe default language cache is too fresh?
        if(!$objPhrase && sizeof($this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE]) <= $tolerance)
        {
            if(sizeof($this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE]) == $tolerance)
            {
                // Load all phrases with same package id, module id, phrase type id, and language id.
                $result = $prepAll->execute(array(CONFIG_CORE_DEFAULT_LANGUAGE, $PhraseTypeID, $PackageID, $ModuleID));
                if (PEAR::isError($result)) {
                    trigger_error($result->getMessage(), E_USER_ERROR);
                }

                while($row = $result->fetchRow())
                {
                    $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE][$row['PhraseName']] = New Phrase($row);
                }
                $result->free();

                $objPhrase = $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE][$PhraseName];
            }
            else
            {
                $result = $prepOne->execute(array(CONFIG_CORE_DEFAULT_LANGUAGE, $PhraseName, $PhraseTypeID, $PackageID, $ModuleID));
                if (PEAR::isError($result)) {
                    trigger_error($result->getMessage(), E_USER_ERROR);
                }

                if($row = $result->fetchRow())
                {
                    $objPhrase = New Phrase($row);
                }
                $result->free();

                $this->MemoryCache->Phrases[$PackageID][$ModuleID][$PhraseTypeID][CONFIG_CORE_DEFAULT_LANGUAGE][$PhraseName] = $objPhrase;
            }
        }

        // Time to give up.
        if(!$objPhrase)
        {
            $DieNote = "Could not load phrase '$PhraseName' with Package ID $PackageID, Module ID $ModuleID, Language ID $LanguageID, and Phrase Type ID $PhraseTypeID! This occured with p={$_REQUEST['p']} and f={$_REQUEST['f']}";
            //die($DieNote);
            return false;
        }

        return $objPhrase;
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
        // Cache repeated phrase types.
        if($this->MemoryCache->PhrasesTypeIDs[$String])
        {
            return $this->MemoryCache->PhrasesTypeIDs[$String];
        }

        static $prep = NULL;
        if(!isset($prep))
        {
        	// Case insensitve, but exact match
            $this->mdb2->setLimit(1);
            $prep = $this->mdb2->prepare('SELECT ID FROM tblCore_PhraseTypes WHERE PhraseType LIKE ?', 'text', MDB2_PREPARE_RESULT);
        }

        $result = $prep->execute($String);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        $row = $result->fetchRow();
        $result->free();

        $row['ID'] = $row['ID'] ? $row['ID'] : 0;

        $this->MemoryCache->PhrasesTypeIDs[$String] = $row['ID'];

        return $row['ID'];
    }







    /**
    * Retrieves an array containing User objects for each ID in $arrIDs
    *
    * @param integer[] $arrIDs
    * @return User[]
    */
    public function getUserArrayFromIDArray($arrIDs)
    {
        if(empty($arrIDs))
        {
            $this->declareError("Could not get User Array: No User IDs specified.");
            return false;
        }

        if(!is_array($arrIDs))
        {
            $this->declareError("Could not get User Array: Argument is not an array.");
            return false;
        }

        $arrUsergroups = $this->getAllUsergroups();

        $Condition = '';
        $ConditionTypes = array();
        $ConditionVars = array();
        foreach($arrIDs as $ID)
        {
            if(is_natural($ID) && $ID >= 0)
            {
                $Condition .= "ID = ? OR ";
                $ConditionTypes[] = 'integer';
                $ConditionVars[] = $ID;
            }
        }

        // Chop off the trailing OR
        $Condition = encoding_substr($Condition, 0, encoding_strlen($Condition) - 3);

        $arrUsers = $this->loadTable("tblCore_Users", 'User', 'DisplayName', $Condition, $ConditionTypes, $ConditionVars);

        if(!empty($arrUsers))
        {
            foreach($arrUsers as &$objUser)
            {
                if($objUser->UsergroupID)
                {
                    $objUser->Usergroup = $arrUsergroups[$objUser->UsergroupID];
                }

                $objUser->dbLoadPermissions();
            }
        }

        reset($arrUsers);

        return $arrUsers;
    }





    /**
    * Retrieves an array containing Usergroup objects for each ID in $arrIDs
    *
    * @param integer[] $arrIDs
    * @return Usergroup[]
    */
    public function getUsergroupArrayFromIDArray($arrIDs)
    {
        if(empty($arrIDs))
        {
            $this->declareError("Could not get Usergroup Array: No Usergroup IDs specified.");
            return false;
        }

        if(!is_array($arrIDs))
        {
            $this->declareError("Could not get Usergroup Array: Argument is not an array.");
            return false;
        }

        foreach($arrIDs as $ID)
        {
            if(is_natural($ID) && $ID >= 0)
            {
                $Condition .= "ID = '$ID' OR ";
            }
        }

        // Chop off the trailing OR
        $Condition = encoding_substr($Condition, 0, encoding_strlen($Condition) - 3);

        $arrUsergroups = $this->loadTable("tblCore_Usergroups", 'Usergroup', 'Usergroup', $Condition);

        if(!empty($arrUsergroups))
        {
            foreach($arrUsergroups as &$objUsergroup)
            {
                $objUsergroup->dbLoadPermissions();
            }
        }

        reset($arrUsergroups);

        return $arrUsergroups;
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
        if (PEAR::isError($result)) {
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
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        $row = $result->fetchRow();
        $result->free();
        $prep->free();

        $row['ID'] = $row['ID'] ? $row['ID'] : 0;

        return $row['ID'];
    }




    /**
     * Returns an array of User objects that belong to the
     * specified Usergroup
     *
     * @param integer $ID
     * @return mixed
     */
    public function getUsersForUsergroup($ID)
    {
        if(!is_natural($ID))
        {
            $this->declareError("Could not get Users: Usergroup ID must be numeric.");
            return false;
        }

        $wherequery = "UsergroupID = '$ID'";

        return $this->loadTable('tblCore_Users', 'User', 'Login', $wherequery);
    }





    /**
     * Initializes Archon for use
     *
     */
    public function initialize()
    {
        global $_ARCHON;

        $this->RootDirectory = getcwd();

        $this->mdb2->loadModule('Manager');
        $arrTables = $this->mdb2->listTables();
	    if (PEAR::isError($arrTables)) {
	        trigger_error($arrTables->getMessage(), E_USER_ERROR);
	    }

        if(!in_array('tblCore_Configuration', $arrTables))
        {
        	if(!in_array('tblArchon_Configuration', $arrTables))
        	{
        		// Most likely tblCore_Configuration does not exist, assume older than 2.00
                header("Location: index.php?p=upgrade");
                die();
        	}
            else if(file_exists('packages/core/install/install.php'))
            {
                header("Location: index.php?p=install");
                die();
            }
            else
            {
                trigger_error("Could not load Archon: There was a problem querying the database", E_USER_ERROR);
            }
        }

        unset($arrTables);

        $query = "SELECT * FROM tblCore_Configuration ORDER BY Directive";
        $result = $this->mdb2->query($query);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        while($row = $result->fetchRow())
        {
        	if($row['Type'] == 'password' || $row['type'] == 'password')
            {
                $row['Value'] = '';
            }

            $this->Configuration[$row['ID']] = New Configuration($row);

            $constname = 'CONFIG_' . encoding_strtoupper($row['Directive']);
            $constname = str_replace(' ', '_', $constname);

            @define($constname, $row['Value'], false);
        }
        $result->free();

        $this->Packages = $this->getAllPackages();

        foreach($this->Configuration as $ID => $objConfiguration)
        {
            if(is_natural($ID))
            {
                $this->Configuration[$this->Packages[$objConfiguration->PackageID]->APRCode . ' ' . encoding_strtolower($objConfiguration->Directive)] =& $this->Configuration[$ID];

                $constname = 'CONFIG_' . encoding_strtoupper($this->Packages[$objConfiguration->PackageID]->APRCode) . '_' . encoding_strtoupper($objConfiguration->Directive);
                $constname = str_replace(' ', '_', $constname);

                @define($constname, $objConfiguration->Value, false);
            }
        }

        foreach($this->Packages as $ID => $objPackage)
        {
            $this->Packages[$objPackage->APRCode] =& $this->Packages[$ID];
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
                        //trigger_error("TODO: Run package $objPackage->APRCode upgrade script", E_USER_WARNING);
                        // At this point I want to load up Archon anyway, but with this and it's dependent packages
                        // disabled so the upgrade script can run smoothly.
                        if($objPackage->APRCode == 'core')
                        {
                            header("Location: index.php?p=upgrade");
                            die();
                        }

                        $this->Packages[$ID]->Enabled = false;
                    }
                    else if(version_compare($this->Packages[$ID]->Version, $this->Packages[$ID]->DBVersion) == -1)
                    {
                        trigger_error("Could not load Archon: The database version for Package {$this->Packages[$ID]->APRCode} ({$this->Packages[$ID]->DBVersion}) is newer than the Package codebase ({$this->Packages[$ID]->Version}).  Please re-install the Package.", E_USER_ERROR);
                    }
                    else if(!empty($this->Packages[$ID]->DependsUpon))
                    {
                        foreach($this->Packages[$ID]->DependsUpon as $APRCode => $DependsUponVersion)
                        {
                            if(!$this->Packages[$APRCode])
                            {
                                trigger_error("Could not load Archon: Package {$this->Packages[$ID]->APRCode} depends upon package $APRCode which is not installed.", E_USER_ERROR);
                            }
                            else if(version_compare($this->Packages[$APRCode]->DBVersion, $DependsUponVersion) == -1)
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

        $arrDependencies = array_merge($this->Packages['core']->DependedUponBy, $this->Packages['core']->EnhancedBy);

        while(!empty($arrDependencies))
        {
            reset($arrDependencies);
            $Dependency = key($arrDependencies);

            if($arrTopologicalSort[$Dependency])
            {
                array_shift($arrDependencies);
            }
            else if($arrSeen[$Dependency] || (empty($this->Packages[$Dependency]->DependedUponBy) && empty($this->Packages[$Dependency]->EnhancedBy)))
            {
                $arrTopologicalSort[$Dependency] = $Dependency;
                array_shift($arrDependencies);
            }
            else
            {
                foreach(array_keys(array_merge($this->Packages[$Dependency]->DependedUponBy, $this->Packages[$Dependency]->EnhancedBy)) as $ChildDependency)
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
            else if(file_exists("packages/$APRCode/lib/index.php"))
            {
                define("PACKAGE_" . encoding_strtoupper($APRCode), $this->Packages[$APRCode]->ID, false);

                $cwd = getcwd();
                chdir("packages/$APRCode/lib/");
                require_once("index.php");
                chdir($cwd);
            }
        }

        $this->Modules = $this->getAllModules();
        foreach($this->Modules as $ID => $objModule)
        {
            $this->Modules[$objModule->Script] =& $this->Modules[$ID];
            define("MODULE_" . encoding_strtoupper($objModule->Script), $objModule->ID, false);
        }

        define("MODULE_NONE", 0, false);

        $this->Security = New Security();

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

        $_REQUEST['p'] = preg_replace('/[^\w^\d^-^_\/]/u', '', encoding_strtolower($_REQUEST['p']));
        $defaultPubP = preg_replace('/[^\w^\d^-^_\/]/u', '', encoding_strtolower(CONFIG_CORE_DEFAULT_PUBLIC_SCRIPT));

        $arrP = explode('/', $_REQUEST['p']);

        $arrDefaultPubP = explode('/', $defaultPubP);
        if(!$this->Packages[$arrDefaultPubP[0]])
        {
            $arrDefaultPubP[0] = 'core';
            $arrDefaultPubP[1] = 'index';
        }
        else if(!file_exists("packages/{$arrDefaultPubP[0]}/pub/{$arrDefaultPubP[1]}.php"))
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
                $this->declareError("Package $Package is not installed.");

                $arrP[0] = $arrDefaultPubP[0];
                $arrP[1] = $arrDefaultPubP[1];
            }
            else if(!file_exists("packages/$Package/admin/$Script.php"))
            {
                $this->declareError("Script $Script does123 not exist in Package $Package.");

                $arrP[0] = $arrDefaultPubP[0];
                $arrP[1] = $arrDefaultPubP[1];
            }
            else
            {
                $this->AdministrativeInterface = New AdministrativeInterface();
            }
        }
        else if($_REQUEST['p'] == 'install' || $_REQUEST['p'] == 'upgrade')
        {
            $this->AdministrativeInterface = New AdministrativeInterface();
        }

        if(!$this->AdministrativeInterface)
        {
            $Package = $arrP[0] ? $arrP[0] : $arrDefaultPubP[0];
            $Script = $arrP[1] ? $arrP[1] : $arrDefaultPubP[1];

            if(!$this->Packages[$Package])
            {
                $this->declareError("Package $Package is not installed.");

                $Package = $arrDefaultPubP[0];
                $Script = $arrDefaultPubP[1];
            }
            else if(!file_exists("packages/$Package/pub/$Script.php"))
            {
                $this->declareError("Script $Script does77 not exist in Package $Package.");

                $Package = $arrDefaultPubP[0];
                $Script = $arrDefaultPubP[1];
            }

            $this->Package = $this->Packages[$Package];
            $this->Script = "packages/$Package/pub/$Script.php";

            $this->PublicInterface = New PublicInterface();
            $DefaultTemplateSet = CONFIG_CORE_DEFAULT_TEMPLATE_SET;
            $this->PublicInterface->initialize(($this->Security->Session->getRemoteVariable('Theme') ? $this->Security->Session->getRemoteVariable('Theme') : CONFIG_CORE_DEFAULT_THEME), ($_REQUEST['templateset'] ? $_REQUEST['templateset'] : $DefaultTemplateSet));

            if($_REQUEST['disabletheme'] || $_REQUEST['notheme'])
            {
                $this->PublicInterface->DisableTheme = true;
            }
        }
        else if($_REQUEST['p'] != 'install' && $_REQUEST['p'] != 'upgrade')
        {
            if(!$this->Security->userHasAdministrativeAccess())
            {
                $Package = 'core';
                $Script = 'login';
            }
            else if($this->Security->verifyPermissions($this->Modules[$Script]->ID, READ) || ($Package == 'core' && ($Script == 'index' || $Script == 'home')))
            {
                $this->Module = $this->Modules[$Script];
            }
            else if($Script != 'login')
            {
                die('Access Denied');
            }

            $this->Package = $this->Packages[$Package];
            $this->Script = "packages/$Package/admin/$Script.php";

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
            eval("\$result = $MixinClass::initialize();");
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
    public function loadObject($Object, $Table)
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

        if(isset($this->MemoryCache->Objects[$strClassName][$Object->ID]))
        {
            $arrVariables = get_object_vars($Object);
            foreach($arrVariables as $name => $defaultvalue)
            {
                if(isset($this->MemoryCache->Objects[$strClassName][$Object->ID]->$name))
                {
                    $Object->$name = $this->MemoryCache->Objects[$strClassName][$Object->ID]->$name;
                }
            }

            return true;
        }

        static $loadPreps = array();
        $loadPreps[$Table] = $loadPreps[$Table] ? $loadPreps[$Table] : $this->mdb2->prepare("SELECT * FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ID = ?", 'integer', MDB2_PREPARE_RESULT);
        $result = $loadPreps[$Table]->execute($Object->ID);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        $row = $result->fetchRow();
        $result->free();

        if(!$row['ID'])
        {
            $this->declareError("Could not load $strClassName: $strClassName ID $Object->ID not found in database.");
            //$this->MemoryCache->Objects[$strClassName][$Object->ID] = false;
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

        //$this->MemoryCache->Objects[$strClassName][$Object->ID] = $Object;

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
                                else if(!file_exists($file))
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
                                else if(!file_exists($file))
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
     * Adds entry in modification log
     *
     * @param string $TableName
     * @param integer $ID
     */
    function log($TableName, $ID)
    {
        if(!$this->mdb2)
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
        if (PEAR::isError($affected)) {
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
    public function redirect($Location = NULL)
    {
        if($Location)
        {
            if(encoding_strpos($Location, ";") === false)
            $Location = "location.href = '$Location';";
        }

        $Location = $_REQUEST['go'] ? "location.href = '" . encode($_REQUEST['go'], ENCODE_JAVASCRIPT) . "';" : $Location;

        if($this->AdministrativeInterface)
        {
?>
    <script type="text/javascript">
    <!--
    var target;

    if(top.frames['main'])
    {
        if(location != top.frames.main.location)
        {
            target = top.frames.main.location;
        }
    }
    else if(parent.window.opener)
    {
        target = parent.location;
    }
<?php
            if($Location)
            {
                echo($Location);
            }
            else
            {
?>
    if(target)
    {
        target.reload();
    }
    else
    {
        location.href = '?p=<?php echo($_REQUEST['p']); ?>';
    }
<?php
            }
?>

    -->
    </script>
<?php
        }
        else
        {
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
    }


 /**
    * Searches the Configuration database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Configuration[]
    */
    public function searchConfigurations($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
    {
        return $this->searchTable($SearchQuery, 'tblCore_Configuration', 'Directive', 'Configuration', 'Directive', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
    }



    /**
    * Searches the Language database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Language[]
    */
    public function searchLanguages($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
    {
        return $this->searchTable($SearchQuery, 'tblCore_Languages', 'LanguageLong', 'Language', 'DisplayOrder, LanguageLong', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
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

        return $this->searchTable($SearchQuery, 'tblCore_Phrases', 'PhraseName', 'Phrase', 'PackageID, ModuleID, PhraseTypeID, PhraseName', $ConditionsAND, $ConditionsANDTypes, $ConditionsANDVars, NULL, array(), array(), $Limit, $Offset);
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
    * Searches the UserProfileField database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return UserProfileField[]
    */
    public function searchUserProfileFields($SearchQuery, $ExcludeDisabledPackageFields = true, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
    {
    	$Conditions = $ExcludeDisabledPackageFields ? "PackageID IN (SELECT PackageID FROM tblCore_Packages WHERE Enabled = 1)" : NULL;
    	
        return $this->searchTable($SearchQuery, 'tblCore_UserProfileFields', 'UserProfileField', 'UserProfileField', 'PackageID, UserProfileField', $Conditions, array(), array(), NULL, array(), array(), $Limit, $Offset);
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
        alert(msg);
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
    public function sendMessageAndRedirect($Message, $Location = NULL)
    {
        $this->sendMessage($Message);
        $this->redirect($Location);
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

        $arrDefaultVars = $this->getMixinClassVars($strClassName);

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

        if(!$this->mixinMethodExists($Object, 'verifyStorePermissions'))
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
                $this->declareError("Could not store $strClassName: Permission Denied.");
                return false;
            }
        }
        
        if($this->mixinClassVarExists($Object, 'ParentID') && !is_natural($Object->ParentID))
        {
            $this->declareError("Could not store $strClassName: Parent ID must be numeric.");
            return false;
        }

        if($this->mixinClassVarExists($Object, 'ParentID') && $Object->ParentID)
        {
            static $parentExistsPreps = array();
            if(!isset($parentExistsPreps[$strClassName]))
            {
            $query = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE ID = ?";
            $parentExistsPreps[$strClassName] = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
            }
            $result = $parentExistsPreps[$strClassName]->execute($Object->ParentID);
            if (PEAR::isError($result)) {
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
        	}

        	$result = $checkPreps[$strClassName][$checkquery]->execute($CheckVars[$key]);
            if (PEAR::isError($result)) {
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
	            if (PEAR::isError($insertPreps[$strClassName][serialize($IgnoredFields)])) {
	            	echo($query);
	            	trigger_error($insertPreps[$strClassName][serialize($IgnoredFields)]->getMessage(), E_USER_ERROR);
	            }
            }
            
            $affected = $insertPreps[$strClassName][serialize($IgnoredFields)]->execute($arrVars);
            if (PEAR::isError($affected)) {
                trigger_error($affected->getMessage(), E_USER_ERROR);
            }

            $result = $checkPreps[$strClassName][reset($CheckQueries)]->execute(reset($CheckVars));
            if (PEAR::isError($result)) {
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
            if (PEAR::isError($result)) {
                trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $row = $result->fetchRow();
            $result->free();

            if(!$row['ID'])
            {
                $this->declareError("Could not update $strClassName: $strClassName ID $Object->ID does not exist in the database.");
                return false;
            }
            
            if($this->mixinClassVarExists($Object, 'ParentID') && $Object->ParentID == $Object->ID)
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
                if (PEAR::isError($updatePreps[$strClassName][serialize($IgnoredFields)])) {
                    trigger_error($updatePreps[$strClassName][serialize($IgnoredFields)]->getMessage(), E_USER_ERROR);
                }
            }

            $affected = $updatePreps[$strClassName][serialize($IgnoredFields)]->execute($arrVars);
            if (PEAR::isError($affected)) {
                trigger_error($affected->getMessage(), E_USER_ERROR);
            }

            $this->log("$Table", $Object->ID);
        }

        return true;
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
    * @return $ClassName[]
    */
    public function loadTable($Table, $ClassName, $OrderBy = NULL, $Condition = NULL, $ConditionTypes = NULL, $ConditionVars = NULL)
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
            $this->declareError("Could not load Table: Class $ClassName does not exist.");
            return false;
        }

        if(isset($Condition) && (!isset($ConditionTypes) || !isset($ConditionVars)))
        {
        	debug_print_backtrace();
        	trigger_error("loadTable not being used correctly!", E_USER_ERROR);
        }
        else if(!isset($Condition))
        {
        	$ConditionTypes = array();
        	$ConditionVars = array();
        }

        if(isset($this->MemoryCache->Tables[$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)]))
        {
            return $this->MemoryCache->Tables[$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)];
        }

        $arrObjects = array();

        //$Table = $this->db->escape_string($Table);
        //$OrderBy = $this->db->escape_string($OrderBy);

        if($Condition)
        {
            $ConditionQuery = "WHERE ($Condition)";
        }

        if($OrderBy)
        {
            $OrderByQuery = "ORDER BY {$this->mdb2->escape($OrderBy)}";
        }

        $query = "SELECT * FROM {$this->mdb2->quoteIdentifier($Table)} $ConditionQuery $OrderByQuery";
        $prep = $this->mdb2->prepare($query, $ConditionTypes, MDB2_PREPARE_RESULT);
        $result = $prep->execute($ConditionVars);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        while($row = $result->fetchRow())
        {
            $this->MemoryCache->Objects[$ClassName][$row['ID']] = New $ClassName($row);
            $arrObjects[$row['ID']] = $this->MemoryCache->Objects[$ClassName][$row['ID']];

            if($row['ParentID'])
            {
                $arrParents[$row['ID']] = $row['ParentID'];
            }
        }
        $result->free();
        $prep->free();

        if(!empty($arrParents))
        {
            foreach($arrParents as $ID => $ParentID)
            {
                if($arrObjects[$ParentID])
                {
                	$this->MemoryCache->Objects[$ClassName][$ID]->Parent = $this->MemoryCache->Objects[$ClassName][$ParentID];
                    $arrObjects[$ID]->Parent = $arrObjects[$ParentID];
                }
            }
        }

        reset($arrObjects);

        $this->MemoryCache->Tables[$Table][$OrderBy . " " . $Condition][serialize($ConditionVars)] = $arrObjects;

        return $arrObjects;
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
    * @return $ClassName[]
    */
    public function searchTable($SearchQuery, $Tables, $Fields, $ClassName, $OrderBy = NULL, $AdditionalConditionsAND = NULL, $AdditionalConditionsANDTypes = array(), $AdditionalConditionsANDVars = array(), $AdditionalConditionsOR = NULL, $AdditionalConditionsORTypes = array(), $AdditionalConditionsORVars = array(), $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
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
                sort($Fields);
            }
        }

        $arrObjects = array();

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
            if(!empty($Fields))
            {
                foreach($Fields as $key => $Field)
                {
                    $wherequery .= "{$this->mdb2->quoteIdentifier($Field)} LIKE '%%'";

                    if($key + 1 < count($Fields))
                    {
                        $wherequery .= ' OR ';
                    }
                }
            }
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
        else if(is_natural($Offset) && $Offset > 0)
        {
            $limitparams = array(4294967295, $Offset);
        }
        else if(is_natural($Limit) && $Limit > 0)
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
        else if($AdditionalConditionsOR)
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

        $query = "SELECT " . $this->mdb2->quoteIdentifier(reset($Tables)) . ".* FROM $tablestring $wherequery $orderquery";
        call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
        $prep = $this->mdb2->prepare($query, $wheretypes, MDB2_PREPARE_RESULT);
        $result = $prep->execute($wherevars);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        while($row = $result->fetchRow())
        {
        	$this->MemoryCache->Objects[$ClassName][$row['ID']] = New $ClassName($row);
            $arrObjects[$row['ID']] = $this->MemoryCache->Objects[$ClassName][$row['ID']];
        }
        $result->free();
        $prep->free();

        reset($arrObjects);

        return $arrObjects;
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
     * Contains the most recent year of Archon development.
     *
     * @var int
     */
    public $CopyrightYear = 2009;

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
     * @var object
     */
    public $MemoryCache = NULL;

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
    public $ReleaseDate = '2008.07.17';

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
     * Contains the version of the Archon codebase.
     *
     * @var string
     */
    public $Version = NULL;

    public $QueryLog = NULL;
}

$_ARCHON->mixClasses('Archon', 'Core_Archon');
?>