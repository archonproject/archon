<?php

abstract class Collections_Collection
{

   /**
    * Collection Constructor
    *
    */
   public function construct()
   {
      global $_ARCHON;

      if($this->CollectionIdentifier)
      {
         if(is_natural($this->CollectionIdentifier))
         {
            $this->CollectionIdentifier = str_pad($this->CollectionIdentifier, CONFIG_COLLECTIONS_COLLECTION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }
      }
   }

   /**
    * Returns the number of content items in the collections
    *
    * @return integer
    */
   public function countContent()
   {
      global $_ARCHON;

      $prep = $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_Content WHERE CollectionID = ?', 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $numRows = $result->numRows();
      $result->free();

      return $numRows;
   }

   /**
    * Deletes Collection from the database
    *
    * @return boolean
    */
   public function dbDeleteCollection()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_COLLECTIONS, 'tblCollections_Collections'))
      {
         return false;
      }

      FindingAidCache::removeContent($this->ID);

      // Remove related information.

      static $userFieldsPrep = NULL;
      if(!isset($userFieldsPrep))
      {
         $query = "DELETE tblCollections_UserFields FROM tblCollections_Content, tblCollections_UserFields WHERE tblCollections_Content.ID = tblCollections_UserFields.ContentID AND tblCollections_Content.CollectionID = ?";
         $userFieldsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $userFieldsPrep->execute($ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $contentPrep = NULL;
      if(!isset($contentPrep))
      {
         $query = "DELETE FROM tblCollections_Content WHERE CollectionID = ?";
         $contentPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $contentPrep->execute($ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $creatorsPrep = NULL;
      if(!isset($creatorsPrep))
      {
         $query = "DELETE FROM tblCollections_CollectionCreatorIndex WHERE CollectionID = ?";
         $creatorsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $creatorsPrep->execute($ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $languagesPrep = NULL;
      if(!isset($languagesPrep))
      {
         $query = "DELETE FROM tblCollections_CollectionLanguageIndex WHERE CollectionID = ?";
         $languagesPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $languagesPrep->execute($ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $subjectsPrep = NULL;
      if(!isset($subjectsPrep))
      {
         $query = "DELETE FROM tblCollections_CollectionSubjectIndex WHERE CollectionID = ?";
         $subjectsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $subjectsPrep->execute($ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if(!$_ARCHON->deleteRelationship('tblCollections_CollectionLocationIndex', 'CollectionID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCollections_CollectionBookIndex', 'CollectionID', $ID, MANY_TO_MANY))
      {
         return false;
      }


      if(defined('PACKAGE_DIGITALLIBRARY'))
      {
         if(!$_ARCHON->deleteRelationship('tblDigitalLibrary_DigitalContent', 'CollectionID', $ID, ONE_TO_MANY))
         {
            return false;
         }
      }

      if(defined('PACKAGE_ACCESSIONS'))
      {
         if(!$_ARCHON->deleteRelationship('tblAccessions_AccessionCollectionIndex', 'CollectionID', $ID, MANY_TO_MANY))
         {
            return false;
         }
      }

      return true;
   }

   /**
    * Loads Collection and all related data and objects
    *
    * @param integer $RootContentID
    *
    * @return boolean
    */
   public function dbLoadAll($RootContentID = LOADCONTENT_ALL)
   {
      // If something is already wrong, abort.
      if($_ARCHON->Error)
      {
         $_ARCHON->declareError("Could not load Collection: There was already an error.");
         return false;
      }


      // Check for an error every step of the way, so we don't waste
      // time doing more work when something has already gone wrong.
      // Furthermore, order loading so that shorter tasks go first,
      // so longer tasks won't run if the shorter ones fail.
      if(!$this->dbLoad())
      {
         return false;
      }

      if(!$this->dbLoadRelatedObjects())
      {
         return false;
      }

      if(!$this->dbLoadContainerCount())
      {
         return false;
      }

      // Save the long one for last...
      if(!$this->dbLoadContent($RootContentID))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads Collection and root-level Content
    *
    * This function loads all data related to the collection except for
    * the full content tree.  It is much faster than dbLoadAll().
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_Collections'))
      {
         return false;
      }

      $this->AcquisitionDateMonth = encoding_substr($this->AcquisitionDate, 4, 2);
      $this->AcquisitionDateDay = encoding_substr($this->AcquisitionDate, 6, 2);
      $this->AcquisitionDateYear = encoding_substr($this->AcquisitionDate, 0, 4);

      $this->PublicationDateMonth = encoding_substr($this->PublicationDate, 4, 2);
      $this->PublicationDateDay = encoding_substr($this->PublicationDate, 6, 2);
      $this->PublicationDateYear = encoding_substr($this->PublicationDate, 0, 4);

      if($this->CollectionIdentifier)
      {
         if(is_natural($this->CollectionIdentifier))
         {
            $this->CollectionIdentifier = str_pad($this->CollectionIdentifier, CONFIG_COLLECTIONS_COLLECTION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }
      }

      return true;
   }

   /**
    * Loads Container Statistics for Collection instance
    *
    * @todo Revamp
    * @return unknown
    */
   public function dbLoadContainerCount()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load ContainerCount: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load ContainerCount: Collection ID must be numeric.");
         return false;
      }

      $query = "SELECT ID, LevelContainer, GlobalNumbering FROM tblCollections_LevelContainers";
      $result = $_ARCHON->mdb2->query($query);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
         $usedistinct = $row['GlobalNumbering'] ? "DISTINCT" : $usedistinct;

         $query = "SELECT $usedistinct LevelContainerIdentifier FROM tblCollections_Content WHERE CollectionID = ? AND LevelContainerID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
         $countresult = $prep->execute(array($this->ID, $row['ID']));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $count = $countresult->numRows();
         $countresult->free();
         if($count > 0)
         {
            $this->ContainerCount[$row['LevelContainer']] = $count;
            $this->ContainerCount[$row['ID']] = $count;

            // If there is more than one container, make the LevelContainer plural
            $this->ContainerCountString .= ( $count > 1) ? "$count " . pluralize($row['LevelContainer']) . ", " : "$count {$row['LevelContainer']}, ";
         }
      }
      $result->free();

      $this->ContainerCountString = encoding_substr($this->ContainerCountString, 0, encoding_strlen($this->ContainerCountString) - 2);

      return true;
   }

   public function getContentArray($RootContentID = LOADCONTENT_ALL, $ForEAD = false)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load CollectionContent: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load CollectionContent: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($RootContentID) && $RootContentID != LOADCONTENT_NONE)
      {
         $_ARCHON->declareError("Could not load CollectionContent: RootContentID must be numeric.");
         return false;
      }

      if($RootContentID == LOADCONTENT_NONE)
      {
         return true;
      }

      $this->Content = array();

      static $contentPrep = NULL;
      if(!isset($contentPrep))
      {
         $rootcontentidquery = " AND ((tblCollections_Content.RootContentID = ? OR ? = " . LOADCONTENT_ALL . ") OR tblCollections_Content.RootContentID = '0')";
         $rootcontentidtypes = array('integer', 'integer');

         $query = "SELECT tblCollections_Content.* FROM tblCollections_Content JOIN tblCollections_LevelContainers on tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID WHERE tblCollections_Content.CollectionID = ?$rootcontentidquery ORDER BY tblCollections_Content.ParentID, tblCollections_Content.SortOrder";
         $contentPrep = $_ARCHON->mdb2->prepare($query, array_merge(array('integer'), $rootcontentidtypes), MDB2_PREPARE_RESULT);
      }

      $rootcontentidvars = array($RootContentID, $RootContentID);

      $result = $contentPrep->execute(array_merge(array($this->ID), $rootcontentidvars));
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      // If there is no content found.
      if(!$result->numRows())
      {
         $result->free();
         return true;
      }

      $arrLevelContainers = $_ARCHON->getAllLevelContainers();

      $imagepath = $_ARCHON->PublicInterface->ImagePath;
      $cid = $this->ID;

      if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE))
      {
         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $link_type = 'admin';
      }
      elseif(!$_ARCHON->Security->userHasAdministrativeAccess()) // && $this->enabled())
      {
         $objRemovePhrase = Phrase::getPhrase('tostring_remove', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
         $strRemove = $objRemovePhrase ? $objRemovePhrase->getPhraseValue(ENCODE_HTML) : 'Remove from your cart.';
         $objAddToPhrase = Phrase::getPhrase('tostring_addto', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
         $strAddTo = $objAddToPhrase ? $objAddToPhrase->getPhraseValue(ENCODE_HTML) : 'Add to your cart.';

         $url = urlencode($_SERVER['REQUEST_URI']);

         $link_type = 'public';
      }
      else
      {
         $link_type = '';
      }

      while($row = $result->fetchRow())
      {
         $this->Content[$row['ID']] = array();
         $this->Content[$row['ID']]['ID'] = $row['ID'];
         $this->Content[$row['ID']]['Enabled'] = $row['Enabled'];
         $this->Content[$row['ID']]['ParentID'] = $row['ParentID'];

         if(!$ForEAD)
         {
            $encoding_substring = $arrLevelContainers[$row['LevelContainerID']]->getString('LevelContainer');

            if($row['LevelContainerIdentifier'])
            {
               $encoding_substring .= ' ' . $row['LevelContainerIdentifier'];
            }

            if(($row['Title'] || $row['Date']) && $encoding_substring)
            {
               $encoding_substring .= ': ';
            }

            if($row['Title'])
            {
               $title = $row['Title'];
               if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
               {
                  $title = encoding_convert_encoding($title, 'UTF-8', 'ISO-8859-1');
               }

               $title = trim($title);

               $encoding_substring .= $title;
            }

            if($row['Date'])
            {
               if($row['Title'])
               {
                  $encoding_substring .= ', ';
               }

               $encoding_substring .= trim($row['Date']);
            }

            if(!$row['Enabled'])
            {
               //@TODO: Make this a phrase
               $encoding_substring .= " [public output disabled]";
            }

            if(CONFIG_CORE_ESCAPE_XML)
            {
               $encoding_substring = encode($encoding_substring, ENCODE_BBCODE);
            }

            $id = $row['ID'];

            if($link_type == 'public' && ($this->Repository->ResearchFunctionality & RESEARCH_COLLECTIONS))
            {
               $link_string = "<a id='ccid$id' class='research_add' onclick='triggerResearchCartEvent(this, {collectionid:{$cid},collectioncontentid:{$id}}); return false;' href='#'><img class='cart' src='{$imagepath}/addtocart.gif' title='$strAddTo' alt='$strAddTo'/></a>";
            }
            elseif($link_type == 'admin')
            {
               $link_string = "<a href='?p=admin/collections/collectioncontent&amp;collectionid={$cid}&amp;parentid={$row['ParentID']}&amp;id={$row['ID']}' rel='external'><img class='edit' src='{$imagepath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
            }
            else
            {
               $link_string = '';
            }

            $string = $encoding_substring . $link_string;

            $this->Content[$row['ID']]['String'] = $string;

            $description = $row['Description'];
            if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
            {
               $description = encoding_convert_encoding($description, 'UTF-8', 'ISO-8859-1');
            }

            $description = trim($description);

            if(CONFIG_CORE_ESCAPE_XML)
            {
               $description = encode($description, ENCODE_BBCODE);
            }
            $this->Content[$row['ID']]['Description'] = $description;
         }
         else
         {
            if($row['Title'])
            {
               $title = $row['Title'];
               if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
               {
                  $title = encoding_convert_encoding($title, 'UTF-8', 'ISO-8859-1');
               }
               $title = encode(trim($title), ENCODE_HTML);
            }
            else
            {
               $title = '';
            }
            if($row['PrivateTitle'])
            {
               $privatetitle = $row['PrivateTitle'];
               if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
               {
                  $privatetitle = encoding_convert_encoding($privatetitle, 'UTF-8', 'ISO-8859-1');
               }
               $privatetitle = encode(trim($privatetitle), ENCODE_HTML);
            }
            else
            {
               $privatetitle = '';
            }

            $description = $row['Description'];
            if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
            {
               $description = encoding_convert_encoding($description, 'UTF-8', 'ISO-8859-1');
            }

            $description = trim($description);

            if(CONFIG_CORE_ESCAPE_XML)
            {
               $description = encode($description, ENCODE_BBCODE);
            }
            $this->Content[$row['ID']]['Description'] = $description;
            $this->Content[$row['ID']]['Title'] = $title;
            $this->Content[$row['ID']]['PrivateTitle'] = $privatetitle;
            $this->Content[$row['ID']]['LevelContainer'] = $arrLevelContainers[$row['LevelContainerID']]->getString('LevelContainer', 0, false);
            $this->Content[$row['ID']]['LevelContainerIdentifier'] = $row['LevelContainerIdentifier'];
            $this->Content[$row['ID']]['Date'] = encode(trim($row['Date']), ENCODE_HTML);
            $this->Content[$row['ID']]['IntellectualLevel'] = $arrLevelContainers[$row['LevelContainerID']]->IntellectualLevel;
            $this->Content[$row['ID']]['PhysicalContainer'] = $arrLevelContainers[$row['LevelContainerID']]->PhysicalContainer;
            $this->Content[$row['ID']]['EADLevel'] = $arrLevelContainers[$row['LevelContainerID']]->getString('EADLevel', 0, false);
         }



         $this->Content[$row['ID']]['Content'] = array();
         $this->Content[$row['ID']]['UserFields'] = array();
      }
      $result->free();

      // Now we need to establish parent-child relationships
      foreach($this->Content as $ID => $Content)
      {
         if($Content['ParentID'])
         {
            $this->Content[$Content['ParentID']]['Content'][$ID] = $Content;
         }
      }

      reset($this->Content);

      if(CONFIG_COLLECTIONS_ENABLE_USER_DEFINED_FIELDS)
      {
         static $fieldsPrep = NULL;
         if(!isset($fieldsPrep))
         {
            $query = "SELECT tblCollections_UserFields.* FROM tblCollections_UserFields JOIN tblCollections_Content ON tblCollections_Content.ID = tblCollections_UserFields.ContentID WHERE tblCollections_Content.CollectionID = ?$rootcontentidquery";
            $fieldsPrep = $_ARCHON->mdb2->prepare($query, array_merge(array('integer'), $rootcontentidtypes), MDB2_PREPARE_RESULT);
         }
         $result = $fieldsPrep->execute(array_merge(array($this->ID), $rootcontentidvars));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            $arrEADElements = $_ARCHON->getAllEADElements();

            while($row = $result->fetchRow())
            {
               if(!$ForEAD)
               {
                  if($row['Value'])
                  {

                     $String = $row['Title'] . ': ';
                     $String .= $row['Value'];
                     if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
                     {
                        $String = encoding_convert_encoding($String, 'UTF-8', 'ISO-8859-1');
                     }

                     $String = trim($String);

                     if(CONFIG_CORE_ESCAPE_XML)
                     {
                        $String = encode($String, ENCODE_BBCODE);
                     }

                     $this->Content[$row['ContentID']]['UserFields'][$row['ID']] = $String;
                  }
               }
               else
               {
                  if($row['Value'])
                  {
                     $eadelement = $arrEADElements[$row['EADElementID']]->EADElement;
                     $eadtag = $arrEADElements[$row['EADElementID']]->EADTag;
                     $title = $row['Title'];
                     $value = $row['Value'];
                     $titleloc = $arrEADElements[$row['EADElementID']]->TitleLocation;
                     $linebreaktag = $arrEADElements[$row['EADElementID']]->LineBreakTag;
                     if($_ARCHON->db->ServerType == 'MSSQL' AND !$_ARCHON->config->DatabaseEncodingUTF8)
                     {
                        $String = encoding_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                     }
                     $value = encode(trim($value), ENCODE_HTML);

                     $this->Content[$row['ContentID']]['UserFields'][$row['ID']] = array(
                         'Title' => $title,
                         'Value' => $value,
                         'EADElement' => $eadelement,
                         'EADTag' => $eadtag,
                         'TitleLocation' => $titleloc,
                         'LineBreakTag' => $linebreaktag
                     );
                  }
               }
            }
         }
         $result->free();
      }

      if(defined('PACKAGE_DIGITALLIBRARY') && count($this->Content) > 0)
      {
         if(!$ForEAD)
         {
            $objViewContentPhrase = $_ARCHON->getPhrase('tostring_viewcontent', PACKAGE_DIGITALLIBRARY, 0, PUBLIC_PHRASE);
            $strViewContent = $objViewContentPhrase ? $objViewContentPhrase->getPhraseValue(ENCODE_HTML) : 'View associated digital content.';


            $browsable = ($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ)) ? '' : ' AND Browsable = 1';
            $contentKeys = array_keys($this->Content);

            $arrContentStrings = array();


            $query = "SELECT CollectionContentID FROM tblDigitalLibrary_DigitalContent WHERE CollectionID = $this->ID AND CollectionContentID IN (" . implode(",", $contentKeys) . ") $browsable GROUP BY CollectionContentID HAVING COUNT(1) > 1";
            $result = $_ARCHON->mdb2->query($query);
            if(pear_isError($result))
            {
               echo($query);
               trigger_error($result->getMessage(), E_USER_ERROR);
            }
            while($row = $result->fetchRow())
            {
               $arrContentStrings[$row['CollectionContentID']] = "?p=core/search&amp;collectioncontentid=" . $row['CollectionContentID'];
            }
            $result->free();

            $query = "SELECT ID,CollectionContentID FROM tblDigitalLibrary_DigitalContent WHERE CollectionContentID IN (SELECT CollectionContentID FROM tblDigitalLibrary_DigitalContent WHERE CollectionID = $this->ID AND CollectionContentID IN (" . implode(",", $contentKeys) . ") $browsable GROUP BY CollectionContentID HAVING COUNT(1) = 1)";
            $result = $_ARCHON->mdb2->query($query);
            if(pear_isError($result))
            {
               echo($query);
               trigger_error($result->getMessage(), E_USER_ERROR);
            }
            while($row = $result->fetchRow())
            {
               $arrContentStrings[$row['CollectionContentID']] = "?p=digitallibrary/digitalcontent&amp;id=" . $row['ID'];
            }
            $result->free();

            foreach($arrContentStrings as $ContentID => $String)
            {
               $this->Content[$ContentID]['String'] .= "<a href='$String'><img class='dl' src='{$_ARCHON->PublicInterface->ImagePath}/dl.gif' title='$strViewContent' alt='$strViewContent' /></a>";
            }
         }
         else
         {
            $arrDigitalContent = array();
            $browsable = ($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ)) ? '' : ' AND Browsable = 1';
            $contentKeys = array_keys($this->Content);
            $query = "SELECT CollectionContentID,ContentURL,Title FROM tblDigitalLibrary_DigitalContent WHERE CollectionID = $this->ID AND ContentURL IS NOT NULL AND HyperlinkURL = 1 AND CollectionContentID IN (" . implode(",", $contentKeys) . ") $browsable";
            $result = $_ARCHON->mdb2->query($query);
            if(pear_isError($result))
            {
               echo($query);
               trigger_error($result->getMessage(), E_USER_ERROR);
            }
            while($row = $result->fetchRow())
            {
               $arrDigitalContent[$row['CollectionContentID']][] = array('Title' => $row['Title'], 'ContentURL' => $row['ContentURL']);
            }
            $result->free();

            foreach($arrDigitalContent as $ContentID => $arrDC)
            {
               $this->Content[$ContentID]['DigitalContent'] = $arrDC;
            }
         }
      }


      if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_SUBJECTS && count($this->Content) > 0)
      {
         $contentKeys = array_keys($this->Content);
         $query = "SELECT tblSubjects_Subjects.*, tblCollections_CollectionContentSubjectIndex.CollectionContentID FROM tblSubjects_Subjects JOIN tblCollections_CollectionContentSubjectIndex ON tblSubjects_Subjects.ID = tblCollections_CollectionContentSubjectIndex.SubjectID WHERE tblCollections_CollectionContentSubjectIndex.CollectionContentID IN (" . implode(",", $contentKeys) . ")";
         $result = $_ARCHON->mdb2->query($query);
         if(pear_isError($result))
         {
            echo($query);
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         while($row = $result->fetchRow())
         {
            $this->Content[$row['CollectionContentID']]['Subjects'][] = New Subject($row);
         }
         $result->free();
      }

      if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_CREATORS && count($this->Content) > 0)
      {
         $arrCreatorTypes = $_ARCHON->getAllCreatorTypes();
         $arrCreatorSources = $_ARCHON->getAllCreatorSources();
         $contentKeys = array_keys($this->Content);
         $query = "SELECT tblCreators_Creators.*, tblCollections_CollectionContentCreatorIndex.CollectionContentID FROM tblCreators_Creators JOIN tblCollections_CollectionContentCreatorIndex ON tblCreators_Creators.ID = tblCollections_CollectionContentCreatorIndex.CreatorID WHERE tblCollections_CollectionContentCreatorIndex.CollectionContentID IN (" . implode(",", $contentKeys) . ")";
         $result = $_ARCHON->mdb2->query($query);
         if(pear_isError($result))
         {
            echo($query);
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         while($row = $result->fetchRow())
         {
            $objCreator = New Creator($row);
            $objCreator->CreatorType = $arrCreatorTypes[$objCreator->CreatorTypeID];
            $objCreator->CreatorSource = $arrCreatorSources[$objCreator->CreatorSourceID];
            $this->Content[$row['CollectionContentID']]['Creators'][] = $objCreator;
         }
         $result->free();
      }


      return true;
   }

   /**
    * Loads Content for Collection instance
    *
    * @internal
    * This function could potentially take a very long time
    * to complete.  Before calling, consider adjusting max_execution_time
    * using set_time_limit(int seconds).
    * Also, this function will pull all content with a RootContentID of 0
    * reguardless of the parameter and attempt to fix the RootContentID for
    * at least part of that set to optimize later calls
    *
    * @param integer $RootContentID[optional] Specifies which top level container to pull content from.
    * LOADCONTENT defines may be used to grab all content or no content with this function
    *
    * @return boolean
    */
   public function dbLoadContent($RootContentID = LOADCONTENT_ALL)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load CollectionContent: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load CollectionContent: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($RootContentID) && $RootContentID != LOADCONTENT_NONE)
      {
         $_ARCHON->declareError("Could not load CollectionContent: RootContentID must be numeric.");
         return false;
      }

      if($RootContentID == LOADCONTENT_NONE)
      {
         return true;
      }

      $this->Content = array();

      static $contentPrep = NULL;
      if(!isset($contentPrep))
      {
         $rootcontentidquery = " AND ((tblCollections_Content.RootContentID = ? OR ? = " . LOADCONTENT_ALL . ") OR tblCollections_Content.RootContentID = '0')";
         $rootcontentidtypes = array('integer', 'integer');

         $query = "SELECT tblCollections_Content.* FROM tblCollections_Content JOIN tblCollections_LevelContainers on tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID WHERE tblCollections_Content.CollectionID = ?$rootcontentidquery ORDER BY tblCollections_Content.ParentID, tblCollections_Content.SortOrder";
         $contentPrep = $_ARCHON->mdb2->prepare($query, array_merge(array('integer'), $rootcontentidtypes), MDB2_PREPARE_RESULT);
      }

      $rootcontentidvars = array($RootContentID, $RootContentID);

      $result = $contentPrep->execute(array_merge(array($this->ID), $rootcontentidvars));
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      // If there is no content found.
      if(!$result->numRows())
      {
         $result->free();
         return true;
      }

      $arrLevelContainers = $_ARCHON->getAllLevelContainers();
//      $arrCollectionContentVariables = get_object_vars(New CollectionContent());

      while($row = $result->fetchRow())
      {
         $this->Content[$row['ID']] = New CollectionContent($row);
      }
      $result->free();

//      $_ARCHON->sortCollectionContentArray(&$this->Content);
      // Now we need to establish parent-child relationships
      foreach($this->Content as $ID => $objContent)
      {
         $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];
         $objContent->Collection = $this;

         if($objContent->ParentID)
         {
            $this->Content[$objContent->ParentID]->Content[$ID] = $objContent;
            $objContent->Parent = $this->Content[$objContent->ParentID];
         }
      }

      reset($this->Content);
      if(CONFIG_COLLECTIONS_ENABLE_USER_DEFINED_FIELDS)
      {
         static $fieldsPrep = NULL;
         if(!isset($fieldsPrep))
         {
            $query = "SELECT tblCollections_UserFields.* FROM tblCollections_UserFields JOIN tblCollections_Content ON tblCollections_Content.ID = tblCollections_UserFields.ContentID WHERE tblCollections_Content.CollectionID = ?$rootcontentidquery";
            $fieldsPrep = $_ARCHON->mdb2->prepare($query, array_merge(array('integer'), $rootcontentidtypes), MDB2_PREPARE_RESULT);
         }
         $result = $fieldsPrep->execute(array_merge(array($this->ID), $rootcontentidvars));
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            $arrEADElements = $_ARCHON->getAllEADElements();

            while($row = $result->fetchRow())
            {
               if($row['Value'])
               {
                  $objUserField = New UserField($row);
                  $objUserField->EADElement = $arrEADElements[$row['EADElementID']];

                  $this->Content[$row['ContentID']]->UserFields[$row['ID']] = $objUserField;
               }
            }
         }
         $result->free();
      }

      return true;
   }
    /**
     * Loads Content for Collection instance for json
     *
     * @internal
     * This function loads 100 items of collection content for a particular item, starting
     * at the offset.  Items are ordered by ParentID and then by SortOrder.    
     *
     * @param integer $offset[optional] Specifies the start now in the result set for the
     * query loading all collection content records for a single collection.  Results are
     *
     * @return boolean
     */
    public function dbLoadContentjson($offset = 0)
    {
        global $_ARCHON;
        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not load CollectionContent: Collection ID not defined.");
            return false;
        }

        if(!is_natural($this->ID))
        {
            $_ARCHON->declareError("Could not load CollectionContent: Collection ID must be numeric.");
            return false;
        }
        
        $this->Content = array();
		$query = "SELECT tblCollections_Content.* FROM tblCollections_Content JOIN tblCollections_LevelContainers on tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID WHERE tblCollections_Content.CollectionID = ". $this->ID . " ORDER BY tblCollections_Content.ParentID, tblCollections_Content.SortOrder";
		$_ARCHON->mdb2->setLimit(100,$offset);	
        $result = $_ARCHON->mdb2->query($query);
        if(pear_isError($result))
        {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        if(!$result->numRows())          // If there is no content found
        {
            $result->free();
            return true;
        }
        $arrLevelContainers = $_ARCHON->getAllLevelContainers();
        while($row = $result->fetchRow())
        {
            $this->Content[$row['ID']] = New CollectionContent($row);
        }
        $result->free();
        foreach($this->Content as $ID => $objContent)
        {
            $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];
         	array($objContent->UserFields);  //put in null property even if they don't use user fields
            
            if(CONFIG_COLLECTIONS_ENABLE_USER_DEFINED_FIELDS)
            {
            $query = "SELECT tblCollections_UserFields.* FROM tblCollections_UserFields WHERE tblCollections_UserFields.ContentID = " . $objContent->ID;
            $result = $_ARCHON->mdb2->query($query);
            if($result->numRows())
            {
                $arrEADElements = $_ARCHON->getAllEADElements();

                while($row = $result->fetchRow())
                {
                    if($row['Value'])
                    {
                        $objUserField = New UserField($row);
                        $objUserField->EADElement = $arrEADElements[$row['EADElementID']];
                        $this->Content[$row['ContentID']]->UserFields[$row['ID']] = $objUserField;
                    }
                }
            }
            $result->free();
            }
            
        }
        return true;
    }
   /**
    * Loads Creators for Collection instance
    *
    * @return boolean
    */
   public function dbLoadCreators()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Creators: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Creators: Collection ID must be numeric.");
         return false;
      }

      $this->Creators = array();

      $query = "SELECT tblCreators_Creators.*, tblCollections_CollectionCreatorIndex.PrimaryCreator FROM tblCreators_Creators JOIN tblCollections_CollectionCreatorIndex ON tblCreators_Creators.ID = tblCollections_CollectionCreatorIndex.CreatorID WHERE tblCollections_CollectionCreatorIndex.CollectionID = ? ORDER BY tblCollections_CollectionCreatorIndex.PrimaryCreator DESC, tblCreators_Creators.Name";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No creators found, return.
         $result->free();
         $prep->free();
         return true;
      }

      $arrCreatorTypes = $_ARCHON->getAllCreatorTypes();
      $arrCreatorSources = $_ARCHON->getAllCreatorSources();

      while($row = $result->fetchRow())
      {
         $objCreator = New Creator($row);
         $objCreator->CreatorType = $arrCreatorTypes[$objCreator->CreatorTypeID];
         $objCreator->CreatorSource = $arrCreatorSources[$objCreator->CreatorSourceID];

         $this->Creators[$row['ID']] = $objCreator;

         if($row['PrimaryCreator'])
         {
            //if(empty($this->PrimaryCreators))
            //{
            $this->PrimaryCreator = $this->Creators[$objCreator->ID];
            //}
            //$this->PrimaryCreators[$objCreator->ID] = $this->Creators[$objCreator->ID];
         }
      }
      $result->free();
      $prep->free();

      return true;
   }

   /**
    * Loads Languages for Collection instance
    *
    * @return boolean
    */
   public function dbLoadLanguages()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Languages: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Languages: Collection ID must be numeric.");
         return false;
      }

      $this->Languages = array();

      $query = "SELECT LanguageID FROM tblCollections_CollectionLanguageIndex WHERE CollectionID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No languages found, return.
         $result->free();
         $prep->free();
         return true;
      }

      while($row = $result->fetchRow())
      {
         $this->Languages[$row['LanguageID']] = New Language($row['LanguageID']);
         $this->Languages[$row['LanguageID']]->dbLoad();
      }
      $result->free();
      $prep->free();

      return true;
   }

   /**
    * Loads Location Entries for Collection instance
    *
    * @return boolean
    */
   public function dbLoadLocationEntries()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load LocationEntries: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load LocationEntries: Collection ID must be numeric.");
         return false;
      }

      $this->LocationEntries = array();

      $query = "SELECT tblCollections_CollectionLocationIndex.* FROM tblCollections_CollectionLocationIndex JOIN tblCollections_Locations ON tblCollections_Locations.ID = tblCollections_CollectionLocationIndex.LocationID WHERE tblCollections_CollectionLocationIndex.CollectionID = ? ORDER BY tblCollections_CollectionLocationIndex.Content, tblCollections_Locations.Location, tblCollections_CollectionLocationIndex.RangeValue, tblCollections_CollectionLocationIndex.Section, tblCollections_CollectionLocationIndex.Shelf";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No location entries found, return.
         return true;
      }

      $arrLocations = $_ARCHON->getAllLocations();
      $arrExtentUnits = $_ARCHON->getAllExtentUnits();

      while($row = $result->fetchRow())
      {
         $objLocationEntry = New LocationEntry($row);
         $objLocationEntry->Location = $arrLocations[$objLocationEntry->LocationID];
         $objLocationEntry->ExtentUnit = $arrExtentUnits[$objLocationEntry->ExtentUnitID];

         $this->LocationEntries[$row['ID']] = $objLocationEntry;
      }
      $result->free();
      $prep->free();

      return true;
   }

   /**
    * Loads Books for Collection instance
    *
    * @return boolean
    */
   public function dbLoadBooks()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Collections: Book ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Collections: Book ID must be numeric.");
         return false;
      }

      $this->Books = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Books.* FROM tblCollections_Books JOIN tblCollections_CollectionBookIndex ON tblCollections_Books.ID = tblCollections_CollectionBookIndex.BookID WHERE tblCollections_CollectionBookIndex.CollectionID = ? ORDER BY tblCollections_Books.Title";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Books[$row['ID']] = New Book($row);
      }
      $result->free();

      return true;
   }

   /**
    * Loads All Related Objects for Collection instance
    *
    * @return boolean
    */
   public function dbLoadRelatedObjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load RelatedObjects: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load RelatedObjects: Collection ID must be numeric.");
         return false;
      }

      if($this->RepositoryID)
      {
         $this->Repository = New Repository($this->RepositoryID);
         $this->Repository->dbLoad();
      }

      if($this->ClassificationID)
      {
         $this->Classification = New Classification($this->ClassificationID);
         $this->Classification->dbLoad();
      }

      if($this->ExtentUnitID)
      {
         $this->ExtentUnit = New ExtentUnit($this->ExtentUnitID);
         $this->ExtentUnit->dbLoad();
      }
      else
      {
         $this->ExtentUnit = New ExtentUnit($this->ExtentUnitID);
      }

      if($this->MaterialTypeID)
      {
         $this->MaterialType = New MaterialType($this->MaterialTypeID);
         $this->MaterialType->dbLoad();
      }

      if($this->DescriptiveRulesID)
      {
         $this->DescriptiveRules = New DescriptiveRules($this->DescriptiveRulesID);
         $this->DescriptiveRules->dbLoad();
      }

      if($this->FindingLanguageID)
      {
         $this->FindingLanguage = New Language($this->FindingLanguageID);
         $this->FindingLanguage->dbLoad();
      }

      // Creators, Subjects, and Location Entries do not need instances created here,
      // since they have their own load function within the Collection object.

      $this->dbLoadCreators();
      $this->dbLoadSubjects();
      $this->dbLoadLocationEntries();
      $this->dbLoadLanguages();

      // Load root-level content only.
      // This shouldn't be costly, because if a collection
      // is organized well, there shouldn't be too much
      // root-level content.
      $this->dbLoadRootContent();

      if($_ARCHON->Error)
      {
         return false;
      }
      else
      {
         return true;
      }
   }

   /**
    * Loads Root-level Content for Collection instance
    *
    * @return boolean
    */
   public function dbLoadRootContent()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load RootContent: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load RootContent: Collection ID must be numeric.");
         return false;
      }

      $this->Content = array();

      $query = "SELECT tblCollections_Content.* FROM tblCollections_Content JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID WHERE CollectionID = ? AND ParentID = '0' ORDER BY tblCollections_Content.SortOrder";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No creators found, return.
         return true;
      }

      $arrLevelContainers = $_ARCHON->getAllLevelContainers();

      while($row = $result->fetchRow())
      {
         $this->Content[$row['ID']] = New CollectionContent($row);
         $this->Content[$row['ID']]->LevelContainer = $arrLevelContainers[$row['LevelContainerID']];
      }
      $result->free();
      $prep->free();

