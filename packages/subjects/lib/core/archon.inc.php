<?php

abstract class Subjects_Archon
{

   /**
    * Returns the number of Subjects in the database
    *
    * If $Alphabetical is set to true, an array will be returned with keys of
    * a-z, #, and * each holding the count for Subject Subjects starting
    * with that character.  # represents all collections starting with a number,
    * and * holds the total count of all collections.
    *
    * @param boolean $Alphabetical[optional]
    * @param integer $SubjectTypeID[optional]
    * @return integer|Array
    */
   public function countSubjects($Alphabetical = false, $SubjectTypeID = 0)
   {
      if($Alphabetical)
      {
         $arrIndex = array();
         $sum = 0;

         if($SubjectTypeID && is_natural($SubjectTypeID))
         {
            $subjecttypeidquery = " AND SubjectTypeID = ?";
            $subjecttypeidtypes = array('integer');
            $subjecttypeidvars = array($SubjectTypeID);
         }
         else
         {
            $subjecttypeidquery = '';
            $subjecttypeidtypes = array();
            $subjecttypeidvars = array();
         }

         $query = "SELECT ID FROM tblSubjects_Subjects WHERE (Subject LIKE '0%' OR Subject LIKE '1%' OR Subject LIKE '2%' OR Subject LIKE '3%' OR Subject LIKE '4%' OR Subject LIKE '5%' OR Subject LIKE '6%' OR Subject LIKE '7%' OR Subject LIKE '8%' OR Subject LIKE '9%')$subjecttypeidquery";
         $prep = $this->mdb2->prepare($query, $subjecttypeidtypes, MDB2_PREPARE_RESULT);
         $result = $prep->execute($subjecttypeidtypes);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $arrIndex['#'] = $result->numRows();
         $result->free();
         $prep->free();
         $sum += $arrIndex['#'];

         $prep = $this->mdb2->prepare("SELECT ID FROM tblSubjects_Subjects WHERE Subject LIKE ?$subjecttypeidquery", array_merge(array('text'), $subjecttypeidtypes), MDB2_PREPARE_RESULT);
         for($i = 65; $i < 91; $i++)
         {
            $char = chr($i);

            $result = $prep->execute(array_merge(array("$char%"), $subjecttypeidvars));
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $arrIndex[$char] = $result->numRows();
            $result->free();
            $arrIndex[encoding_strtolower($char)] = & $arrIndex[$char];
            $sum += $arrIndex[$char];
         }
         $prep->free();

         $arrIndex['*'] = $sum;

         return $arrIndex;
      }
      else
      {
         if($SubjectTypeID && is_natural($SubjectTypeID))
         {
            $subjecttypeidquery = " WHERE SubjectTypeID = ?";
            $subjecttypeidtypes = array('integer');
            $subjecttypeidvars = array($SubjectTypeID);
         }
         else
         {
            $subjecttypeidquery = '';
            $subjecttypeidtypes = array();
            $subjecttypeidvars = array();
         }

         $query = "SELECT ID FROM tblSubjects_Subjects$subjecttypeidquery";
         $prep = $this->mdb2->prepare($query, $subjecttypeidtypes, MDB2_PREPARE_RESULT);
         $result = $prep->execute($subjecttypeidvars);

         $numRows = $result->numRows();
         $result->free();
         $prep->free();

         return $numRows;
      }
   }

   /**
    * Creates an formatted string from an array of Subject objects
    *
    * @param Subject[] $arrSubjects
    * @param string $Delimiter[optional]
    * @param integer $MakeIntoLink[optional]
    * @param string $SubDelimiter[optional]
    * @return string
    */
   public function createStringFromSubjectArray($arrSubjects, $Delimiter = ', ', $MakeIntoLink = LINK_NONE, $SubDelimiter = ' - ')
   {
      if(empty($arrSubjects))
      {
         $this->declareError("Could not create Subject String: No IDs specified.");
         return false;
      }

      $objLast = end($arrSubjects);

      foreach($arrSubjects as $objSubject)
      {
         $string .= $objSubject->toString($MakeIntoLink, true, $SubDelimiter);

         if($objSubject->ID != $objLast->ID)
         {
            $string .= $Delimiter;
         }
      }

      return $string;
   }

