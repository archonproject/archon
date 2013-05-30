<?php

/**
 * Output file for finding aids
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

@set_time_limit(60);

$in_RootContentID = $_REQUEST['rootcontentid'] ? $_REQUEST['rootcontentid'] : 0;

// Load the collection and all of its items (pre-processed)
// into one object
$objCollection = New Collection($_REQUEST['id']);


$objCollection->dbLoadAll(LOADCONTENT_NONE);


if(!$objCollection->enabled())
{
   $_ARCHON->AdministrativeInterface = true;
   $_ARCHON->declareError("Could not access Collection \"" . $objCollection->toString() . "\": Public access disallowed.");
   $_ARCHON->AdministrativeInterface = false;
}

if(!$objCollection->enabled())
{
   $readPermissions = false;
}
else
{
   $readPermissions = false;


   if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, READ)
           || ($_ARCHON->Security->userHasAdministrativeAccess() && !CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS)
           || (CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS && $_ARCHON->Security->verifyRepositoryPermissions($objCollection->RepositoryID)))
   {
      $readPermissions = true;
   }
}



if($objCollection->TemplateSet && $_REQUEST['templateset'] != 'EAD')
{
   $_ARCHON->PublicInterface->TemplateSet = $objCollection->TemplateSet;
   $_ARCHON->PublicInterface->Templates = $_ARCHON->loadTemplates($_ARCHON->PublicInterface->TemplateSet);
}

if(!$_ARCHON->PublicInterface->Templates['collections']['Collection'])
{
   $_ARCHON->declareError("Could not display FindingAid: Collection template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}

$_ARCHON->PublicInterface->Title = $objCollection->toString();

if($objCollection->Classification)
{
   $arrClassifications = $_ARCHON->traverseClassification($objCollection->ClassificationID);

   foreach($arrClassifications as $objClassification)
   {
      $_ARCHON->PublicInterface->addNavigation($objClassification->getString('Title', 30), "?p=collections/classifications&amp;id=$objClassification->ID");
   }
}



$objFindingAidPhrase = Phrase::getPhrase('findingaid_findingaid', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strFindingAid = $objFindingAidPhrase ? $objFindingAidPhrase->getPhraseValue(ENCODE_HTML) : 'Finding Aid';

$_ARCHON->PublicInterface->addNavigation($objCollection->getString('Title', 30), "?p=collections/controlcard&amp;id=$objCollection->ID");
$_ARCHON->PublicInterface->addNavigation($strFindingAid, "?p={$_REQUEST['p']}&amp;id=$objCollection->ID");

require_once("header.inc.php");


if(!$_ARCHON->Error)
{
   // Stop automatic highlighting.
   if(!$_ARCHON->PublicInterface->DisableTheme)
   {
      ob_end_flush();
      flush();
   }

   if(!$_ARCHON->Security->userHasAdministrativeAccess() && $_REQUEST['disabletheme'] !== '1')
   {
      ?>
      <script type="text/javascript">
         $(function(){
            /* <![CDATA[ */
            updateResearchCartLinks();
            /* ]]> */
         });
      </script>
      <?php

   }

   $output = FindingAidCache::getContent($objCollection->ID, $_ARCHON->PublicInterface->TemplateSet, $readPermissions, $in_RootContentID);
   if($output != '')
   {
      $count = 0;
      if(!empty($arrWords))
      {
         foreach($arrWords as $word)
         {
            if($word && $word{0} != "-")
            {
               $output = preg_replace("/(\A|\>)([^\<]*[^\w^=^\<^\+^\/]|)(" . preg_quote($word, '/') . ")(|[^\w^=^\>\+][^\>]*)(\<|\z)/ui", "$1$2<span class='highlight$count bold'>$3</span>$4$5", $output);
               $count++;
            }
         }
      }

      echo($output);
      flush();
   }
   else
   {

      $objCollection->ignoreCart = true;

      $objCollection->dbLoadRootContent();
      $arrRootContent = $objCollection->Content;

      // Turn on highlighting buffer
      ob_start();

      // Process the collection template.
      eval($_ARCHON->PublicInterface->Templates['collections']['Collection']);

      $output = ob_get_clean();

      $arrWords = $_ARCHON->createSearchWordArray($_ARCHON->QueryString);

      if($output)
      {
         $highlighted_output = $output;
         $count = 0;
         if(!empty($arrWords))
         {
            foreach($arrWords as $word)
            {
               if($word && $word{0} != "-")
               {
                  $highlighted_output = preg_replace("/(\A|\>)([^\<]*[^\w^=^\<^\+^\/]|)(" . preg_quote($word, '/') . ")(|[^\w^=^\>\+][^\>]*)(\<|\z)/ui", "$1$2<span class='highlight$count bold'>$3</span>$4$5", $highlighted_output);
                  $count++;
               }
            }
         }
      }


      // Break the collection template where %Items% occurs
      // into two strings, containing the template data before
      // and after the processed items should be inserted.
      list($outputnow, $outputlater) = explode("#CONTENT#", $highlighted_output);
      list($output_before, $output_after) = explode("#CONTENT#", $output);
      echo($outputnow);
      flush();



      // If the collection contains items, process and display them.
      // Note: $objCollection->Content was created (and ordered) by the Collection
      // constructor and consists of a one-dimensional array of items,
      // with the ID field from the tblCollections_Collections table acting as the array keys.

      if(!empty($arrRootContent))
      {



         ob_start();


         $objContent = reset($arrRootContent);
         $rootContentIDsSet = $objCollection->rootContentIDsSet();

         if(!$rootContentIDsSet)
         {
            $objCollection->getContentArray(LOADCONTENT_ALL, ($_REQUEST['templateset'] == 'EAD'));
         }

         do
         {
            if(!$in_RootContentID || $objContent->ID == $in_RootContentID)
            {
               if($rootContentIDsSet)
               {
                  $objCollection->getContentArray($objContent->ID, ($_REQUEST['templateset'] == 'EAD'));
               }
               // Process and display the current item.
               findingaid_DisplayContent($objContent->ID);
            }

            // Advance the array pointer to the next item
            $objContent = next($arrRootContent);

            // If there are no more items, we are done.
            // If the item is contained by another item, then the item
            // has already been displayed (since root-level items occur
            // first in the array), we are also done.
         }
         while($objContent && !$objContent->ParentID);

         $content = ob_get_clean();

         FindingAidCache::setContent($objCollection->ID, $_ARCHON->PublicInterface->TemplateSet, $readPermissions, $in_RootContentID, $output_before . $content . $output_after);


         if($content)
         {
            $count = 0;
            if(!empty($arrWords))
            {
               foreach($arrWords as $word)
               {
                  if($word && $word{0} != "-")
                  {
                     $content = preg_replace("/(\A|\>)([^\<]*[^\w^=^\<^\+^\/]|)(" . preg_quote($word, '/') . ")(|[^\w^=^\>\+][^\>]*)(\<|\z)/ui", "$1$2<span class='highlight$count bold'>$3</span>$4$5", $content);
                     $count++;
                  }
               }
            }
         }
         echo($content);
         flush();
      }



      echo($outputlater);
      flush();
   }
   // END
}
else
{
   echo($_ARCHON->Error);
}

