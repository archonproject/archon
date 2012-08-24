<?php
/**
 * DigitalContent template
 *
 * The variable:
 *
 *  $objDigitalContent
 *
 * is an instance of a DigitalContent object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the DigitalContent class definition in packages/digitallibrary/lib/digitalcontent.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in packages/core/lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Chris Rishel, Chris Prom
 */
isset($_ARCHON) or die();


$strPageTitle = strip_tags($_ARCHON->PublicInterface->Title);

$parts = explode(' | ', $strPageTitle);
$heading = $parts[0];

echo("<h2>$heading</h2>\n");



if(!empty($objDigitalContent->Files))
{
   $firstFile = true;

   echo("<div id='digcontentwrapper'><div id='digcontentfiles' class='mdround'>\n");
   foreach($objDigitalContent->Files as $objFile)
   {
      if(!$firstFile)
      {
         echo("<hr/>");
      }

      $firstFile = false;

      $PreviewAccess = $objFile->verifyLoadPermissions(DIGITALLIBRARY_FILE_PREVIEWSHORT);

      $FullAccess = $objFile->verifyLoadPermissions(DIGITALLIBRARY_FILE_FULL);

      echo("<div class='digcontenttitlebox mdround'>");

      if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) && $objFile->DefaultAccessLevel != DIGITALLIBRARY_ACCESSLEVEL_FULL)
      {
         if($objFile->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY)
         {
            echo("<span class='bold'>NOTE</span>: The public cannot download this file.<br/>");
         }
         elseif($objFile->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_NONE)
         {
            echo("<span class='bold'>NOTE</span>: The public cannot view previews of or download this file.<br/>");
         }
      }

      if(!$PreviewAccess)
      {
         echo("<span class='digcontentfiletitle'>No preview for this item is publicly available. Contact the archives for information about accessing this item.<br/><br/>");
         echo("{$objFile->getString('Title')} (" . ($objFile->FileType ? $objFile->FileType->getString('FileType') : '') . ", " . formatsize($objFile->Size) . ")</span><br/>");
      }
      else
      {
         if(encoding_substr_count($objFile->FileType->FileExtensions, '.pdf') && file_exists("{$_ARCHON->PublicInterface->ImagePath}/pdficon_large.gif"))
         {
            $onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GADigContentPrefix) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GADigContentPrefix}/pdf/DigitalContentID={$objDigitalContent->ID}/fileID={$objFile->ID}\");'" : "";
            echo("<img src='{$_ARCHON->PublicInterface->ImagePath}/pdficon_large.gif' alt='PDF icon' /><br/>");
         }
         elseif($objFile->FileType->MediaType->MediaType == 'Image')
         {
            $onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GADigContentPrefix) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GADigContentPrefix}/image/DigitalContentID={$objDigitalContent->ID}/fileID={$objFile->ID}\");'" : "";
            echo("<img class='digcontentfile' src='".$objFile->getFileURL(DIGITALLIBRARY_FILE_PREVIEWLONG)."' alt='{$objFile->getString('Title')}'/><br/>");
         }
         elseif($objFile->Filename)
         {
            preg_match("/.+?\/(.+)/u", $objFile->FileType->getString('ContentType'), $matches);
            $contenttype = $matches[1] ? $matches[1] : 'file';
            $onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GADigContentPrefix) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GADigContentPrefix}/{$contenttype}/DigitalContentID={$objDigitalContent->ID}/fileID={$objFile->ID}\");'" : "";
            echo("<script type='text/javascript'>embedFile($objFile->ID, '" . encode($objFile->FileType->MediaType->MediaType, ENCODE_JAVASCRIPT) . "', 'long');</script><br/>");
         }

         echo("<span class='digcontentfiletitle'>" . $objFile->getString('Title') . " (" . $objFile->FileType->getString('FileType') . ", " . formatsize($objFile->Size) . ")<br/>");

         if($FullAccess)
         {
            echo("<a href='?p=digitallibrary/getfile&amp;id=$objFile->ID' $onclick>Download Original File</a><br/>");
         }
         else
         {
            echo("<br/>Download of the full file is not publicly available.  Contact the archives for information about accessing this item.<br/>");
         }

         echo("</span>");
      }


      if($objDigitalContent->Repository->ResearchFunctionality & RESEARCH_DIGITALLIB)
      {
         echo ("<br/><span class='digcontentrequest'><a href='?p=digitallibrary/request&amp;id=" . $_REQUEST['id'] . "&amp;fileid=" . $objFile->ID . "&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'>Request hi-res copy</a></span>");
      }

      echo("<br/><br/></div>");
   }
   echo ("</div>\n");
}



