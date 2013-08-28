<?php
abstract class Creators_Archon
{
   /**
    * Returns the number of Creators in the database
    *
    * If $Alphabetical is set to true, an array will be returned with keys of
    * a-z, #, and * each holding the count for Creator Names starting
    * with that character.  # represents all collections starting with a number,
    * and * holds the total count of all collections.
    *
    * @param boolean $Alphabetical[optional]
    * @return integer|Array
    */
   public function countCreators($Alphabetical = false)
   {
      if($Alphabetical)
      {
         $arrIndex = array();
         $sum = 0;

         $query = "SELECT ID FROM tblCreators_Creators WHERE (Name LIKE '0%' OR Name LIKE '1%' OR Name LIKE '2%' OR Name LIKE '3%' OR Name LIKE '4%' OR Name LIKE '5%' OR Name LIKE '6%' OR Name LIKE '7%' OR Name LIKE '8%' OR Name LIKE '9%')";
         $result = $this->mdb2->query($query);
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $arrIndex['#'] = $result->numRows();
         $result->free();
         $sum += $arrIndex['#'];

         $prep = $this->mdb2->prepare('SELECT ID FROM tblCreators_Creators WHERE Name LIKE ?', 'text', MDB2_PREPARE_RESULT);
         for($i = 65; $i < 91; $i++)
         {
            $char = chr($i);

            $result = $prep->execute("$char%");

            $arrIndex[$char] = $result->numRows();
            $result->free();
            $arrIndex[encoding_strtolower($char)] =& $arrIndex[$char];
            $sum += $arrIndex[$char];
         }
         $prep->free();

         $arrIndex['*'] = $sum;

         return $arrIndex;
      }
      else
      {
         $query = "SELECT ID FROM tblCreators_Creators";
         $result = $this->mdb2->query($query);
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $numRows = $result->numRows();
         $result->free();

         return $numRows;
      }
   }






   /**
    * Creates a formatted string from an array of Creator objects
    *
    * @param Creator[] $arrCreators
    * @param string $Delimiter[optional]
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateParentBody[optional]
    * @return string
    */
   public function createStringFromCreatorArray($arrCreators, $Delimiter = ', ', $MakeIntoLink = LINK_NONE)

   {
      if(empty($arrCreators))
      {
         $this->declareError("Could not create Creator String: No IDs specified.");
         return false;
      }

      $objLast = end($arrCreators);

      foreach($arrCreators as $objCreator)
      {
         $string .= $objCreator->toString($MakeIntoLink);

         if($objCreator->ID != $objLast->ID)
         {
            $string .= $Delimiter;
         }
      }

      return $string;
   }



   /**
    * Retrieves all Creators from the database
    *
    * If $MakeIntoIndex is false, the returned array of Creator objects
    * is sorted by Creator and has IDs as keys.
    *
    * If $MakeIntoIndex is true, the returned array is a
    * two dimensional array, with the first dimension indexed with
    * 0 (representing numeric characters) and the lowercase characters a-z.
    * Each of those arrays will contain a sorted set of Creator objects, with
    * the Creator's IDs as keys.
    *
    * @param $MakeIntoIndex[optional]
    * @return Creator[]
    */
   public function getAllCreators($ReturnList = false, $OnlyToStringFields = false)
   {
      if($ReturnList)
      {
         return $this->loadObjectList("tblCreators_Creators", "Creator", "Name");
      }
      elseif($OnlyToStringFields)
      {
         $tmpCreator = new Creator;
         $toStringFields = $tmpCreator->ToStringFields;
         //loadTable($Table, $ClassName, $OrderBy = NULL, $Condition = NULL, $ConditionTypes = NULL, $ConditionVars = NULL, $NoMemoryCache = false, $SelectFields = array())
         $arrCreators = $this->loadTable("tblCreators_Creators", "Creator", "Name", NULL, NULL, NULL, false, $toStringFields);
      }
      else
      {
         $arrCreators = $this->loadTable("tblCreators_Creators", "Creator", "Name");
      }

      return $arrCreators;

   }