//      $_ARCHON->sortCollectionContentArray(&$this->Content);

      return true;
   }

   /**
    * Loads Subjects for Collection instance
    *
    * @return boolean
    */
   public function dbLoadSubjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Subjects: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Subjects: Collection ID must be numeric.");
         return false;
      }

      $this->Subjects = array();

      $query = "SELECT tblSubjects_Subjects.* FROM tblSubjects_Subjects JOIN tblCollections_CollectionSubjectIndex ON tblSubjects_Subjects.ID = tblCollections_CollectionSubjectIndex.SubjectID WHERE tblCollections_CollectionSubjectIndex.CollectionID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No subjects found, return.
         return true;
      }

      $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();

      while($row = $result->fetchRow())
      {
         // We can't add the subjects to the final array just yet
         // because the subjects need to be sorted based upon how
         // they will end up displaying (parent subjects will
         // be concatenated before child subjects).
         $objSubject = New Subject($row);
         $objSubject->SubjectType = $arrSubjectTypes[$objSubject->SubjectTypeID];

         $arrSorter[$objSubject->toString(LINK_NONE, true)] = $objSubject;

         // this should now be taken care of by calling dbLoad() within the subject class which is invoked by toString()
//         // In case parents are used multiple times
//         $objTransSubject = $objSubject;
//         while($objTransSubject)
//         {
//            $_ARCHON->MemoryCache['Objects']['Subject'][$objTransSubject->ID] = $objTransSubject;
//            $objTransSubject = $objTransSubject->Parent;
//         }
      }
      $result->free();
      $prep->free();

      natcaseksort($arrSorter);

      if(!empty($arrSorter))
      {
         foreach($arrSorter as $objSubject)
         {
            $this->Subjects[$objSubject->ID] = $objSubject;
         }
      }

      return true;
   }

   public function dbUpdateRelatedCreators($arrRelatedIDs, $arrPrimaryCreatorIDs)
   {
      global $_ARCHON;


      if(!$_ARCHON->updateCreatorRelations($this, MODULE_COLLECTIONS, 'tblCollections_CollectionCreatorIndex', $arrRelatedIDs, $arrPrimaryCreatorIDs))
      {
         return false;
      }


      return true;
   }

   /**
    * Relate Creator to Collection
    *
    * @param integer $CreatorID
    * @return boolean
    */
   public function dbRelateCreator($CreatorID)
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not relate Creator: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not relate Creator: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not relate Creator: Collection ID must be numeric.");
         return false;
      }

      // Make sure user isn't dealing with a collection from another repository if they're limited
      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not relate Creator: Collections may only be altered for the primary repository.");
         return false;
      }

      if(!is_natural($CreatorID) || !$CreatorID)
      {
         $_ARCHON->declareError("Could not relate Creator: Creator ID must be numeric.");
         return false;
      }

      static $existPrep = NULL;
      if(!isset($existPrep))
      {
         $query = "SELECT ID FROM tblCreators_Creators WHERE ID = ?";
         $existPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $existPrep->execute($CreatorID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $creatorrow = $result->fetchRow();
      $result->free();

      if(!$creatorrow['ID'])
      {
         $_ARCHON->declareError("Could not relate Creator: Creator ID $CreatorID not found in database.");
         return false;
      }

      static $checkPrep = NULL;
      if(!isset($checkPrep))
      {
         $checkquery = "SELECT ID FROM tblCollections_CollectionCreatorIndex WHERE CollectionID = ? AND CreatorID = ?";
         $checkPrep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkPrep->execute(array($this->ID, $CreatorID));
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not relate Creator: Creator ID $CreatorID already related to Collection ID $this->ID.");
         return false;
      }

      // Assume this creator is the primary creator.
      $PrimaryCreator = 1;

      // If a creator is already assigned as the primary creator, don't assign this creator as primary.
      static $primaryPrep = NULL;
      if(!isset($primaryPrep))
      {
         $query = "SELECT PrimaryCreator FROM tblCollections_CollectionCreatorIndex WHERE CollectionID = ?";
         $primaryPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $primaryPrep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         if($row['PrimaryCreator'])
         {
            $PrimaryCreator = 0;
         }
      }
      $result->free();

      static $insertPrep = NULL;
      if(!isset($insertPrep))
      {
         $query = "INSERT INTO tblCollections_CollectionCreatorIndex (CollectionID, CreatorID, PrimaryCreator) VALUES (?, ?, ?)";
         $insertPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $insertPrep->execute(array($this->ID, $CreatorID, $PrimaryCreator));
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $result = $checkPrep->execute(array($this->ID, $CreatorID));
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not relate Creator: Unable to update the database table.");
         return false;
      }

      // Add the creator to the Collections's Creators[] array
      $objCreator = New Creator($creatorrow);

      if($PrimaryCreator)
      {
         $this->PrimaryCreator = $objCreator;
      }

      $this->Creators[$CreatorID] = $objCreator;

      $_ARCHON->log("tblCollections_CollectionCreatorIndex", $row['ID']);
      $_ARCHON->log("tblCollections_Collections", $this->ID);

      return true;
   }

   public function dbUpdateRelatedLanguages($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONS, 'Language', 'tblCollections_CollectionLanguageIndex', NULL, $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   /**
    * Relate Language to Collection
    *
    * @param integer $LanguageID
    * @return boolean
    */
   public function dbRelateLanguage($LanguageID)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONS, 'Language', 'tblCollections_CollectionLanguageIndex', NULL, array($LanguageID), ADD))
      {
         return false;
      }

      return true;
   }

   public function dbUpdateRelatedSubjects($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONS, 'Subject', 'tblCollections_CollectionSubjectIndex', 'tblSubjects_Subjects', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   /**
    * Relate Subject to Collection
    *
    * @param integer $SubjectID
    * @return boolean
    */
   public function dbRelateSubject($SubjectID)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONS, 'Subject', 'tblCollections_CollectionSubjectIndex', 'tblSubjects_Subjects', array($SubjectID), ADD))
      {
         return false;
      }

      $objSubject = New Subject($SubjectID);
      $objSubject->dbLoad();

      $this->Subjects[$SubjectID] = $objSubject;

      return true;
   }

   public function dbUpdateRelatedBooks($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONS, 'Book', 'tblCollections_CollectionBookIndex', 'tblCollections_Books', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Collection to the database
    *
    * If the ID value has been set, dbStore will try to update
    * an existing collection.  Otherwise, it will add a new collection.
    *
    * IMPORTANT:
    *
    *    dbStore will NOT store any information added/modified to
    *    ANY member objects!  If you wish to update the database
    *    entries for member objects, you will need to separately
    *    call the object's dbStore method.
    *
    * @return boolean
    */
   public function dbStoreCollection()
   {
      global $_ARCHON;

      if($this->ID)
      {
         FindingAidCache::setDirty($this->ID);
      }

      if($this->OtherURL && !preg_match('/[\w\d]+:\/\//u', $this->OtherURL))
      {
         $this->OtherURL = 'http://' . $this->OtherURL;
      }

      if($this->OrigCopiesURL && !preg_match('/[\w\d]+:\/\//u', $this->OrigCopiesURL))
      {
         $this->OrigCopiesURL = 'http://' . $this->OrigCopiesURL;
      }

      if($this->RelatedMaterialsURL && !preg_match('/[\w\d]+:\/\//u', $this->RelatedMaterialsURL))
      {
         $this->RelatedMaterialsURL = 'http://' . $this->RelatedMaterialsURL;
      }

      // Transform non-table variables into table variables if set.
      if($this->AcquisitionDateYear || $this->AcquisitionDateMonth || $this->AcquisitionDateDay)
      {
         $this->AcquisitionDate = str_pad($this->AcquisitionDateYear, 4, "0", STR_PAD_LEFT);
         $this->AcquisitionDate .= str_pad($this->AcquisitionDateMonth, 2, "0", STR_PAD_LEFT);
         $this->AcquisitionDate .= str_pad($this->AcquisitionDateDay, 2, "0", STR_PAD_LEFT);
      }

      if($this->PublicationDateYear || $this->PublicationDateMonth || $this->PublicationDateDay)
      {
         $this->PublicationDate = str_pad($this->PublicationDateYear, 4, "0", STR_PAD_LEFT);
         $this->PublicationDate .= str_pad($this->PublicationDateMonth, 2, "0", STR_PAD_LEFT);
         $this->PublicationDate .= str_pad($this->PublicationDateDay, 2, "0", STR_PAD_LEFT);
      }

      $this->Extent = str_replace(',', '.', $this->Extent);

      $checkqueries = array();
      $checktypes = array();
      $checkvars = array();
      $checkqueryerrors = array();
      $problemfields = array();

      if($this->InclusiveDates)
      {
         $checkqueries[] = "SELECT ID FROM tblCollections_Collections WHERE Title = ? AND SortTitle = ? AND RepositoryID = ? AND ClassificationID = ? AND InclusiveDates = ? AND ID != ?";
         $checktypes[] = array('text', 'text', 'integer', 'integer', 'text', 'integer');
         $checkvars[] = array($this->Title, $this->SortTitle, $this->RepositoryID, $this->ClassificationID, $this->InclusiveDates, $this->ID);
         $checkqueryerrors[] = "A Collection with the same TitleAndSortTitleAndRepositoryAndInclusiveDates already exists in the database";
         $problemfields[] = array('Title', 'SortTitle', 'RepositoryID', 'InclusiveDates');
      }
      else
      {
         $checkqueries[] = "SELECT ID FROM tblCollections_Collections WHERE Title = ? AND SortTitle = ? AND RepositoryID = ? AND ClassificationID = ? AND InclusiveDates IS NULL AND ID != ?";
         $checktypes[] = array('text', 'text', 'integer', 'integer', 'integer');
         $checkvars[] = array($this->Title, $this->SortTitle, $this->RepositoryID, $this->ClassificationID, $this->ID);
         $checkqueryerrors[] = "A Collection with the same TitleAndSortTitleAndRepository already exists in the database";
         $problemfields[] = array('Title', 'SortTitle', 'RepositoryID');
      }
      $requiredfields = array('Title', 'SortTitle', 'RepositoryID');
      $ignoredfields = array($AcquisitionDateMonth, $AcquisitionDateDay, $AcquisitionDateYear, $PublicationDateMonth, $PublicationDateDay, $PublicationDateYear);

      if($this->CollectionIdentifier)
      {
         if(is_natural($this->CollectionIdentifier))
         {
            $this->CollectionIdentifier = str_pad($this->CollectionIdentifier, CONFIG_COLLECTIONS_COLLECTION_IDENTIFIER_MINIMUM_LENGTH, '0', STR_PAD_LEFT);
         }

         $checkqueries[] = "SELECT ID FROM tblCollections_Collections WHERE ClassificationID = ? AND CollectionIdentifier = ? AND ID != ?";
         $checktypes[] = array('integer', 'text', 'integer');
         $checkvars[] = array($this->ClassificationID, $this->CollectionIdentifier, $this->ID);
         $checkqueryerrors[] = "A Collection with the same ClassificationAndCollectionIdentifier already exists in the database";
         $problemfields[] = array('CollectionIdentifier', 'ClassificationID');
      }

      if(!$_ARCHON->storeObject($this, MODULE_COLLECTIONS, 'tblCollections_Collections', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields, $ignoredfields))
      {
         return false;
      }

      return true;
   }

   /**
    * Unrelates all creators for collection
    *
    * @return boolean
    */
   public function dbUnrelateAllCreators()
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Creators: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Creators: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Creators: Collection ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Creators: Collections may only be altered for the primary repository.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblCollections_CollectionCreatorIndex WHERE CollectionID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute($this->ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblCollections_CollectionCreatorIndex WHERE CollectionID = ?";
         $_ARCHON->setLimit(1);
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Creators: Unable to update the database table.");
         return false;
      }
      else
      {
         $this->Creators = array();
         $this->PrimaryCreator = NULL;

         $_ARCHON->log("tblCollections_CollectionCreatorIndex", "-1");
         $_ARCHON->log("tblCollections_Collections", $this->ID);

         return true;
      }
   }

   /**
    * Unrelates all languages for collection
    *
    * @return boolean
    */
   public function dbUnrelateAllLanguages()
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Languages: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Languages: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Languages: Collection ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Languages: Collections may only be altered for the primary repository.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblCollections_CollectionLanguageIndex WHERE CollectionID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute($this->ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblCollections_CollectionLanguageIndex WHERE CollectionID = ?";
         $_ARCHON->setLimit(1);
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Languages: Unable to update the database table.");
         return false;
      }
      else
      {
         $this->Languages = array();

         $_ARCHON->log("tblCollections_CollectionLanguageIndex", "-1");
         $_ARCHON->log("tblCollections_Collections", $this->ID);

         return true;
      }
   }

   /**
    * Unrelates all subjects for collection
    *
    * @return boolean
    */
   public function dbUnrelateAllSubjects()
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Collection ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Collections may only be altered for the primary repository.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblCollections_CollectionSubjectIndex WHERE CollectionID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute($this->ID);
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblCollections_CollectionSubjectIndex WHERE CollectionID = ?";
         $_ARCHON->setLimit(1);
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Unable to update the database table.");
         return false;
      }
      else
      {
         $this->Subjects = array();
         $this->PrimarySubject = NULL;

         $_ARCHON->log("tblCollections_CollectionSubjectIndex", "-1");
         $_ARCHON->log("tblCollections_Collections", $this->ID);

         return true;
      }
   }

   /**
    * Unrelate Creator from Collection
    *
    * @param integer $CreatorID
    * @return boolean
    */
   public function dbUnrelateCreator($CreatorID)
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Creator: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Creator: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Creator: Collection ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Creator: Collections may only be altered for the primary repository.");
         return false;
      }

      if(!is_natural($CreatorID) || !$CreatorID)
      {
         $_ARCHON->declareError("Could not unrelate Creator: Creator ID must be numeric.");
         return false;
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblCollections_CollectionCreatorIndex WHERE CollectionID = ? AND CreatorID = ?";
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute(array($this->ID, $CreatorID));
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      $RowID = $row['ID'];

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Creator: Creator ID $CreatorID is not related to Collection ID $this->ID.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblCollections_CollectionCreatorIndex WHERE CollectionID = ? AND CreatorID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($this->ID, $CreatorID));
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $result = $checkprep->execute(array($this->ID, $CreatorID));
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Creator: Unable to update the database table.");
         return false;
      }
      else
      {
         unset($this->Creators[$CreatorID]);

         $_ARCHON->log("tblCollections_CollectionCreatorIndex", $RowID);
         $_ARCHON->log("tblCollections_Collections", $this->ID);

         return true;
      }
   }

   /**
    * Unrelate Subject from Collection
    *
    * @param integer $SubjectID
    * @return boolean
    */
   public function dbUnrelateSubject($SubjectID)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONS, 'Subject', 'tblCollections_CollectionSubjectIndex', 'tblSubjects_Subjects', array($SubjectID), DELETE))
      {
         return false;
      }

      if(isset($this->Subjects[$SubjectID]))
      {
         unset($this->Subjects[$SubjectID]);
      }

      return true;
   }

   /**
    * Checks to see if all RootContentIDs for this collection are set
    *
    * @return boolean
    */
   public function rootContentIDsSet()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not check Content: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not check Content: Collection ID must be numeric");
         return false;
      }

      $_ARCHON->mdb2->setLimit(1);
      $prep = $_ARCHON->mdb2->prepare("SELECT ID FROM tblCollections_Content WHERE CollectionID = ? AND RootContentID = '0'", 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(pear_isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $numRows = $result->numRows();
      $result->free();

      if($numRows > 0)
      {
         return false;
      }
      else
      {
         return true;
      }
   }

   public function enabled()
   {
      global $_ARCHON;

      if(!$this->RepositoryID)
      {
         $this->dbLoad();
      }

      $readPermissions = true;

      if(!$this->Enabled)
      {
         $readPermissions = false;

         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ)
                 || ($_ARCHON->Security->userHasAdministrativeAccess() && !CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS)
                 || (CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS && $_ARCHON->Security->verifyRepositoryPermissions($this->RepositoryID)))
         {
            $readPermissions = true;
         }
      }
      return $readPermissions;
   }

   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, DELETE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete Collection: Collections may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   public function verifyRepositoryPermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->Session->User->RepositoryLimit)
      {
         return true;
      }

      if($this->ID) // Old repository may be disallowed or maybe an empty object
      {
         static $prep = NULL;
         if(!isset($prep))
         {
            $query = "SELECT RepositoryID FROM tblCollections_Collections WHERE ID = ?";
            $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $prep->execute($this->ID);
         if(pear_isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($row = $result->fetchRow())
         {
            $prevRepositoryID = $row['RepositoryID'];
         }
         $result->free();

         if(!$prevRepositoryID || !$_ARCHON->Security->verifyRepositoryPermissions($prevRepositoryID))
         {
            return false;
         }

         if(!$this->RepositoryID)
         {
            $this->RepositoryID = $prevRepositoryID;
            return true; //no sense in re-running the same permissions test below
         }
      }

      if(!$this->RepositoryID || !$_ARCHON->Security->verifyRepositoryPermissions($this->RepositoryID))
      {
         return false;
      }


      return true;
   }

   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE)))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not store Collection: Collections may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   /**
    * Returns a formatted string of a traversal of subject instance
    *
    * @param integer $MakeIntoLink[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE, $ConcatinateCollectionIdentifier = false, $UseSortTitle = false)
   {
      global $_ARCHON;

      if(!$this->Title || !$this->RepositoryID)
      {
         $this->dbLoad();
      }

      if($this->RepositoryID && !$this->Repository)
      {
         $this->Repository = New Repository($this->RepositoryID);
         $this->Repository->dbLoad();
         $_ARCHON->cacheObject($this->Repository);
      }

      if($ConcatinateCollectionIdentifier && $this->CollectionIdentifier)
      {
         $String = $this->getString('CollectionIdentifier') . ' ';
      }

      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         if($_ARCHON->QueryStringURL)
         {
            $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         }

         $String .= " <a href='?p=collections/controlcard&amp;id={$this->ID}{$q}'> ";
      }

      if($UseSortTitle)
      {
         $String .= $this->getString('SortTitle');
      }
      else
      {
         $String .= $this->getString('Title');
      }

      if($this->InclusiveDates)
      {
         $String .= ', ' . $this->getString('InclusiveDates');
      }

      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         $String .= '</a>';
      }

      if(!$_ARCHON->PublicInterface->DisableTheme && !$_ARCHON->AdministrativeInterface && $this->ID)
      {


         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
         {
            $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
            $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

            $String .= "<a href='?p=admin/collections/collections&amp;id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
         }
         elseif(!$_ARCHON->Security->userHasAdministrativeAccess() && ($this->Repository->ResearchFunctionality & RESEARCH_COLLECTIONS))
         {
            $objRemovePhrase = Phrase::getPhrase('tostring_remove', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strRemove = $objRemovePhrase ? $objRemovePhrase->getPhraseValue(ENCODE_HTML) : 'Remove from your cart.';
            $objAddToPhrase = Phrase::getPhrase('tostring_addto', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strAddTo = $objAddToPhrase ? $objAddToPhrase->getPhraseValue(ENCODE_HTML) : 'Add to your cart.';

            $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();

            if(!$this->ignoreCart && $arrCart->Collections[$this->ID])
            {
               $String .= "<a id='cid" . $this->ID . "' class='research_delete' onclick='triggerResearchCartEvent(this, {collectionid:{$this->ID},collectioncontentid:0}); return false;' href='#'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/removefromcart.gif' title='$strRemove' alt='$strRemove'/></a>";
            }
            else
            {
               $String .= "<a id='cid" . $this->ID . "' class='research_add' onclick='triggerResearchCartEvent(this, {collectionid:{$this->ID},collectioncontentid:0}); return false;' href='#'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/addtocart.gif' title='$strAddTo' alt='$strAddTo'/></a>";
            }
         }
      }

      return $String;
   }

   public function getNormalDate()
   {
      if(!$this->NormalDateBegin)
      {
         return '';
      }

      if(!$this->NormalDateEnd)
      {
         return $this->NormalDateBegin;
      }
      elseif($this->NormalDateEnd == $this->NormalDateBegin)
      {
         return $this->NormalDateBegin;
      }
      else
      {
         return $this->NormalDateBegin . '/' . $this->NormalDateEnd;
      }
   }

   public function getPublicationDate()
   {
      if(!$this->PublicationDateYear)
      {
         return '';
      }

      $date = $this->PublicationDateYear;
      $date = ($this->PublicationDateMonth) ? $date . "-" . $this->PublicationDateMonth : $date;
      $date = ($this->PublicationDateDay) ? $date . "-" . $this->PublicationDateDay : $date;

      return $date;
   }

   /**
    * These variables correspond directly to the fields in the tblCollections_Collections table.
    */

   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var integer
    */
   public $Enabled = 1;

   /** @var integer */
   public $RepositoryID = 0;

   /** @var integer */
   public $ClassificationID = 0;

   /**
    * @var string
    */
   public $CollectionIdentifier = '';

   /** @var string */
   public $Title = '';

   /** @var string */
   public $SortTitle = '';

   /** @var string */
   public $InclusiveDates = '';

   /** @var string */
   public $PredominantDates = '';

   /** @var string */
   public $NormalDateBegin = '';

   /** @var string */
   public $NormalDateEnd = '';

   /** @var string */
   public $FindingAidAuthor = '';

   /** @var string */
   public $Extent = 0.00;

   /** @var integer */
   public $ExtentUnitID = 0;

   /** @var string */
   public $Scope = '';

   /** @var string */
   public $Abstract = '';

   /** @var string */
   public $Arrangement = '';

   /** @var integer */
   public $MaterialTypeID = 0;

   /** @var string */
   public $AltExtentStatement = '';

   /** @var string */
   public $AccessRestrictions = '';

   /** @var string */
   public $UseRestrictions = '';

   /** @var string */
   public $PhysicalAccess = '';

   /** @var string */
   public $TechnicalAccess = '';

   /** @var string */
   public $AcquisitionSource = '';

   /** @var string */
   public $AcquisitionMethod = '';

   /**
    * Format: YYYYMMDD
    *
    * @var string
    */
   public $AcquisitionDate = '';

   /** @var string */
   public $AppraisalInfo = '';

   /** @var string */
   public $AccrualInfo = '';

   /** @var string */
   public $CustodialHistory = '';

   /** @var string */
   public $OrigCopiesNote = '';

   /** @var string */
   public $OrigCopiesURL = '';

   /** @var string */
   public $RelatedMaterials = '';

   /** @var string */
   public $RelatedMaterialsURL = '';

   /** @var string */
   public $RelatedPublications = '';

   /** @var string */
   public $SeparatedMaterials = '';

   /** @var string */
   public $PreferredCitation = '';

   /** @var string */
   public $OtherNote = '';

   /** @var string */
   public $OtherURL = '';

   /** @var integer */
   public $DescriptiveRulesID = 0;

   /** @var string */
   public $ProcessingInfo = '';

   /** @var string */
   public $RevisionHistory = '';

   /**
    * Format: YYYYMMDD
    *
    * @var string
    */
   public $PublicationDate = '';

   /** @var string */
   public $PublicationNote = '';

   /** @var integer */
   public $FindingLanguageID = 0;

   /** @var string */
   public $BiogHist = '';

   /** @var string */
   public $BiogHistAuthor = '';

   /**
    * These variables are generated during loading
    */

   /** @var string */
   public $AcquisitionDateMonth;

   /** @var string */
   public $AcquisitionDateDay;

   /** @var string */
   public $AcquisitionDateYear;

   /** @var string */
   public $PublicationDateMonth;

   /** @var string */
   public $PublicationDateDay;

   /** @var string */
   public $PublicationDateYear;

   /**
    * These variables store other objects which relate to the Collection
    */

   /**
    * Array containing Content for Collection
    *
    * @var CollectionContent[]
    */
   public $Content = array();

   /**
    * Array containing Creators for Collection
    *
    * @var Creator[]
    */
   public $Creators = array();

   /**
    * Array containing Subjects for Collection
    *
    * @var Subject[]
    */
   public $Subjects = array();

   /**
    * Array containing Location Entries for Collection
    *
    * @var LocationEntry[]
    */
   public $LocationEntries = array();

   /**
    * Array containing Languages for Collection
    *
    * @var Language[]
    */
   public $Languages = array();

   /**
    * Array containing Books for Collection
    *
    * @var Book[]
    */
   public $Books = array();

   /**
    * Repository where the Collection is stored
    *
    * @var Repository
    */
   public $Repository = NULL;

   /** @var Classification */
   public $Classification = NULL;

   /** @var ExtentUnit */
   public $ExtentUnit = NULL;

   /** @var MaterialType */
   public $MaterialType = NULL;

   /**
    * Language of the Finding Aid
    *
    * @var Language
    */
   public $FindingLanguage = NULL;

   /**
    * Descriptive Rules Used for the Finding Aid
    *
    * @var DescriptiveRules
    */
   public $DescriptiveRules = NULL;

   /**
    * Creator Object of the Primary Creator for Collection
    *
    * @var Creator
    */
   public $PrimaryCreator = NULL;
   public $PrimaryCreators = array();
   public $ToStringFields = array('ID', 'Title', 'SortTitle', 'ClassificationID', 'InclusiveDates', 'CollectionIdentifier');
   public $ignoreCart = NULL;

}

$_ARCHON->mixClasses('Collection', 'Collections_Collection');
?>