echo("<div id='digcontentmetadata' class='mdround'>\n");

if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) && !$objDigitalContent->Browsable)
{
   echo("<span class='bold'>NOTE</span>: This metadata is <span class='bold'>NOT</span> searchable by the public.<br/>");
}

if($objDigitalContent->ContentURL && empty($objDigitalContent->Files))
{
?>&nbsp;  <!--forces IE to behave -->

   <div class='digcontentlabel'>Available:</div>
   <div class='digcontentdata' style='font-weight:bold'>
   <?php
   if($objDigitalContent->HyperlinkURL)
   {
      echo("<a href='{$objDigitalContent->getString('ContentURL')}'>{$objDigitalContent->getString('ContentURL')}</a>");
   }
   else
   {
      echo($objDigitalContent->getString('ContentURL'));
   }
   ?>
</div>
<?php
}

if($objDigitalContent->Title)
{
?>

   <div class='digcontentlabel'>Title:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->toString()); ?></div>
<?php
}


if($objDigitalContent->Date)
{
?>
   <div class='digcontentlabel'>Date:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->getString('Date')); ?></div>
<?php
}


if($objDigitalContent->Scope)
{
?>
   <div class='digcontentlabel'>Description:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->getString('Scope')); ?></div>

<?php
}

if($objDigitalContent->PhysicalDescription)
{
?>
   <div class='digcontentlabel'>Phys. Desc:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->getString('PhysicalDescription')); ?></div>
<?php
}

if($objDigitalContent->Identifier)
{
?>
   <div class='digcontentlabel'>ID:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->getString('Identifier')); ?></div>
<?php
}

if($objDigitalContent->Collection->Repository)
{
?>
   <div class='digcontentlabel'>Repository:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->Collection->Repository); ?></div>
<?php
}


if($objDigitalContent->Collection)
{
?>

   <div class='digcontentlabel'>Found in:</div>
   <div class='digcontentdata'><?php
   echo($objDigitalContent->Collection->toString(LINK_TOTAL));
   if($objDigitalContent->CollectionContent)
   {
      echo($_ARCHON->PublicInterface->Delimiter . $objDigitalContent->CollectionContent->toString(LINK_EACH, true, true, true, true, $_ARCHON->PublicInterface->Delimiter));
   }
?>
</div>
<?php
}

if($objDigitalContent->Creators && defined('PACKAGE_CREATORS'))
{
?>
   <div class='digcontentlabel'>Creators:</div>
   <div class='digcontentdata'><?php echo($_ARCHON->createStringFromCreatorArray($objDigitalContent->Creators, '<br/>', LINK_TOTAL)); ?></div>
<?php
}

if($objDigitalContent->Subjects && defined('PACKAGE_SUBJECTS'))
{
?>
   <div class='digcontentlabel'>Subjects:</div>
   <div class='digcontentdata'><?php echo($_ARCHON->createStringFromSubjectArray($objDigitalContent->Subjects, '<br/>', LINK_TOTAL)); ?></div>
<?php
}

if($objDigitalContent->Publisher)
{
?>
   <div class='digcontentlabel'>Publisher:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->getString('Publisher')); ?></div>
<?php
}

if($objDigitalContent->Contributor)
{
?>
   <div class='digcontentlabel'>Contributor:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->getString('Contributor')); ?></div>
<?php
}

if($objDigitalContent->RightsStatement)
{
?>
   <div class='digcontentlabel'>Rights:</div>
   <div class='digcontentdata'><?php echo($objDigitalContent->getString('RightsStatement')); ?></div>
<?php
}


if($objDigitalContent->Languages)
{
?>
   <div class='digcontentlabel'>Languages:</div>
   <div class='digcontentdata'><?php echo($_ARCHON->createStringFromLanguageArray($objDigitalContent->Languages, '&nbsp;', LINK_TOTAL)); ?></div>
<?php
}
else
{
?>
   <!--No languages are listed for this digital content.-->
<?php
}

if($objDigitalContent->ContentURL && !empty($objDigitalContent->Files))
{
?>
   <div class='digcontentlabel'>See Also:</div>
   <div class='digcontentdata'><?php
   if($objDigitalContent->HyperlinkURL)
   {
      echo("<a href='{$objDigitalContent->getString('ContentURL')}'>{$objDigitalContent->getString('ContentURL')}</a>");
   }
   else
   {
      echo($objDigitalContent->getString('ContentURL'));
   }
?>
</div>
<?php
}

echo('</div>');

if(!empty($objDigitalContent->Files))
{
   echo('</div>');
}
