<?php

abstract class Subjects_Subject
{

   /**
    * Deletes Subject from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_SUBJECTS, 'tblSubjects_Subjects'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads Subject from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblSubjects_Subjects'))
      {
         return false;
      }

      if($this->ParentID)
      {
         $this->Parent = New Subject($this->ParentID);
         $this->Parent->dbLoad();
         $_ARCHON->cacheObject($this->Parent);
      }

      if($this->SubjectTypeID)
      {
         $this->SubjectType = New SubjectType($this->SubjectTypeID);
         $this->SubjectType->dbLoad();
         // since these are JSONObjects, they are already "cached"
      }

      if($this->SubjectSourceID)
      {
         $this->SubjectSource = New SubjectSource($this->SubjectSourceID);
         $this->SubjectSource->dbLoad();
         $_ARCHON->cacheObject($this->SubjectSource);
      }

      return true;
   }

   /**
    * Stores Subject to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if($this->ParentID)
      {
         $this->SubjectSourceID = 0;
      }

      $checkqueries = array();
      $checktypes = array();
      $checkvars = array();
      $checkqueryerrors = array();
      $problemfields = array();



      if($this->Identifier)
      {
         $checkqueries[] = "SELECT ID FROM tblSubjects_Subjects WHERE Identifier = ? AND ID != ?";
         $checktypes[] = array('text', 'integer');
         $checkvars[] = array($this->Identifier, $this->ID);
         $problemfields[] = array('Identifier');
         $checkqueryerrors[] = "A Subject with the same Identifier already exists in the database";
      }


      $checkqueries[] = "SELECT ID FROM tblSubjects_Subjects WHERE Subject = ? AND ParentID = ? AND ID != ?";
      $checktypes[] = array('text', 'integer', 'integer');
      $checkvars[] = array($this->Subject, $this->ParentID, $this->ID);
      $checkqueryerrors[] = "A Subject with the same NameAndParent already exists in the database";
      $problemfields[] = array('Subject', 'ParentID');
      $requiredfields = array('Subject');

      if(!$_ARCHON->storeObject($this, MODULE_SUBJECTS, 'tblSubjects_Subjects', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }

   public function hasChildren()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT ID FROM tblSubjects_Subjects WHERE ParentID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $_ARCHON->mdb2->setLimit(1);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $hasChildren = false;

      if($result->numRows())
      {
         $hasChildren = true;
      }

      $result->free();

      return $hasChildren;
   }

   /**
    * Returns a formatted string of a traversal of subject instance
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateParents[optional]
    * @param string $Delimiter[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE, $ConcatinateParents = false, $Delimiter = " -- ")
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Subject to string: Subject ID not defined.");
         return false;
      }

      if(!$this->Subject)
      {
         $this->dbLoad();
      }

      if($ConcatinateParents && $this->ParentID && !$this->Parent)
      {
         $this->Parent = New Subject($this->ParentID);
         $this->Parent->dbLoad();
      }

      $objTmp = $this;

      while($objTmp)
      {
         if($MakeIntoLink == LINK_EACH)
         {
            $String = ($objTmp->ID != $this->ID) ? "<a href='?p=subjects/subjects&amp;id={$objTmp->ID}'>" . $objTmp->getString('Subject') . "</a>" . $String : "<a href='?p=subjects/subjects&amp;id={$objTmp->ID}'>" . $objTmp->getString('Subject') . "</a>" . $String;
         }
         else
         {
            $String = $objTmp->getString('Subject') . $String;
         }

         if($objTmp->ParentID && $ConcatinateParents)
         {
            $String = $Delimiter . $String;

            $objTmp = $objTmp->Parent;
         }
         else
         {
            $objTmp = NULL;
         }
      }

      if($MakeIntoLink == LINK_TOTAL)
      {
         $String = "<a href='?p=subjects/subjects&amp;id={$this->ID}'>$String</a>";
      }

      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $_ARCHON->Security->verifyPermissions(MODULE_SUBJECTS, UPDATE))
      {
         

         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='?p=admin/subjects/subjects&amp;id={$this->ID}&amp;parentid={$this->ParentID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }

      return $String;
   }

   /**
    * Loads SubjectSource for Subject from the database
    *
    * @return boolean
    */
   public function dbLoadSubjectSource()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load SubjectSource: SubjectID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load SubjectSource: Subject ID must be numeric.");
         return false;
      }

      $this->SubjectSources = array();

      $query = "SELECT tblSubjects_SubjectSources . * FROM tblSubjects_SubjectSources JOIN tblSubjects_Subjects ON tblSubjects_Subjects.SubjectSourceID = tblSubjects_SubjectSources.ID WHERE tblSubjects_Subjects.ID = ? ";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);

      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         return true;
      }

      while($row = $result->fetchRow())
      {
         $this->SubjectSources[$row['ID']] = New SubjectSource($row);
      }

      $result->free();
      $prep->free();

      return true;
   }


   /**
    * @var integer
    * */
   public $ID = 0;
   /** @var string */
   public $Subject = '';
   /** @var integer */
   public $SubjectTypeID = 0;
   /** @var integer */
   public $SubjectSourceID = 0;
   /**
    * @var String
    */
   public $Identifier = '';
   /** @var integer */
   public $ParentID = 0;
   /**
    * @var SubjectType
    */
   public $SubjectType = NULL;
   /**
    * @var SubjectSource
    */
   public $SubjectSource = NULL;
   /**
    * @var Subject
    */
   public $Parent = NULL;
   /**
    *
    * @var string
    */
   public $Description = '';

}

$_ARCHON->mixClasses('Subject', 'Subjects_Subject');
?>