<?php
/**
 * Control Card template for "default" templateset
 *
 * The variable:
 *
 *
 *  $objCollection
 *
 * is an instance of a Collection object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Collection class definition in lib/collection.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Chris Rishel, Chris Prom, Paul Sorensen
 */
isset($_ARCHON) or die();

$repositoryid = $objCollection->RepositoryID;

echo("<h1 id='titleheader'>" . $_ARCHON->PublicInterface->Title . "</h1>\n");
//var_dump($objCollection);
//return;
?>

<div id='ccardleft'>        <!--begin div ccardleft -->
   <div id="ccardpublic" class='mdround'>  <!-- begin div ccardcontents -->
      <?php
      if($objCollection->Title)
      {
         ?>
         <div class='ccardcontent'><span class='ccardlabel'>Title:</span> <?php echo($objCollection->toString()); ?></div>
         <?php
      }

      if($objCollection->Classification)
      {
         ?>
         <div class='ccardcontent'><span class='ccardlabel'>ID:</span> <?php echo($objCollection->Classification->toString(LINK_NONE, true, false, true, false)); ?>/<?php echo($objCollection->getString('CollectionIdentifier')); ?></div>
         <?php
      }

      if($objCollection->Extent)
      {
         ?>
         <div class='ccardcontent'><span class='ccardlabel'>Extent:</span> <?php echo(preg_replace('/\.(\d)0/', ".$1", $objCollection->getString('Extent'))) . " "; ?><?php echo( is_null($objCollection->ExtentUnit)? '' : $objCollection->ExtentUnit->toString()); ?>
         </div>
   <?php
}

if($objCollection->AltExtentStatement)
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionAltExtent'); return false;"><img id='CollectionAltExtentImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />
   <?php
   echo ("More Extent Information");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionAltExtentResults'>
   <?php echo($objCollection->AltExtentStatement); ?>
            </div>
         </div>
   <?php
}

if($objCollection->PredominantDates)
{
   ?>
         <div class='ccardcontent'><span class='ccardlabel'>Predominant Dates:</span> <?php echo($objCollection->PredominantDates); ?></div>
         <?php
      }

      if($objCollection->Arrangement)
      {
         ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionArrangement'); return false;"><img id='CollectionArrangementImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />
   <?php
   echo ("Arrangement");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionArrangementResults'>
   <?php echo($objCollection->getString('Arrangement')); ?>
            </div>
         </div>
   <?php
}

if($objCollection->Abstract)
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionAbstract'); return false;"><img id='CollectionAbstractImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />
   <?php
   echo ("Abstract");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionAbstractResults'>
   <?php echo($objCollection->getString('Abstract')); ?>
            </div>
         </div>
   <?php
}

if(!empty($objCollection->Creators))
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionCreators'); return false;"><img id='CollectionCreatorsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />

   <?php
   echo ("Creators");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionCreatorsResults'>
   <?php echo($_ARCHON->createStringFromCreatorArray($objCollection->Creators, '<br/>', LINK_TOTAL, TRUE)); ?>
            </div>
         </div>
   <?php
}

if($objCollection->BiogHist)
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionBiogHist'); return false;"><img id='CollectionBiogHistImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />
   <?php
   echo ("Administrative/Biographical History");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionBiogHistResults'>
   <?php
   echo($objCollection->getString('BiogHist'));
   if($objCollection->BiogHistAuthor)
   {
      echo(" <span class='bold'>Author:</span> " . $objCollection->getString('BiogHistAuthor'));
   }
   ?>
            </div>
         </div>
   <?php
}


if(!empty($objCollection->Subjects))
{
   $GenreSubjectTypeID = $_ARCHON->getSubjectTypeIDFromString('Genre/Form of Material');

   foreach($objCollection->Subjects as $objSubject)
   {
      if($objSubject->SubjectTypeID == $GenreSubjectTypeID)
      {
         $arrGenres[$objSubject->ID] = $objSubject;
      }
      else
      {
         $arrSubjects[$objSubject->ID] = $objSubject;
      }
   }

   if(!empty($arrSubjects))
   {
      ?>
            <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('subjects'); return false;"><img id='subjectsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Subjects</a> <span style='font-size:80%'>(links to similar collections)</span></span><br/>
               <div class='ccardshowlist' style='display: none' id='subjectsResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrSubjects, "<br/>\n", LINK_TOTAL)); ?></div>
            </div>
      <?php
   }
   if(!empty($arrGenres))
   {
      ?>
            <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('genres'); return false;"><img id='genresImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Forms of Material</a> <span style='font-size:80%'>(links to similar genres)</span></span><br/>
               <div class='ccardshowlist' style='display: none' id='genresResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrGenres, "<br/>\n", LINK_TOTAL)); ?></div>
            </div>
      <?php
   }
}