   /**
    * Retrieves all Subjects from the database
    *
    * If $MakeIntoIndex is false, the returned array of Subject objects
    * is sorted by Subject and has IDs as keys.
    *
    * If $MakeIntoIndex is true, the returned array is a
    * two dimensional array, with the first dimension indexed with
    * 0 (representing numeric characters) and the lowercase characters a-z.
    * Each of those arrays will contain a sorted set of Subject objects, with
    * the Subject's IDs as keys.
    *
    * @param boolean $MakeIntoIndex[optional]
    * @return Subject[]
    */
   public function getAllSubjects($MakeIntoIndex = false)
   {
      $arrSubjects = $this->loadTable("tblSubjects_Subjects", "Subject", "Subject");

	  foreach($arrSubjects as &$objSubject)
	  
	  {
	  $objSubject->dbLoadSubjectSource();
	  }

      if($MakeIntoIndex)
      {
         foreach($arrSubjects as $objSubject)
         {
            $arrSorter[$objSubject->toString(LINK_NONE, true)] = $objSubject;
         }

         natcaseksort($arrSorter);

         $arrIndex = array();

         if(!empty($arrSorter))
         {
            foreach($arrSorter as $strSubject => &$objSubject)
            {
               if(is_natural($strSubject{0}))
               {
                  $arrIndex['#'][$objSubject->ID] = $objSubject;
               }

               $arrIndex[encoding_strtolower($strSubject{0})][$objSubject->ID] = $objSubject;
            }

            ksort($arrIndex);
         }

         return $arrIndex;
      }
      else
      {
         return $arrSubjects;
      }
   }

 /**
    * Retrieves all Subjects from the database in ID order and w/out subject sources, 
    * for ASpace migration
    *
    * If $MakeIntoIndex is false, the returned array of Subject objects
    * is sorted by Subject and has IDs as keys.
    *
    * If $MakeIntoIndex is true, the returned array is a
    * two dimensional array, with the first dimension indexed with
    * 0 (representing numeric characters) and the lowercase characters a-z.
    * Each of those arrays will contain a sorted set of Subject objects, with
    * the Subject's IDs as keys.
    *
    * @param boolean $MakeIntoIndex[optional]
    * @return Subject[]
    */
   public function getAllSubjectsforJSON($MakeIntoIndex = false)
   {
      $arrSubjects = $this->loadTable("tblSubjects_Subjects", "Subject", "ID");

      if($MakeIntoIndex)
      {
         foreach($arrSubjects as $objSubject)
         {
            $arrSorter[$objSubject->toString(LINK_NONE, true)] = $objSubject;
         }

         natcaseksort($arrSorter);

         $arrIndex = array();

         if(!empty($arrSorter))
         {
            foreach($arrSorter as $strSubject => &$objSubject)
            {
               if(is_natural($strSubject{0}))
               {
                  $arrIndex['#'][$objSubject->ID] = $objSubject;
               }

               $arrIndex[encoding_strtolower($strSubject{0})][$objSubject->ID] = $objSubject;
            }

            ksort($arrIndex);
         }

         return $arrIndex;
      }
      else
      {
         return $arrSubjects;
      }
   }



   /**
    * Retrieves all Subjects for a subject type from the database
    *
    * The returned array of Subject objects
    * is sorted by Subject and has IDs as keys.
    *
    * @return Subject[]
    */
   public function getAllSubjectsForSubjectTypeID($SubjectTypeID)
   {
      if(!is_natural($SubjectTypeID))
      {
         return false;
      }

      return $this->loadTable("tblSubjects_Subjects", "Subject", "Subject", "SubjectTypeID = ?", array('integer'), array($SubjectTypeID));
   }

   /**
    * Retrieves all Subject Types from the database
    *
    * The returned array of SubjectType objects
    * is sorted by SubjectType and has IDs as keys.
    *
    * @return SubjectType[]
    */
   public function getAllSubjectTypes()
   {
      return SubjectType::getAllSubjectTypes();
   }

   public function getSubjectTypeJSONList()
   {
      $arrSubjectTypes = SubjectType::getAllSubjectTypes();

      $arrSubjectTypeList = array();

      foreach($arrSubjectTypes as $ID => $obj)
      {
         $arrSubjectTypeList[] = '{"id":"' . $ID . '","text":' . json_encode(caplength(call_user_func_array(array($obj, 'toString'), array()), CONFIG_CORE_RELATED_OPTION_MAX_LENGTH)) . '}';
      }

      return "[" . implode(",", $arrSubjectTypeList) . "]";
   }

   /**
    * Retrieves all Subject Sources from the database
    *
    * The returned array of SubjectSource objects
    * is sorted by SubjectSource and has IDs as keys.
    *
    * @return SubjectSource[]
    */
   public function getAllSubjectSources()
   {
      return $this->loadTable("tblSubjects_SubjectSources", "SubjectSource", "SubjectSource");
   }