  /**
    * Retrieves all Creators from the database for JSON endpoint, ordered by ID
    *
    * If $MakeIntoIndex is false, the returned array of Creator objects
    * is sorted by Creator and has IDs as keys.
    *
    * If $MakeIntoIndex is true, the returned array is a
    * two dimensional array, with the first dimension indexed with
    * 0 (representing numeric characters) and the lowercase characters a-z.
    * Each of those arrays will contain a sorted set of Creator objects, with
    * the Creator's IDs as keys.
    *
    * @param $MakeIntoIndex[optional]
    * @return Creator[]
*/
 public function getAllCreatorsJSON($ReturnList = false, $OnlyToStringFields = false)
   {
      if($ReturnList)
      {
         return $this->loadObjectList("tblCreators_Creators", "Creator", "Name");
      }
      elseif($OnlyToStringFields)
      {
         $tmpCreator = new Creator;
         $toStringFields = $tmpCreator->ToStringFields;
         //loadTable($Table, $ClassName, $OrderBy = NULL, $Condition = NULL, $ConditionTypes = NULL, $ConditionVars = NULL, $NoMemoryCache = false, $SelectFields = array())
         $arrCreators = $this->loadTable("tblCreators_Creators", "Creator", "ID", NULL, NULL, NULL, false, $toStringFields);
      }
      else
      {
         $arrCreators = $this->loadTable("tblCreators_Creators", "Creator", "ID");
      }

      return $arrCreators;

   }








   /**
    * Retrieves all Creator Types from the database
    *
    * The returned array of CreatorType objects
    * is sorted by CreatorType and has IDs as keys.
    *
    * @return CreatorType[]
    */
   public function getAllCreatorTypes()
   {
      return CreatorType::getAllCreatorTypes();
   }




   public function getAllCreatorRelationshipTypes()
   {
      return CreatorRelationshipType::getAllCreatorRelationshipTypes();
   }



   /**
    * Retrieves all Creator Sources from the database
    *
    * The returned array of CreatorSource objects
    * is sorted by CreatorSource and has IDs as keys.
    *
    * @return CreatorSource[]
    */
   public function getAllCreatorSources()
   {
      return $this->loadTable("tblCreators_CreatorSources", "CreatorSource", "CreatorSource");
   }