if(!empty($objCollection->Languages))
{
   ?>
         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('langs'); return false;"><img id='langsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Languages of Materials</a></span><br/>
            <div class='ccardshowlist' style='display: none' id='langsResults'><?php echo($_ARCHON->createStringFromLanguageArray($objCollection->Languages, "<br/>\n", LINK_TOTAL)); ?></div>
         </div>
   <?php
}

if(!empty($objCollection->AcquisitionDate) || !empty($objCollection->AccrualInfo) || !empty($objCollection->AccessRestrictions) || !empty($objCollection->UseRestrictions) || !empty($objCollection->PhysicalAccessNote) || !empty($objCollection->TechnicalAccessNote) || !empty($objCollection->AcquisitionSource) || !empty($objCollection->AcquisitionMethod) || !empty($objCollection->AppraisalInformation) || !empty($objCollection->OrigCopiesNote) || !empty($objCollection->OrigCopiesURL) || !empty($objCollection->RelatedMaterials) || !empty($objCollection->RelatedMaterialsURL) || !empty($objCollection->RelatedPublications) || !empty($objCollection->PreferredCitation) || !empty($objCollection->ProcessingInfo) || !empty($objCollection->RevisionHistory) || !empty($objCollection->MaterialType))
//admin info exists
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('otherinformation'); return false;"><img id='otherinformationImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Administrative Information</a></span><br/>
            <div class='ccardshowlist' style='display:none' id='otherinformationResults'>

   <?php
   if(!empty($objCollection->Repository) && ($objCollection->Repository != $_ARCHON->Repository))
   {
      ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Repository:</span> <?php echo$objCollection->Repository->getString('Name'); ?></div>
                  <?php
               }

               if($objCollection->AcquisitionDate || $objCollection->AccrualInfo)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Acquired:</span>
                  <?php
                  if($objCollection->AcquisitionDate)
                  {

                     if($objCollection->AcquisitionDateMonth <> "00")
                     {
                        echo($objCollection->AcquisitionDateMonth . '/');
                     }
                     if($objCollection->AcquisitionDateDay <> "00")
                     {
                        echo($objCollection->AcquisitionDateDay . '/');
                     }
                     echo ($objCollection->AcquisitionDateYear . ".  ");
                  }
                  if($objCollection->AccrualInfo)
                  {
                     echo($objCollection->getString('AccrualInfo'));
                  }
                  ?>
                  </div>
                     <?php
                  }



                  if($objCollection->AccessRestrictions)
                  {
                     ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Restrictions:</span> <?php echo($objCollection->getString('AccessRestrictions')); ?></div>
                  <?php
               }

               if($objCollection->UseRestrictions)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Rights:</span> <?php echo($objCollection->getString('UseRestrictions')); ?></div>
                  <?php
               }

               if($objCollection->PhysicalAccess)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Access Notes: </span><?php echo($objCollection->getString('PhysicalAccess')); ?></div>
                  <?php
               }

               if($objCollection->TechnicalAccess)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Technical Notes: </span><?php echo($objCollection->getString('TechnicalAccess')); ?></div>

                  <?php
               }

               if($objCollection->AcquisitionSource || $objCollection->AcquisitionMethod)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Acquisition Note: </span>
                  <?php
                  if($objCollection->AcquisitionSource)
                  {
                     echo("&nbsp;<em>Source:</em> " . $objCollection->getString('AcquisitionSource') . ".<br/>");
                  }
                  if($objCollection->AcquisitionMethod)
                  {
                     echo($objCollection->getString('AcquisitionMethod'));
                  }
                  ?>
                  </div>
                     <?php
                  }

                  if($objCollection->AppraisalInformation)
                  {
                     ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Appraisal Notes:</span> <?php echo($objCollection->getString('AppraisalInformation')); ?></div>
                  <?php
               }

               if($objCollection->OrigCopiesNote || $objCollection->OrigCopiesURL)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Other Formats:</span>
                  <?php
                  if($objCollection->OrigCopiesNote)
                  {
                     echo($objCollection->getString('OrigCopiesNote'));
                  }
                  if($objCollection->OrigCopiesURL)
                  {
                     echo("<br/>For more information please see <a href='{$objCollection->getString('OrigCopiesURL')}'>{$objCollection->getString('OrigCopiesURL')}</a>.");
                  }
                  ?>
                  </div>
                     <?php
                  }

                  if($objCollection->RelatedMaterials || $objCollection->RelatedMaterialsURL)
                  {
                     ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Related Materials:</span>
                  <?php
                  if($objCollection->RelatedMaterials)
                  {
                     echo($objCollection->getString('RelatedMaterials'));
                  }
                  if($objCollection->RelatedMaterialsURL)
                  {
                     echo("<br/>For more information please see <a href='{$objCollection->getString('RelatedMaterialsURL')}'>{$objCollection->getString('RelatedMaterialsURL')}</a>.");
                  }
                  ?>
                  </div>
                     <?php
                  }


                  if($objCollection->RelatedPublications)
                  {
                     ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Related Publications:</span> <?php echo($objCollection->getString('RelatedPublications')); ?></div>
                  <?php
               }


               if($objCollection->PreferredCitation)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Preferred Citation:</span> <?php echo($objCollection->getString('PreferredCitation')); ?></div>
                  <?php
               }

               if($objCollection->ProcessingInformation)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Processing Note</span>: <?php echo($objCollection->getString('ProcessingInformation')); ?></div>
                  <?php
               }


               if($objCollection->RevisionHistory)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Finding Aid Revisions:</span> <?php echo($objCollection->getString('RevisionHistory')); ?></div>
                  <?php
               }

               if($objCollection->MaterialType)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Collection Material Type:</span> <?php echo($objCollection->MaterialType->toString()); ?></div>
                  <?php
               }


               if(!empty($objCollection->OtherNote))
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Other Note: </span><?php echo($objCollection->getString('OtherNote')); ?></div>
                  <?php
               }




               echo("</div>");  // ending ccardshowlist
               echo("</div>");   // ending admininfo content
            }

            if(!empty($arrDisplayAccessions))
            {
               ?>
               <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('accessions'); return false;"><img id='accessionsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Unprocessed Materials<?php
            if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
            {
               echo (" and Processed Accessions");
            }
               ?></a></span><br/>

                        <?php
                        echo ("<div class='ccardshowlist' style='display: none' id='accessionsResults'>");

                        foreach($arrDisplayAccessions as $objAccession)
                        {
                           echo($objAccession->toString(LINK_EACH) . "<br/>\n");
                           $ResultCount++;
                        }
                        ?>

               </div>
            </div>
                  <?php
               }

               if($objCollection->Books)
               {
                  ?>
            <div class='ccardcontent'><span class='bold'><a href='#' onclick="toggleDisplay('LinkedBooks'); return false;"><img id='LinkedBooksImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Books </a></span><br/>
               <div class='ccardshowlist' style='display: none' id='LinkedBooksResults'><?php echo($_ARCHON->createStringFromBookArray($objCollection->Books, "<br/>\n", LINK_TOTAL)); ?></div>
            </div>

<?php
            }




            echo("</div>");	//end ccardpublic



         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
         {
         ?>
         <div id='ccardstaff' class='mdround'>
            <div class='ccardstafflabel'>Staff Information</div>
            <div class='ccardcontents'><br/>
               <span class='ccardlabel'>Storage Locations:</span>
   <?php
   if(!empty($objCollection->LocationEntries))
   {
      ?>
                  <table id='locationtable' border='1'>
                     <tr>
                        <th>Content</th>
                        <th>Location</th>
                        <th>Range</th>
                        <th>Section</th>
                        <th>Shelf</th>
                        <th>Extent</th>
                     </tr>
                     <tr>
                        <td>
      <?php echo($_ARCHON->createStringFromLocationEntryArray($objCollection->LocationEntries, '&nbsp;</td></tr><tr><td>', LINK_EACH, false, '&nbsp;</td><td>')); ?>
                        </td>
                     </tr>
                  </table>
      <?php
   }
   else
   {
      ?>
                  No locations are listed for this record series.
      <?php
   }
   ?>
            </div>

            <div class="ccardcontents"><br/><span class='ccardlabel'>Show this record as:</span><br/><br/>
               <a href='?p=collections/ead&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=ead&amp;disabletheme=1&amp;output=<?php echo(formatFileName($objCollection->getString('SortTitle', 0, false, false))); ?>'>EAD</a><br/>
               <a href='?p=collections/marc&amp;id=<?php echo($objCollection->ID); ?>'>MARC</a><br/>
               <a href='?p=collections/controlcard&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=kardexcontrolcard&amp;disabletheme=1'>5 by 8 Kardex</a><br/>
               <a href='?p=collections/controlcard&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=draftcontrolcard&amp;disabletheme=1'>Review copy/draft</a>
            </div>
         </div>

   <?php
}