   /**
    * Retrieves an array of child Subjects strings for Subject specified by $ID
    * This is a wrapper function for getChildSubjects
    *
    * @param integer $ID
    * @param integer $SubjectTypeID
    * @param boolean $ReturnList[optional]
    * @return String[]
    */
   public function getChildSubjectsList($ID, $SubjectTypeID = 0)
   {
      return $this->getChildSubjects($ID, $SubjectTypeID, false, true);
   }

   /**
    * Retrieves child Subjects for Subject specified by $ID
    *
    * @param integer $ID
    * @param integer $SubjectTypeID
    * @param boolean $ReturnList[optional]
    * @return Subject[]
    */
   public function getChildSubjects($ID, $SubjectTypeID = 0, $ReturnList = false)
   {
      $ID = $ID ? $ID : 0;

      $subjecttypeidtypes = array();
      $subjecttypeidvars = array();
      if($SubjectTypeID && is_natural($SubjectTypeID))
      {
         $subjecttypeidquery = " AND SubjectTypeID = ?";
         $subjecttypeidtypes[] = 'integer';
         $subjecttypeidvars[] = $SubjectTypeID;
      }

      if(!is_natural($ID))
      {
         $this->declareError("Could not get Child Subjects: Subject ID must be numeric.");
         return false;
      }


      $ConditionTypes = array_merge(array('integer'), $subjecttypeidtypes);
      $ConditionVars = array_merge(array($ID), $subjecttypeidvars);


      if($ReturnList)
      {
         return $this->loadObjectList("tblSubjects_Subjects", "Subject", "Subject", "Subject", "ParentID = ?$subjecttypeidquery", $ConditionTypes, $ConditionVars);
      }

      return $this->loadTable("tblSubjects_Subjects", "Subject", "Subject", "ParentID = ?$subjecttypeidquery", $ConditionTypes, $ConditionVars, false);
   }

   /**
    * Retrieves a single array of all child Subjects for Subject specified by $ID
    *  This function is used by searchSubjects to show children subjects from a search result
    *
    * @param integer $ID
    * @param integer $SubjectTypeID
    * @param boolean $ReturnList[optional]
    * @return Subject[]
    */
   public function getAllChildSubjects($ID, $SubjectTypeID = 0, $ReturnList = false)
   {
      $arrSubjects = $this->getChildSubjects($ID, $SubjectTypeID, $ReturnList);

      $arrArrays = array();
      foreach($arrSubjects as $objSubject)
      {
         $arrArrays[] = $this->getAllChildSubjects($objSubject->ID, $SubjectTypeID, $ReturnList);
      }

      foreach($arrArrays as $array)
      {
         $arrSubjects = array_merge($arrSubjects, $array);
      }

      return $arrSubjects;
   }