require_once("footer.inc.php");

// ***********************************************
// * findingaid_DisplayContent()                 *
// ***********************************************
//
//   - Purpose: Prepares an item for processing.
//
//   - Incoming Variables:
//
//      - $id (Integer) (REQUIRED):
//           Contains the ID value from
//           tblCollections_Collections for the item being
//           displayed.
//
// ***********************************************
function findingaid_DisplayContent($id)
{
   // Put our collection and templates objects into the global scope.
   // $objCollection was initiated by the Collection constructor called by
   // this script.
   // The templates object was initiated by another file loaded by includes.inc.php
   global $_ARCHON, $objCollection, $arrWords, $readPermissions;

   $Content = $objCollection->Content[$id];

   if($_ARCHON->PublicInterface->Templates['collections'][$Content['LevelContainer']])
   {
      $ItemType = $Content['LevelContainer'];
   }
   else
   {
      $ItemType = "DefaultContent";
   }

   if(!$_ARCHON->PublicInterface->Templates['collections'][$ItemType])
   {
      $_ARCHON->declareError("Could not display $ItemType: $ItemType template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
      return;
   }

   // Process and send the first portion of the item template.
   $enabled = $readPermissions || $Content['Enabled'];

   ob_start();
   eval($_ARCHON->PublicInterface->Templates['collections'][$ItemType]);

   $output = ob_get_clean();

   // Break the item template where %Items% occurs
   // into two strings, containing the template data before
   // and after the processed items should be inserted.z
   list($outputnow, $outputlater) = explode("#CONTENT#", $output);

   if(function_exists("template_ContentPreProcess"))
   {
      $outputnow = template_ContentPreProcess($outputnow, $Content);
   }

   echo($outputnow);

   flush();


   if($enabled)
   {
      // Process and display all the children recursively.
      if(!empty($Content['Content']))
      {
         foreach($Content['Content'] as $ID => $Child)
         {
            findingaid_DisplayContent($ID);
         }

         /* //For deallocing as you go.

           $objChild = reset($objContent->Content);

           do
           {
           // Process and display the current item.
           findingaid_DisplayContent($objChild->ID);
           $objContent->Content[$objChild->ID] = NULL;
           $objCollection->Content[$objChild->ID] = NULL;

           // Advance the array pointer to the next item
           $objChild = next($objContent->Content);
           } while($objChild); */
      }
   }

   if(function_exists("template_ContentPostProcess"))
   {
      $outputlater = template_ContentPostProcess($outputlater, $Content);
   }

   echo($outputlater);
   flush();
}
?>
