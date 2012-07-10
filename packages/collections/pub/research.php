<?php
/**
 * Register a research account form
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

if($_ARCHON->Security->isAuthenticated() && $_ARCHON->Security->userHasAdministrativeAccess())
{
   header('Location: index.php?p=');
}


research_initialize();

function research_initialize()
{
   if($_REQUEST['f'] == 'cart')
   {
      research_cart();
   }
   elseif($_REQUEST['f'] == 'jsoncart')
   {
      research_jsoncart();
   }
   elseif($_REQUEST['f'] == 'email')
   {
      research_email();
   }
   elseif($_REQUEST['f'] == 'verify')
   {
      research_verify();
   }
   else
   {
      research_exec();
   }
}

function research_jsoncart()
{
   global $_ARCHON;

   $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();

   if(!empty($arrCart))
   {


      $arrCartOutput = array();
      $prevCollectionID = 0;


      foreach($arrCart->Collections as $CollectionID => $arrObjs)
      {
         foreach($arrObjs->Content as $ContentID => $obj)
         {
            if($obj instanceof Collection)
            {
               $objCollection = $obj;
               unset($objContent);
            }
            else
            {
               $objCollection = $obj->Collection;
               $objContent = $obj;
            }
            if($CollectionID != $prevCollectionID)
            {
               if(!isset($arrCartOutput[$CollectionID]))
               {
                  $arrCartOutput[$CollectionID] = array();
               }
            }

            if($objContent)
            {
               $arrCartOutput[$CollectionID][] = $objContent->ID;
            }

            $prevCollectionID = $CollectionID;
         }
      }
   }

   $arrResults = array();
   if(!empty($arrCartOutput))
   {
      foreach($arrCartOutput as $CollectionID => $arrContentIDs)
      {
         $arrResults[] = "{\"CollectionID\":$CollectionID,\"ContentIDs\":[" . implode(",", $arrContentIDs) . "]}";
      }
   }
   $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

   header('Content-type: application/json; charset=UTF-8');

   if($callback)
   {
      echo($callback . "(");
   }

   echo("{\"results\":[" . implode(",", $arrResults) . "]}");

   if($callback)
   {
      echo(");");
   }

   die();
}

function research_cart()
{
   global $_ARCHON;



   $objResearchTitlePhrase = Phrase::getPhrase('research_carttitle', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strResearchTitle = $objResearchTitlePhrase ? $objResearchTitlePhrase->getPhraseValue(ENCODE_HTML) : 'My Research Cart';

   $_ARCHON->PublicInterface->Title = $strResearchTitle;
   $_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title);

   $in_referer = $_REQUEST['referer'] ? $_REQUEST['referer'] : $_REQUEST['HTTP_REFERER'];

   if(!$_ARCHON->PublicInterface->Templates['collections']['Cart'])
   {
      $_ARCHON->declareError("Could not display Cart: Cart template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }

   include("header.inc.php");

   if($_ARCHON->Security->userHasAdministrativeAccess())
   {
      include("footer.inc.php");
      return;
   }
   ?>
   <form action="index.php" accept-charset="UTF-8" method="post">
      <div>
         <input type="hidden" name="f" value="verify" />
         <input type="hidden" name="p" value="collections/research" />
      </div>
      <?php
      if(!$_ARCHON->Error)
      {
         eval($_ARCHON->PublicInterface->Templates['collections']['Cart']);
      }
      ?>
   </form>
   <?php
   include("footer.inc.php");
}

function research_email()
{
   global $_ARCHON;



   $objEmailTitlePhrase = Phrase::getPhrase('research_email_title', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strEmailTitle = $objEmailTitlePhrase ? $objEmailTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Send Email';
   $objExtPhrase = Phrase::getPhrase('research_email_ext', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strExt = $objExtPhrase ? $objExtPhrase->getPhraseValue(ENCODE_HTML) : 'Ext';
   $objMarkedPhrase = Phrase::getPhrase('research_email_marked', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strMarked = $objMarkedPhrase ? $objMarkedPhrase->getPhraseValue(ENCODE_NONE) : 'Fields marked with an asterisk (<span style="color:red;">*</span>) are required.';
   $objFromNamePhrase = Phrase::getPhrase('research_email_fromname', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strFromName = $objFromNamePhrase ? $objFromNamePhrase->getPhraseValue(ENCODE_HTML) : 'Your Name';
   $objFromAddressPhrase = Phrase::getPhrase('research_email_fromaddress', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strFromAddress = $objFromAddressPhrase ? $objFromAddressPhrase->getPhraseValue(ENCODE_HTML) : 'Your Email Address';
   $objFromPhonePhrase = Phrase::getPhrase('research_email_fromphone', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strFromPhone = $objFromPhonePhrase ? $objFromPhonePhrase->getPhraseValue(ENCODE_HTML) : 'Your Phone Number';
   $objSubjectPhrase = Phrase::getPhrase('research_email_subject', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strSubject = $objSubjectPhrase ? $objSubjectPhrase->getPhraseValue(ENCODE_HTML) : 'Subject';
   $objMessagePhrase = Phrase::getPhrase('research_email_message', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strMessage = $objMessagePhrase ? $objMessagePhrase->getPhraseValue(ENCODE_HTML) : 'Your Message';
   $objSendEmailPhrase = Phrase::getPhrase('research_email_sendemail', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strSendEmail = $objSendEmailPhrase ? $objSendEmailPhrase->getPhraseValue(ENCODE_HTML) : 'Send Email';
   $objCartAppendPhrase = Phrase::getPhrase('research_email_cartappend', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strCartAppend = $objCartAppendPhrase ? $objCartAppendPhrase->getPhraseValue(ENCODE_HTML) : "Your 'cart' currently holds the following materials.  This list will be appended to your email message.";

   $_ARCHON->PublicInterface->Title = $strEmailTitle;
   $_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title);

   $in_referer = $_REQUEST['referer'] ? $_REQUEST['referer'] : $_REQUEST['HTTP_REFERER'];


   if(!$_ARCHON->PublicInterface->Templates['collections']['Email'])
   {
      $_ARCHON->declareError("Could not display Email: Email template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }


   include("header.inc.php");



   if($_ARCHON->Security->userHasAdministrativeAccess())
   {
      include("footer.inc.php");
      return;
   }


   $strName = $_ARCHON->Security->Session->User ? $_ARCHON->Security->Session->User->toString() : '';
   $strName = $_REQUEST['fromname'] ? encode($_REQUEST['fromname'], ENCODE_HTML) : $strName;
   //$strName = encode($strName, ENCODE_HTML);

   $strUserFrom = isset($_ARCHON->Security->Session->User) ? $_ARCHON->Security->Session->User->getString('Email') : '';
   $strFrom = $_REQUEST['fromaddress'] ? encode($_REQUEST['fromaddress'], ENCODE_HTML) : $strUserFrom;
   //$strFrom = encode($strFrom, ENCODE_HTML);

   $strPhone = isset($_ARCHON->Security->Session->User) ? $_ARCHON->Security->Session->User->getString('Phone') : '';
   if($_ARCHON->Security->Session->User->PhoneExtension)
   {
      $strPhone .= " $strExt: " . $_ARCHON->Security->Session->User->getString('PhoneExtension');
   }
   $strPhone = $_REQUEST['fromphone'] ? encode($_REQUEST['fromphone'], ENCODE_HTML) : $strPhone;
   //$strPhone = encode($strPhone, ENCODE_HTML);
   ?>
   <form action="index.php" accept-charset="UTF-8" method="post">
      <div>
         <input type="hidden" name="f" value="sendemails" />
         <input type="hidden" name="p" value="collections/research" />
         <input type="hidden" name="referer" value="<?php echo($in_referer); ?>" />
         <input type="hidden" name="query_string" value="<?php echo($_SERVER['QUERY_STRING']); ?>" />

      </div>

      <?php
      if(!$_ARCHON->Error)
      {
         eval($_ARCHON->PublicInterface->Templates['collections']['Email']);
      }
      ?>
   </form>    
   <?php
   include('footer.inc.php');
}

function research_verify()
{
   global $_ARCHON;



   $objVerifyTitlePhrase = Phrase::getPhrase('research_verify_title', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strVerifyTitle = $objVerifyTitlePhrase ? $objVerifyTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Verify Research Appointment';
   $objVerifyNavPhrase = Phrase::getPhrase('research_verify_nav', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strVerifyNav = $objVerifyNavPhrase ? $objVerifyNavPhrase->getPhraseValue(ENCODE_HTML) : 'My Research Request Cart';

   $ArrivalTimestamp = strtotime($_REQUEST['arrivaldatestring']);
   $DepartureTimestamp = strtotime($_REQUEST['departuredatestring']);
   $RepositoryID = $_REQUEST['repositoryid'];

   if(!$_ARCHON->Security->isAuthenticated())
   {
      $_ARCHON->declareError("Could not store appointment: You must be logged in to make an appointment.");
   }
   elseif($_ARCHON->Security->userHasAdministrativeAccess())
   {
      $_ARCHON->declareError("Could not store appointment: Only researchers may make an appointment.");
   }
   elseif(!$ArrivalTimestamp)
   {
      $_ARCHON->declareError("Could not store appointment: Unable to parse ArrivalTime.  Please follow the example format.");
   }
   elseif($_REQUEST['departuredatestring'] && !$DepartureTimestamp)
   {
      $_ARCHON->declareError("Could not store appointment: Unable to parse DepartureTime.  Please follow the example format.");
   }
   elseif($ArrivalTimestamp < time())
   {
      $_ARCHON->declareError("Could not store appointment: ArrivalTime cannot be before the current time.");
   }
   elseif($DepartureTimestamp && ($DepartureTimestamp < $ArrivalTimestamp))
   {
      $_ARCHON->declareError("Could not store appointment: DepartureTime cannot be before the arrival time.");
   }
   elseif(!$RepositoryID)
   {
      $_ARCHON->declareError("Could not store appointment: Repository must be defined.");
   }

   if($_ARCHON->Error)
   {
      $_ARCHON->PublicInterface->Header->Message = $_ARCHON->clearError();
      $_REQUEST['f'] = 'cart';
      research_cart();
   }

   $_ARCHON->PublicInterface->Title = $strVerifyTitle;
   $_ARCHON->PublicInterface->addNavigation($strVerifyNav, "?p=collections/research&f=cart");
   $_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title);

   if($_REQUEST['appointmentpurposeid'])
   {
      $objAppointmentPurpose = New ResearchAppointmentPurpose($_REQUEST['appointmentpurposeid']);
      $objAppointmentPurpose->dbLoad();
   }

   if(!$_ARCHON->PublicInterface->Templates['collections']['Verify'])
   {
      $_ARCHON->declareError("Could not display Verification: Verify template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }

   if($_ARCHON->Error)
   {
      research_cart();
      return;
   }

   include("header.inc.php");
   ?>
   <form action="index.php" accept-charset="UTF-8" method="GET">
      <input type="hidden" name=f value="makeappointment" />
      <input type="hidden" name="p" value="collections/research" />
      <input type="hidden" id="RepositoryIDField" name="RepositoryID" value="<?php echo($RepositoryID); ?>" />
   <?php eval($_ARCHON->PublicInterface->Templates['collections']['Verify']); ?>
   </form>
   <?php
   include("footer.inc.php");
}

function research_displaycart()
{
   global $_ARCHON;

   $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();

   if(empty($arrCart))
   {
      return;
   }

   $arrCartOutput = array();
   $arrPrevCollectionIDs = array();

   $DisableStyle = $_ARCHON->PublicInterface->DisableTheme;
   $_ARCHON->PublicInterface->DisableTheme = true;
//   $arrCartOutput[$objCollection->RepositoryID]

   foreach($arrCart->Collections as $CollectionID => $arrObjs)
   {
      foreach($arrObjs->Content as $ContentID => $obj)
      {
         if($obj instanceof Collection)
         {
            $objCollection = $obj;
            unset($objContent);
         }
         else
         {
            $objCollection = $obj->Collection;
            $objContent = $obj;
         }
         if($CollectionID != $arrPreviousCollectionIDs[$objCollection->RepositoryID])
         {
            if($arrPreviousCollectionIDs[$objCollection->RepositoryID])
            {
               $arrCartOutput[$objCollection->RepositoryID] .= "</dl>\n";
            }

            $arrCartOutput[$objCollection->RepositoryID] .= "<dl>\n";
            $arrCartOutput[$objCollection->RepositoryID] .= "<dt>" . $objCollection->toString(LINK_TOTAL)
                    . "<a class='removefromcart' href='#' onclick='removeFromCart({collectionid:" . $objCollection->ID . ",collectioncontentid:0}); return false;'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/removefromcart.gif' title='Remove from cart' alt='$strRemove'/></a></dt>\n";
         }

         if($objContent)
         {
            $arrCartOutput[$objCollection->RepositoryID] .= "<dd>" . $objContent->toString(LINK_EACH, true, true, true, true, $_ARCHON->PublicInterface->Delimiter)
                    . "<a class='removefromcart' href='#' onclick='removeFromCart({collectionid:" . $objCollection->ID . ",collectioncontentid:" . $objContent->ID . " }); return false;'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/removefromcart.gif' title='Remove from cart' alt='$strRemove'/></a></dt>\n";
         }

         $arrPreviousCollectionIDs[$objCollection->RepositoryID] = $CollectionID;
      }
   }

   $_ARCHON->PublicInterface->DisableTheme = $DisableStyle;
   echo("<div id='researchcart' class='bground'>");

   foreach($arrCartOutput as $RepositoryID => $output)
   {
      $objRepository = New Repository($RepositoryID);
      $objRepository->dbLoad();
      echo("<div class='repogrp' id=repo" . $RepositoryID . "><span class='cartrepository'>" . $objRepository->toString() . "</span>");
      echo($output);
      echo("</dl></div>\n");
   }
   echo("</div>");
}

function research_exec()
{
   global $_ARCHON;


   $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';




   if($_REQUEST['f'] == 'add')
   {
      if($_ARCHON->Security->Session->ResearchCart->addToCart($_REQUEST['collectionid'], $_REQUEST['collectioncontentid']))
      {
         if($callback)
         {
            echo($callback . "(");
         }
         $_ARCHON->Security->Session->ResearchCart->getCart();
         ?>
         {"response":
         {
         "message":"Item added to research cart",
         "cartcount":<?php echo($_ARCHON->Security->Session->ResearchCart->getCartCount()); ?>
         }
         }
         <?php
         if($callback)
         {
            echo(");");
         }
      }


      return;
   }
   elseif($_REQUEST['f'] == 'delete')
   {

      if($_ARCHON->Security->Session->ResearchCart->deleteFromCart($_REQUEST['collectionid'], $_REQUEST['collectioncontentid']))
      {
         if($callback)
         {
            echo($callback . "(");
         }
         $_ARCHON->Security->Session->ResearchCart->getCart();
         ?>
         {"response":
         {
         "message":"Item removed from research cart",
         "cartcount":<?php echo($_ARCHON->Security->Session->ResearchCart->getCartCount()); ?>
         }
         }
         <?php
         if($callback)
         {
            echo(");");
         }
      }
      return;
   }
   elseif($_REQUEST['f'] == 'makeappointment')
   {
      if(!$_ARCHON->Security->isAuthenticated())
      {
         $_ARCHON->declareError("You must be logged in to make an appointment.");
      }
      elseif($_ARCHON->Security->userHasAdministrativeAccess())
      {
         $_ARCHON->declareError("Only researchers may make an appointment.");
      }
      elseif($_REQUEST['arrivaltime'] < time())
      {
         $_ARCHON->declareError("ArrivalTime cannot be before the current time.");
      }
      elseif($_REQUEST['departuretime'] && ($_REQUEST['departuretime'] < $_REQUEST['arrivaltime']))
      {
         $_ARCHON->declareError("DepartureTime cannot be before the arrival time.");
      }
      elseif(!$_REQUEST['repositoryid'])
      {
         $_ARCHON->declareError("Could not store appointment: Repository must be defined.");
      }

      if($_ARCHON->Error)
      {
         $_REQUEST['arrivaldatestring'] = date(CONFIG_CORE_DATE_FORMAT, $_REQUEST['arrivaltime']);
         $_REQUEST['departuredatestring'] = date(CONFIG_CORE_DATE_FORMAT, $_REQUEST['departuretime']);
         $_REQUEST['f'] = 'cart';
      }
      else
      {
         $objAppointment = New ResearchAppointment($_REQUEST);
         $objAppointment->ID = 0;

         $objAppointment->ResearcherID = $_ARCHON->Security->Session->User->ID;
         $objAppointment->ArchivistComments = '';

         if($objAppointment->dbStore())
         {
            $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();

            if(!empty($arrCart))
            {
               foreach($arrCart->Collections as $CollectionID => $arrObjs)
               {
                  foreach($arrObjs->Content as $CollectionContentID => $obj)
                  {
                     if($obj instanceof Collection)
                     {
                        $objCollection = $obj;
                        unset($objContent);
                     }
                     else
                     {
                        $objCollection = $obj->Collection;
                        $objContent = $obj;
                     }
                     if($objCollection->RepositoryID == $_REQUEST['repositoryid'])
                     {
                       $objAppointment->dbRelateMaterials($CollectionID, $CollectionContentID);
                       $_ARCHON->Security->Session->ResearchCart->deleteFromCart($CollectionID, $CollectionContentID);
                     }
                  }
               }
            }

            if(!$_ARCHON->Error)
            {
               $objAppointment->sendEmails($_REQUEST['repositoryid']);
            }
         }

         if(!$_ARCHON->Error)
         {
            $_ARCHON->Security->Session->User->dbEmptyCart();

            $msg = "Thank you!  An e-mail has been sent with the details of your appointment.";
         }

         $location = "index.php?p={$_REQUEST['p']}&f=cart";
      }
   }
   elseif($_REQUEST['f'] == 'sendemails')
   {
      $arrDetails = $_ARCHON->Security->Session->ResearchCart->getCartDetailsArray();
      if(!empty($arrDetails))
      {
      	foreach($arrDetails as $RepositoryID => $details)
      	{
         	$_ARCHON->sendEmail($_REQUEST['fromaddress'], $_REQUEST['message'], $_REQUEST['referer'], $_REQUEST['fromname'], $_REQUEST['subject'], $_REQUEST['fromphone'], $_REQUEST['details'] . '\n\n' . $details, $_REQUEST['detailsfunction'], $_REQUEST['detailsparams'], $RepositoryID);
      	}
      }
      else
      {
      $_ARCHON->sendEmail($_REQUEST['fromaddress'], $_REQUEST['message'], $_REQUEST['referer'], $_REQUEST['fromname'], $_REQUEST['subject'], $_REQUEST['fromphone'], $_REQUEST['details'], $_REQUEST['detailsfunction'], $_REQUEST['detailsparams']);
      }
      if(!$_ARCHON->Error)
      {
         $msg = "Thank you! Your e-mail has been sent.";
         $uri = strstr($_REQUEST['referer'], '?');
         $location = $uri ? $uri : "index.php";
      }
      else
      {
         //$_REQUEST['f'] = 'email';
         $location = "index.php?" . $_REQUEST['query_string'];

         $params = array_intersect_key($_REQUEST, array_flip(array('fromaddress', 'message', 'fromname', 'subject', 'fromphone')));
      }
   }
   else
   {
      $location = "index.php?p={$_REQUEST['p']}&f=cart";
   }


   if($_ARCHON->Error)
   {
      $msg = $_ARCHON->clearError();
   }

   if($location)
   {
      $_ARCHON->sendMessageAndRedirect($msg, $location);
   }
   else
   {
      $_ARCHON->PublicInterface->Header->Message = $msg;
      research_initialize();
   }
}