   /**
    * Retrieves an array containing Subject objects for each ID in $arrIDs
    *
    * @param integer[] $arrIDs
    * @return Subject[]
    */
   public function getSubjectArrayFromIDArray($arrIDs)
   {
      if(empty($arrIDs))
      {
         $this->declareError("Could not get Subject Array: No Subject IDs specified.");
         return false;
      }

      if(!is_array($arrIDs))
      {
         $this->declareError("Could not get Subject Array: Argument is not an array.");
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

      $arrSubjects = $this->loadTable("tblSubjects_Subjects", 'Subject', 'Subject', $Condition);

      if(!empty($arrSubjects))
      {
         foreach($arrSubjects as &$objSubject)
         {
            if($objSubject->ParentID)
            {
               $objSubject->Parent = New Subject($objSubject->ParentID);
               $objSubject->dbLoad();
            }
         }
      }

      reset($arrSubjects);

      return $arrSubjects;
   }

   /**
    * Returns SubjectID value
    * when passed the string value
    * for a container type.
    *
    * @param string $String
    * @param integer $ParentID
    * @return integer
    */
   public function getSubjectIDFromString($String, $ParentID = 0)
   {
      if(!is_natural($ParentID))
      {
         $this->declareError("Could not get Subject: Parent ID must be numeric.");
         return false;
      }

      if($ParentID)
      {
         $parent_query = " AND ParentID = ?";
         $parent_types = array('integer');
         $parent_vars = array($ParentID);
      }
      else
      {
         $parent_query = '';
         $parent_types = array();
         $parent_vars = array();
      }

      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare("SELECT ID FROM tblSubjects_Subjects WHERE Subject LIKE ?$parent_query ORDER BY ParentID", array_merge(array('text'), $parent_types), MDB2_PREPARE_RESULT);
      $result = $prep->execute(array_merge(array($String), $parent_vars));
      if(PEAR::isError($result))
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
    * Retrieves an array of Subject objects that begin with
    * the character specified by $Char
    *
    * @param string $Char
    * @param integer $SubjectTypeID
    * @return Subject[]
    */
   public function getSubjectsForChar($Char, $SubjectTypeID = 0)
   {
      if(!$Char)
      {
         $this->declareError("Could not get Subjects: Character not defined.");
         return false;
      }

      $arrSubjects = array();

      if($SubjectTypeID && is_natural($SubjectTypeID))
      {
         $subjecttypeidquery = " AND SubjectTypeID = ?";
         $subjecttypeidtypes = array('integer');
         $subjecttypeidvars = array($SubjectTypeID);
      }
      else
      {
         $subjecttypeidquery = '';
         $subjecttypeidtypes = array();
         $subjecttypeidvars = array();
      }

      if($Char == '#')
      {
         $query = "SELECT * FROM tblSubjects_Subjects WHERE (Subject LIKE '0%' OR Subject LIKE '1%' OR Subject LIKE '2%' OR Subject LIKE '3%' OR Subject LIKE '4%' OR Subject LIKE '5%' OR Subject LIKE '6%' OR Subject LIKE '7%' OR Subject LIKE '8%' OR Subject LIKE '9%') AND (ParentID = '0')$subjecttypeidquery ORDER BY Subject";
      }
      else
      {
         $query = "SELECT * FROM tblSubjects_Subjects WHERE Subject LIKE '{$this->mdb2->escape($Char, true)}%' AND (ParentID = '0')$subjecttypeidquery ORDER BY Subject";
      }

      $prep = $this->mdb2->prepare($query, $subjecttypeidtypes, MDB2_PREPARE_RESULT);
      $result = $prep->execute($subjecttypeidvars);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrSubjects[$row['ID']] = New Subject($row);
      }
      $result->free();
      $prep->free();

      return $arrSubjects;
   }

   /**
    * Returns SubjectSourceID value
    * when passed the string value
    * for a subject source.
    *
    * @param string $String
    * @return integer
    */
   public function getSubjectSourceIDFromString($String)
   {
      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare("SELECT ID FROM tblSubjects_SubjectSources WHERE SubjectSource LIKE ?", 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($String);
      if(PEAR::isError($result))
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
    * Returns SubjectTypeID value
    * when passed the string value
    * for a container type.
    *
    * @param string $String
    * @return integer
    */
   public function getSubjectTypeIDFromString($String)
   {
      return SubjectType::getSubjectTypeIDFromString($String);
   }

   /**
    * Searches the Subject database
    *
    * @param string $SearchQuery
    * @param integer $ParentID[optional]
    * @param integer $SubjectTypeID[optional]
    * @param boolean $ShowChildren[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Creator[]
    */
   public function searchSubjects($SearchQuery, $ParentID = NULL, $SubjectTypeID = 0, $ShowChildren = false, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $ConditionsANDTypes = array();
      $ConditionsANDVars = array();

      if($SubjectTypeID && is_natural($SubjectTypeID))
      {
         $ConditionsAND = "SubjectTypeID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $SubjectTypeID;
      }

      // look for "--" to indicate faceted subject as whole
      // this will override the normal search
      if(substr_count($SearchQuery, "--"))
      {
         $query = "SELECT * FROM tblSubjects_Subjects WHERE Subject LIKE ?";
         $facetedPrep = $this->mdb2->prepare($query, array('text'), MDB2_PREPARE_RESULT);
         $facetedParentPrep = $this->mdb2->prepare($query . " AND ParentID = ?", array('text', 'integer'), MDB2_PREPARE_RESULT);

         $facets = explode("--", $SearchQuery);
         $parentID = NULL;
         foreach($facets as $facet)
         {
            if($parentID)
            {
               $result = $facetedParentPrep->execute(array(trim($facet), $parentID));
            }
            else
            {
               $result = $facetedPrep->execute(trim($facet));
            }

            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $results = array();

            if($result->numRows())
            {
               while($row = $result->fetchRow())
               {
                  $results[$row['ID']] = New Subject($row);
               }
               $res = current($results);
               $parentID = $res->ID;
            }
            else
            {
               return array();
            }
         }

         return $results;
      }


      if(isset($ParentID) && is_natural($ParentID))
      {
         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND .= "ParentID = ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $ParentID;

         $arrSubjects = $this->searchTable($SearchQuery, 'tblSubjects_Subjects', 'Subject', 'Subject', 'Subject', $ConditionsAND, $ConditionsANDTypes, $ConditionsANDVars, NULL, array(), array(), $Limit, $Offset);
      }
      else
      {
         $arrSubjects = array();
         $texttypes = array();
         $textvars = array();

         $arrWords = $this->createSearchWordArray($SearchQuery);

         if(!empty($arrWords))
         {
            foreach($arrWords as $word)
            {
               $i++;
               if($word{0} == "-")
               {
                  $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
                  $textquery .= "(Subject NOT LIKE ?)";
                  $texttypes[] = 'text';
                  $textvars[] = "%$word%";
               }
               else
               {
                  $textquery .= "(Subject LIKE ?)";
                  $texttypes[] = 'text';
                  $textvars[] = "%$word%";
               }

               if($i < count($arrWords))
               {
                  $textquery .= " AND ";
               }
            }
         }
         else
         {
            //$textquery = "Subject LIKE '%%'";
            $textquery = "1=1";
         }

         $subtypes = array();
         $subvars = array();

         // If our query is just a number, try to match it
         // directly to an ID from the Subjects table.
         if(is_natural($SearchQuery))
         {
            $subquery = " OR ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $SearchQuery;
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

         if($textquery || $subquery)
         {
            $wherequery = "$textquery $subquery";
            $wheretypes = array_merge($texttypes, $subtypes);
            $wherevars = array_merge($textvars, $subvars);
         }
         else
         {
            $wherequery = '(1 = 1)';
            $wheretypes = array();
            $wherevars = array();
         }

         $wherequery = $ConditionsAND ? "WHERE ($wherequery) AND $ConditionsAND" : "WHERE $wherequery";
         $wheretypes = $ConditionsAND ? array_merge($wheretypes, $ConditionsANDTypes) : $wheretypes;
         $wherevars = $ConditionsAND ? array_merge($wherevars, $ConditionsANDVars) : $wherevars;


         // Run query to list subjects
         $query = "SELECT * FROM tblSubjects_Subjects $wherequery";
         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($query, $wheretypes, MDB2_PREPARE_RESULT);
         $result = $prep->execute($wherevars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $DisableTheme = $this->PublicInterface->DisableTheme;
         $this->PublicInterface->DisableTheme = true;

         while($row = $result->fetchRow())
         {
            // We can't add the subjects to the final array just yet
            // because the subjects need to be sorted based upon how
            // they will end up displaying (parent subjects will
            // be concatenated before child subjects).
            $objSubject = New Subject($row);
            $arrSorter[$objSubject->toString(LINK_NONE, true)] = $objSubject;

            if($ShowChildren)
            {
               $arrChildren = $this->getAllChildSubjects($objSubject->ID, $SubjectTypeID);
               foreach($arrChildren as $objChildSubject)
               {
                  $arrSorter[$objChildSubject->toString(LINK_NONE, true)] = $objChildSubject;
               }
            }
         }
         $result->free();
         $prep->free();

         natcaseksort($arrSorter);

         if(!empty($arrSorter))
         {
            foreach($arrSorter as $objSubject)
            {
               $arrSubjects[$objSubject->ID] = $objSubject;
            }
         }

         reset($arrSubjects);

         $this->PublicInterface->DisableTheme = $DisableTheme;
      }



      return $arrSubjects;
   }

   /**
    * Searches the SubjectSource database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return SubjectSource[]
    */
   public function searchSubjectSources($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblSubjects_SubjectSources', 'SubjectSource', 'SubjectSource', 'SubjectSource', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the SubjectType database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return SubjectType[]
    */
   public function searchSubjectTypes($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return SubjectType::searchSubjectTypes($SearchQuery, $Limit, $Offset);
   }

   /**
    * Returns an array containing Subject objects sorted from root to node
    *
    * @param integer $ID
    * @return Subject[]
    */
   public function traverseSubject($SubjectID)
   {
      if(!$SubjectID)
      {
         $this->declareError("Could not traverse Subject: Subject ID not defined.");
         return false;
      }

      $objSubject = New Subject($SubjectID);
      $objSubject->dbLoad();

      $arrSubjects[$objSubject->ID] = $objSubject;

      while($objSubject->Parent)
      {
         $objSubject = $objSubject->Parent;
         $arrSubjects[$objSubject->ID] = $objSubject;
      }

      return array_reverse($arrSubjects);
   }

}

$_ARCHON->mixClasses('Archon', 'Subjects_Archon');
?>