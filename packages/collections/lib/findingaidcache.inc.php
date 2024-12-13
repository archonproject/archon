<?php

class FindingAidCache extends AObject
{

   public static function getContent($CollectionID, $TemplateSet, $ReadPermissions, $RootContentID)
   {
      global $_ARCHON;

      if(!CONFIG_COLLECTIONS_ENABLE_FINDING_AID_CACHING)
      {
         return '';
      }

      $query = "SELECT TOP 1 Dirty,FindingAidText FROM tblCollections_FindingAidCache WHERE CollectionID = ? AND TemplateSet = ? AND ReadPermissions = ? AND RootContentID = ? ORDER BY id desc";


      $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'text', 'integer', 'integer'), MDB2_PREPARE_RESULT);

      $result = $prep->execute(array($CollectionID, $TemplateSet, $ReadPermissions, $RootContentID));
      if(pear_isError($result))
      {
         echo($query);
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      if($result->numRows() == 0)
      {
         return '';
      }
      $row = $result->fetchRow();

      if($row['Dirty'])
      {
         return '';
      }

      return $row['FindingAidText'];
   }

   public static function setContent($CollectionID, $TemplateSet, $ReadPermissions, $RootContentID, $Text)
   {
      global $_ARCHON;

      if(!CONFIG_COLLECTIONS_ENABLE_FINDING_AID_CACHING)
      {
         return false;
      }

      $query = "SELECT ID FROM tblCollections_FindingAidCache WHERE CollectionID = ? AND TemplateSet = ? AND ReadPermissions = ? AND RootContentID = ?";
      
      $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'text', 'integer', 'integer'), MDB2_PREPARE_RESULT);

      $result = $prep->execute(array($CollectionID, $TemplateSet, $ReadPermissions, $RootContentID));

      if(pear_isError($result))
      {
         echo($query);
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      if($result->numRows() == 0)
      {
         $query = "INSERT INTO tblCollections_FindingAidCache (CollectionID, TemplateSet, ReadPermissions, Dirty, RootContentID, FindingAidText) Values (?, ?, ?, ?, ?, ?)";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'text', 'integer', 'integer', 'integer', 'text'), MDB2_PREPARE_MANIP);
         if(pear_isError($prep))
         {
            trigger_error($prep->getMessage(), E_USER_ERROR);
            return false;
         }

         $affected = $prep->execute(array($CollectionID, $TemplateSet, $ReadPermissions, 0, $RootContentID, $Text));
         if(pear_isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
            return false;
         }

         $prep->free();
      }
      else
      {
         $query = "UPDATE tblCollections_FindingAidCache SET FindingAidText = ?, Dirty = 0 WHERE CollectionID = ? AND TemplateSet = ? AND ReadPermissions = ? AND RootContentID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('text', 'integer', 'text', 'integer', 'integer'), MDB2_PREPARE_MANIP);
         if(pear_isError($prep))
         {
            trigger_error($prep->getMessage(), E_USER_ERROR);
            return false;
         }

         $affected = $prep->execute(array($Text, $CollectionID, $TemplateSet, $ReadPermissions, $RootContentID));
         if(pear_isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
            return false;
         }

         $prep->free();
      }

      return true;
   }

   public static function setDirty($CollectionIDs)
   {
      global $_ARCHON;

      if(!is_array($CollectionIDs))
      {
         $CollectionIDs = array($CollectionIDs);
      }

      $question_marks = array_fill(0, count($CollectionIDs), '?');
      $types = array_fill(0, count($CollectionIDs) + 1, 'integer');

      $query = "UPDATE tblCollections_FindingAidCache SET Dirty = ? WHERE CollectionID IN (" . implode(', ', $question_marks) . ")";
      $prep = $_ARCHON->mdb2->prepare($query, $types, MDB2_PREPARE_MANIP);
      if(pear_isError($prep))
      {
         trigger_error($prep->getMessage(), E_USER_ERROR);
         return false;
      }

      $affected = $prep->execute(array_merge(array(1), $CollectionIDs));
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
         return false;
      }

      $prep->free();
   }

   public static function removeContent($CollectionID)
   {
      global $_ARCHON;

      $query = "DELETE FROM tblCollections_FindingAidCache WHERE CollectionID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_MANIP);
      if(pear_isError($prep))
      {
         trigger_error($prep->getMessage(), E_USER_ERROR);
         return false;
      }

      $affected = $prep->execute(array($CollectionID));
      if(pear_isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
         return false;
      }

      $prep->free();
   }

}

?>