   /**
    * Returns CreatorSourceID value
    * when passed the string value
    * for a Creator source.
    *
    * @param string $String
    * @return integer
    */
   public function getCreatorSourceIDFromString($String)
   {
      static $arrCreatorSourceIDs = array();
      if(isset($arrCreatorSourceIDs[$String]))
      {
         return $arrCreatorSourceIDs[$String];
      }

      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare("SELECT ID FROM tblCreators_CreatorSources WHERE SourceAbbreviation LIKE ?", 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($String);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      $arrCreatorSourceIDs[$String] = $row['ID'];

      return $row['ID'];
   }




   /**
    * Returns CreatorID value
    * when passed the string value
    * for a container type.
    *
    * @param string $String
    * @return integer
    */
   public function getCreatorIDFromString($String)
   {
      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare("SELECT ID FROM tblCreators_Creators WHERE Name LIKE ?", 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($String);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;
      $result->free();
      $prep->free();

      return $row['ID'];
   }




   /**
    * Retrieves an array of Creator objects that begin with
    * the character specified by $Char
    *
    * @param string $Char
    * @return Creator[]
    */
   public function getCreatorsForChar($Char)
   {
      if(!$Char)
      {
         $this->declareError("Could not get Creators: Character not defined.");
         return false;
      }

      $arrCreators = array();

      if($Char == '#')
      {
         $query = "SELECT * FROM tblCreators_Creators WHERE (Name LIKE '0%' OR Name LIKE '1%' OR Name LIKE '2%' OR Name LIKE '3%' OR Name LIKE '4%' OR Name LIKE '5%' OR Name LIKE '6%' OR Name LIKE '7%' OR Name LIKE '8%' OR Name LIKE '9%') ORDER BY Name";
      }
      else
      {
         $query = "SELECT * FROM tblCreators_Creators WHERE Name LIKE '{$this->mdb2->escape($Char, true)}%' ORDER BY Name";
      }

      $result = $this->mdb2->query($query);
      while($row = $result->fetchRow())
      {
         $arrCreators[$row['ID']] = New Creator($row);
      }
      $result->free();

      return $arrCreators;
   }





   /**
    * Returns CreatorTypeID value
    * when passed the string value
    * for a container type.
    *
    * @param string $String
    * @return integer
    */
   public function getCreatorTypeIDFromString($String)
   {
      return CreatorType::getCreatorTypeIDFromString($String);
   }


   public function getCreatorsForRepository($RepositoryID)
   {
      return $this->searchTable('', 'tblCreators_Creators', array('Name'), 'Creator', 'Name', 'RepositoryID = ?', array('integer'), array($RepositoryID), NULL, array(), array(), 0, 0);
   }


   /**
    * Searches the Creator database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Creator[]
    */
   public function searchCreators($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $vars = $this->getClassVars('Creator');
      $toStringFields = $vars['ToStringFields'];

      return $this->searchTable($SearchQuery, 'tblCreators_Creators', array('Name', 'NameVariants'), 'Creator', 'Name', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset, $toStringFields);
   }





   /**
    * Searches the CreatorSource database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return SubjectSource[]
    */
   public function searchCreatorSources($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCreators_CreatorSources', 'CreatorSource', 'CreatorSource', 'CreatorSource', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }



   /**
    *
    * @staticvar <type> $currentPreps
    * @staticvar <type> $existPreps
    * @staticvar <type> $checkPreps
    * @staticvar <type> $insertPreps
    * @staticvar <type> $deletePreps
    * @staticvar <type> $updatePreps
    * @param <type> $Object
    * @param <type> $ModuleID
    * @param <type> $Table
    * @param <type> $arrRelatedCreatorIDs
    * @param <type> $arrPrimaryCreatorIDs
    * @return <type>
    */
   public function updateCreatorRelations($Object, $ModuleID, $Table, $arrRelatedCreatorIDs, $arrPrimaryCreatorIDs)
   {
      //This is a fix for only having one primary creator. The reason I'm not rewriting the function
      //is because this works, and it would be nice to retain the possible functionality in the future
      //given that our database structure would have always supported it.
      //Also, this function will clean up any existing instances where several creators are incorrectly selected as primary

      if(count($arrPrimaryCreatorIDs) > 1)
      {
         //set the array to only the first primary creator ID
         $arrPrimaryCreatorIDs = array(0 => reset($arrPrimaryCreatorIDs));
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

      if(!is_array($arrRelatedCreatorIDs) || !$arrRelatedCreatorIDs)
      {
         $this->declareError("Could not relate Creator: No Creator IDs are defined.");
         return false;
      }

      // if there are no primary creators passed, make the first creator passed the primary
      if(!is_array($arrPrimaryCreatorIDs) || !$arrPrimaryCreatorIDs || $arrPrimaryCreatorIDs == array(0))
      {
         $arrPrimaryCreatorIDs = array(0 => $arrRelatedCreatorIDs[0]);
      }


      $completeSuccess = true;


      $strClassID = $strClassName."ID";


      static $currentPreps = array();
      if(!isset($currentPreps[$strClassName]))
      {
         $query = "SELECT CreatorID, PrimaryCreator FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ?";
         $currentPreps[$strClassName] = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }

      $result = $currentPreps[$strClassName]->execute($Object->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $arrCurrentRelatedCreatorIDs = array();
      $arrCurrentPrimaryCreatorIDs = array();


      while($row = $result->fetchRow())
      {
         $arrCurrentRelatedCreatorIDs[]=$row['CreatorID'];
         if($row['PrimaryCreator'])
         {
            $arrCurrentPrimaryCreatorIDs[]=$row['CreatorID'];
         }
      }
      $result->free();

      $arrNewRelatedCreatorIDs = array_diff($arrRelatedCreatorIDs, $arrCurrentRelatedCreatorIDs);
      $arrNewUnrelatedCreatorIDs = array_diff($arrCurrentRelatedCreatorIDs, $arrRelatedCreatorIDs);

      $arrNewPrimaryCreatorIDs = array_diff($arrPrimaryCreatorIDs, $arrCurrentPrimaryCreatorIDs);
      $arrNewSecondaryCreatorIDs = array_diff($arrCurrentPrimaryCreatorIDs, $arrPrimaryCreatorIDs);

      if($arrNewRelatedCreatorIDs == array(0))
      {
         $arrNewRelatedCreatorIDs = array();
      }

      if($arrNewPrimaryCreatorIDs == array(0))
      {
         $arrNewPrimaryCreatorIDs = array();
      }


      static $existPrep = NULL;
      static $checkPreps = array();
      static $insertPreps = array();
      static $deletePreps = array();
      static $updatePreps = array();


      /* check if the arrays are full to avoid preparing statements that aren't used */

      if(!empty($arrNewRelatedCreatorIDs))
      {
         if (!isset($existPrep))
         {
            $query = "SELECT ID FROM tblCreators_Creators WHERE ID = ?";
            $existPrep = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }

         if(!isset($checkPreps[$strClassName]))
         {
            $query = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ? AND CreatorID = ?";
            $checkPreps[$strClassName] = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
         }

         if(!isset($insertPreps[$strClassName]))
         {
            $query = "INSERT INTO {$this->mdb2->quoteIdentifier($Table)} ({$this->mdb2->quoteIdentifier($strClassID)}, CreatorID, PrimaryCreator) VALUES (?, ?, ?)";
            $insertPreps[$strClassName] = $this->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
         }
      }

      if(!empty($arrNewUnrelatedCreatorIDs))
      {
         if(!isset($deletePreps[$strClassName]))
         {
            $query = "DELETE FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ? AND CreatorID = ?";
            $deletePreps[$strClassName] = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
         }
      }

      if(!isset($checkPreps[$strClassName]))
      {
         $query = "SELECT ID FROM {$this->mdb2->quoteIdentifier($Table)} WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ? AND CreatorID = ?";
         $checkPreps[$strClassName] = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }

      if(!isset($updatePreps[$strClassName]))
      {
         $query = "UPDATE {$this->mdb2->quoteIdentifier($Table)} SET PrimaryCreator = ? WHERE {$this->mdb2->quoteIdentifier($strClassID)} = ? AND CreatorID = ?";
         $updatePreps[$strClassName] = $this->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }



      foreach($arrNewRelatedCreatorIDs as $key => $newRelatedCreatorID)
      {

         $result = $existPrep->execute($newRelatedCreatorID);
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['ID'])
         {
            $this->declareError("Could not update Creator: Creator ID {$newRelatedCreatorID} does not exist in the database.");
            unset($arrNewRelatedCreatorIDs[$key]);
            $completeSuccess = false;
            continue;
         }


         $result = $checkPreps[$strClassName]->execute(array($Object->ID, $newRelatedCreatorID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            $this->declareError("Could not relate Creator: Creator ID {$newRelatedCreatorID} already related to {$strClassName} ID {$row['ID']}.");
            unset($arrNewRelatedCreatorIDs[$key]);
            $completeSuccess = false;
            continue;
         }


         $primaryCreator = 0;

         if(($pkey = array_search($newRelatedCreatorID, $arrNewPrimaryCreatorIDs)) !== false)
         {
            $primaryCreator = 1;
            unset($arrNewPrimaryCreatorIDs[$pkey]);
         }


         $affected = $insertPreps[$strClassName]->execute(array($Object->ID, $newRelatedCreatorID, $primaryCreator));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $result = $checkPreps[$strClassName]->execute(array($Object->ID, $newRelatedCreatorID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['ID'])
         {
            $this->declareError("Could not relate Creator: Unable to update the database table.");
            unset($arrNewRelatedCreatorIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $this->log($Table, $row['ID']);
         $this->log("tbl".pluralize($strClassName)."_".pluralize($strClassName), $Object->ID);
      }

      foreach($arrNewUnrelatedCreatorIDs as $key => $newUnrelatedCreatorID)
      {


         $result = $checkPreps[$strClassName]->execute(array($Object->ID, $newUnrelatedCreatorID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $RowID = $row['ID'];

         if(!$row['ID'])
         {
            $this->declareError("Could not unrelate Creator: Creator ID {$newUnrelatedCreatorID} not related to {$strClassName} ID {$Object->ID}.");
            unset($arrNewUnrelatedCreatorIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         if(($pkey = array_search($newUnrelatedCreatorID, $arrNewSecondaryCreatorIDs)) !== false)
         {
            unset($arrNewSecondaryCreatorIDs[$pkey]);
         }

         $affected = $deletePreps[$strClassName]->execute(array($Object->ID, $newUnrelatedCreatorID));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $result = $checkPreps[$strClassName]->execute(array($Object->ID, $newUnrelatedCreatorID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            $this->declareError("Could not unrelate Creator: Unable to update the database table.");
            unset($arrNewUnrelatedCreatorIDs[$key]);
            $completeSuccess = false;
            continue;
         }
         else
         {

            $this->log($Table, $RowID);
            $this->log("tbl".pluralize($strClassName)."_".pluralize($strClassName), $Object->ID);
         }

      }


      foreach($arrNewPrimaryCreatorIDs as $key => $newPrimaryCreatorID)
      {

         $result = $checkPreps[$strClassName]->execute(array($Object->ID, $newPrimaryCreatorID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $RowID = $row['ID'];

         if(!$row['ID'])
         {
            $this->declareError("Could not update Creator: Creator ID {$newPrimaryCreatorID} not related to {$strClassName} ID {$Object->ID}.");
            unset($arrNewPrimaryCreatorIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $affected = $updatePreps[$strClassName]->execute(array(1, $Object->ID, $newPrimaryCreatorID));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
         else
         {
            $this->log($Table, $RowID);

         }
      }

      foreach($arrNewSecondaryCreatorIDs as $key => $newSecondaryCreatorID)
      {

         $result = $checkPreps[$strClassName]->execute(array($Object->ID, $newSecondaryCreatorID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $RowID = $row['ID'];

         if(!$row['ID'])
         {
            $this->declareError("Could not update Creator: Creator ID {$newSecondaryCreatorID} not related to {$strClassName} ID {$Object->ID}.");
            unset($arrNewSecondaryCreatorIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $affected = $updatePreps[$strClassName]->execute(array(0, $Object->ID, $newSecondaryCreatorID));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
         else
         {
            $this->log($Table, $RowID);

         }
      }


      return $completeSuccess;

   }




}

$_ARCHON->mixClasses('Archon', 'Creators_Archon');
?>