echo("</div>"); //end ccardleft

echo ("<div id='ccardprintcontact' class='smround'> <a href='?p=collections/controlcard&amp;id=" . $objCollection->ID . "&amp;templateset=print&amp;disabletheme=1'><img src='" . $_ARCHON->PublicInterface->ImagePath . "/printer.png' alt='Printer-friendly' /></a> <a href='?p=collections/controlcard&amp;id=" . $objCollection->ID . "&amp;templateset=print&amp;disabletheme=1'>Printer-friendly</a> | <a href='?p=collections/research&amp;f=email&amp;repositoryid=$repositoryid&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'><img src='" . $_ARCHON->PublicInterface->ImagePath . "/email.png' alt='Email Us' /> </a><a href='?p=collections/research&amp;f=email&amp;repositoryid=$repositoryid&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'>Email Us</a></div>");

if($objCollection->Scope || !empty($objCollection->Content) || ($objCollection->DigitalContent || $containsImages) || !empty($objCollection->OtherURL))
{
   ?>
         <div id="ccardscope" class="mdround">
         <?php
         if($objCollection->Scope)
         {
            ?>
               <div class='ccardcontent expandable' style='padding-left:.2em'><span class='ccardlabel'>Scope and Contents:</span> <?php echo($objCollection->getString('Scope')); ?></div>
            <?php
         }
         if($objCollection->DigitalContent || $containsImages)
         {
            ?>

               <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('digitalcontent'); return false;"><img id='digitalcontentImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> On-line Images/Records</a></span><br/>
                  <div class='ccardshowlist' style="display: none;" id="digitalcontentResults">
               <?php
               if($containsImages)
               {
                  echo("<span class='bold'><a href='index.php?p=digitallibrary/thumbnails&amp;collectionid={$objCollection->ID}'>Images</a></span> (browse thumbnails)<br/>\n\n");
               }
               if($objCollection->DigitalContent)
               {
                  echo("<br/><span class='bold'>Documents and Files:</span><br/>&nbsp;" . $_ARCHON->createStringFromDigitalContentArray($objCollection->DigitalContent, "<br/>\n&nbsp;", LINK_TOTAL));
               }
               ?>
                  </div>
               </div>
                     <?php
                  }


                  if(!empty($objCollection->Content))
                  {
                     ?>
               <div class='ccardcontent'><span class='ccardlabel'><a href='?p=collections/findingaid&amp;id=<?php echo($objCollection->ID); ?>&amp;q=<?php echo($_ARCHON->QueryStringURL); ?>'>Detailed Description</a></span><br/>
      <?php
      $DisableTheme = $_ARCHON->PublicInterface->DisableTheme;
      $_ARCHON->PublicInterface->DisableTheme = true;

      foreach($objCollection->Content as $ID => $objContent)
      {
         if(!$objContent->ParentID)
         {
            if($objContent->enabled())
            {
               echo("<span class='ccardserieslist'><a href='?p=collections/findingaid&amp;id=$objCollection->ID&amp;q=$_ARCHON->QueryStringURL&amp;rootcontentid=$ID#id$ID'>" . $objContent->toString() . "</a></span><br/>\n");
            }
            else
            {

               $objInfoRestrictedPhrase = Phrase::getPhrase('informationrestricted', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
               $strInfoRestricted = $objInfoRestrictedPhrase ? $objInfoRestrictedPhrase->getPhraseValue(ENCODE_HTML) : 'Information restricted, please contact us for additional information.';
               echo("<span class='ccardserieslist'>{$strInfoRestricted}</span><br/>\n");
            }
         }
      }

      $_ARCHON->PublicInterface->DisableTheme = $DisableTheme;
      ?>
               </div>
                  <?php
               }


               if(!empty($objCollection->OtherURL))
               {
                  $onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GACollectionsURL) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GACollectionsURL}\");'" : "";
                  ?>
               <div class='ccardcontent'><span class='ccardlabel'>Other URL:</span> <a href='<?php echo($objCollection->getString('OtherURL')); ?>' <?php echo($onclick); ?>><?php echo($objCollection->getString('OtherURL')); ?></a></div>
                  <?php
               }
               ?>
         </div>
            <?php
         }
         ?>
