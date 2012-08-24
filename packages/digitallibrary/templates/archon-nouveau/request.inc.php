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
?>

<form action="index.php" accept-charset="UTF-8" method="post">
<?php
echo("<div id='digcontentmetadata' class='mdround'>\n");

$strName = $_ARCHON->Security->Session->User ? $_ARCHON->Security->Session->User->getString('DisplayName') : '';
$strName = $_REQUEST['fromname'] ? encode($_REQUEST['fromname'], ENCODE_HTML) : $strName;
$strUserFrom = isset($_ARCHON->Security->Session->User) ? $_ARCHON->Security->Session->User->getString('Email') : '';
$strFrom = $_REQUEST['fromaddress'] ? encode($_REQUEST['fromaddress'], ENCODE_HTML) : $strUserFrom;
//$strFrom = encode($strFrom, ENCODE_HTML);
$objFromNamePhrase = Phrase::getPhrase('research_email_fromname', PACKAGE_RESEARCH, 0, PHRASETYPE_PUBLIC);
$strFromName = $objFromNamePhrase ? $objFromNamePhrase->getPhraseValue(ENCODE_HTML) : 'Your Name';
$objFromAddressPhrase = Phrase::getPhrase('research_email_fromaddress', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strFromAddress = $objFromAddressPhrase ? $objFromAddressPhrase->getPhraseValue(ENCODE_HTML) : 'Your Email Address';
$objFromPhonePhrase = Phrase::getPhrase('research_email_fromphone', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strFromPhone = $objFromPhonePhrase ? $objFromPhonePhrase->getPhraseValue(ENCODE_HTML) : 'Your Phone Number';
$objSubjectPhrase = Phrase::getPhrase('research_email_subject', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strSubject = $objSubjectPhrase ? $objSubjectPhrase->getPhraseValue(ENCODE_HTML) : 'Subject';
$objMessagePhrase = Phrase::getPhrase('research_email_message', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strMessage = $objMessagePhrase ? $objMessagePhrase->getPhraseValue(ENCODE_HTML) : 'Your Message';


$strPhone = isset($_ARCHON->Security->Session->User) ? $_ARCHON->Security->Session->User->getString('Phone') : '';
if($_ARCHON->Security->Session->User->PhoneExtension)
{
   $strPhone .= " $strExt: " . $_ARCHON->Security->Session->User->getString('PhoneExtension');
}
$strPhone = $_REQUEST['fromphone'] ? encode($_REQUEST['fromphone'], ENCODE_HTML) : $strPhone;

$in_referer = $_REQUEST['referer'] ? $_REQUEST['referer'] : $_REQUEST['HTTP_REFERER'];


$repository = $objDigitalContent->getRepository();
$repositoryid = $repository->ID;
?>
   <div>
      <input type="hidden" name="f" value="sendemail" />
      <input type="hidden" name="p" value="core/contact" />
      <input type="hidden" name="referer" value="<?php echo($in_referer); ?>" />
      <input type="hidden" name="repositoryid" value="<?php echo($repositoryid); ?>" />
      <input type="hidden" name="detailsparams[]" value="<?php echo($objDigitalContent->ID); ?>" />
      <input type="hidden" name="detailsparams[]" value="<?php echo($_REQUEST['fileid']); ?>" />
      <input type="hidden" name="detailsfunction" value="createEmailDetailsForHighResolutionRequest" />
      <input type="hidden" name="query_string" value="<?php echo($_SERVER['QUERY_STRING']); ?>" />
   </div>
   <div class="userformbox bground">

      <p class="bold"><?php echo($_ARCHON->PublicInterface->Title); ?></p>
      Fields marked with an asterisk (<span style="color:red">*</span>) are required.


      <div class="userformpair">
         <label for="name"><?php echo($strFromName); ?>:</label><br/>
         <input type="text" name="FromName" id="name" size="30" value="<?php echo($strName); ?>" />
      </div>
      <div class="userformpair">
         <label for="email"><span style="color:red">*</span> <?php echo($strFromAddress); ?>:</label><br/>
         <input type="text" name="FromAddress" id="email" size="25" value="<?php echo($strFrom); ?>" />
      </div>
      <div class="userformpair">
         <label for="phone"><?php echo($strFromPhone); ?>:</label><br/>
         <input type="text" name="FromPhone" id="phone" size="20" value="<?php echo($strPhone); ?>" />
      </div>
      <div class="userformpair">
         <label for="subject"><?php echo($strSubject); ?>:</label><br/>
         <input type="text" name="subject" id="subject" size="40" value="<?php echo(encode($_REQUEST['subject'], ENCODE_HTML)); ?>" />
      </div>
      <div class="userformpair">
         <label for="message"><span style="color:red">*</span> <?php echo($strMessage); ?>:</label><br/>
         <textarea name="message" id="message" cols="38" rows="5"><?php echo(encode($_REQUEST['message'], ENCODE_HTML)); ?></textarea>
      </div>

      <div id="digcontentlabel"></div>
      <div class="digcontentdata"><input type="submit" value="Send Request" class="button" /></div>

   </div>
</div>

