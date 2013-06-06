<?php
if(!file_exists('packages/core/index.php'))
{
   die('The Archon Core could not be found in packages/core/');
}

require_once('common.inc.php');

$totalsteps = 12;
if(preg_match('/^install([\d]+)$/', $_REQUEST['f'], $arrMatch) && $arrMatch[1] <= $totalsteps)
{
   $currentstep = $arrMatch[1];
}

$cwd = getcwd();
chdir('packages/core/lib/');
require_once('index.php');
require_once('archoninstaller.inc.php');
chdir($cwd);

ArchonInstaller::checkForMDB2();

require_once('MDB2.php');

require_once('config.inc.php');
require_once('start.inc.php');


if($_REQUEST['f'] == 'dbprogress')
{
   ArchonInstaller::printDBProgress();
   die();
}


if($currentstep > 8)
{
   $_ARCHON->initialize();
}
else
{
   include('packages/core/index.php');
   $_ARCHON->Version = $Version;
}

if($currentstep)
{
   call_user_func("core_install_{$_REQUEST['f']}");
}
else
{
   core_install_main();
}

function core_install_main()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;


   ob_start();
   $query = "SELECT ID FROM tblCore_Configuration LIMIT 1";
   $result = $_ARCHON->mdb2->query($query);
   ob_end_clean();

   if(!PEAR::isError($result))
   {

      $query = "SELECT * FROM tblCore_Packages WHERE APRCode = 'core' LIMIT 1";
      $result = $_ARCHON->mdb2->query($query);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $objCorePackage = New Package($row);
      if(version_compare($objCorePackage->DBVersion, $_ARCHON->Version) == -1)
      {
         header("Location: index.php?p=upgrade");
         die();
      }
      else
      {
         $currentstep = 0;
         output_install_header();
         ?>

<span style="color:red"><b>WARNING</b></span>: An installation of Archon has been detected!<br /><br />
If you re-install Archon, all data in the database <span style="color:red"><b>will be deleted</b></span>!<br /><br />
To continue re-installation, click next.

         <?php
         $currentstep = 1;
         $totalsteps = 2;

         output_install_footer();
      }
   }
   else
   {
      ob_start();
      $query = "SELECT ID FROM tblArchon_Configuration LIMIT 1";
      $result = $_ARCHON->mdb2->query($query);

      ob_end_clean();

      if(!PEAR::isError($result))
      {
         header("Location: index.php?p=upgrade");
         die();
      }
      else
      {
         header('Location: index.php?p=install&f=install1');
         die();
      }
   }
}




function core_install_install1()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

   output_install_header();

   ?>
<p style="width: 65%; margin: 30px auto 10px;">
   <strong>Welcome to Archon!</strong><br /><br />
   Click next to begin.
</p>

   <?php
   output_install_footer();
}




function core_install_install2()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

   $license = @file_get_contents('LICENSE');
   $disablenext = " disabled";

   output_install_header();

   ?>

Do you accept the license agreement?
<div class="center">
   <textarea disabled=true cols=89 rows=17><?php echo($license); ?></textarea>
</div>
<span class="center">
   <input type="radio" name="accepted" value="no" id="notaccepted" onclick="verify_license();" checked>
   <label for="notaccepted">I DO NOT Accept</label>
   <input type="radio" name="accepted" value="yes" id="accepted" onclick="verify_license();">
   <label for="accepted">I Accept</label>
</span>

<script type="text/javascript">
   /* <![CDATA[ */

   function verify_license() {
      if($('#accepted').attr('checked')){
         $('#nextbutton').removeAttr('disabled');
         $('#nextbutton').removeClass('disabled');

      }else{
         $('#nextbutton').attr('disabled', 'disabled');
         $('#nextbutton').addClass('disabled');
      }
   }

   /* ]]> */
</script>

   <?php
   output_install_footer();
}




function core_install_install3()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

   output_install_header();
   ?>
<div class="center">
   The Archon Installer will now attempt to connect to the database using the settings from config.inc.php.<br /><br />
   Click Next to Continue.<br /><br />
   Note: After clicking next, if you see an error message, check
   the database configuration in config.inc.php.
</div>
   <?php
   output_install_footer();
}




function core_install_install4()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

   $disablenext = " disabled";

   output_install_header();
   ?>
<p class="center">
   <strong>Testing Database Configuration:</strong>
</p>
<div class="class">
      <?php

      echo("Connecting to server {$_ARCHON->db->Server->Address}...<font color=lightgreen><b>OK</b></font><br /><br />");
      echo("Selecting Database {$_ARCHON->db->Name}...<font color=lightgreen><b>OK</b></font><br /><br />");
      echo("Testing Permissions:<br />");

      $query = "DROP TABLE tblArchon_InstallTest";
      $_ARCHON->mdb2->exec($query);

      echo(" - CREATE: ");
      $query = "CREATE TABLE tblArchon_InstallTest (TestField INT NOT NULL)";
      $result = $_ARCHON->mdb2->exec($query);
      if(!PEAR::isError($result))
      {
         echo("<font color=lightgreen><b>Yes</b></font><br />");
      }
      else
      {
         echo("<font color=red><b>No</b></font><br />");
         $failed = 1;
      }

      echo(" - INSERT: ");
      $query = "INSERT INTO tblArchon_InstallTest (TestField) VALUES (1)";
      $result = $_ARCHON->mdb2->exec($query);
      if(!PEAR::isError($result))
      {
         echo("<font color=lightgreen><b>Yes</b></font><br />");
      }
      else
      {
         echo("<font color=red><b>No</b></font><br />");
         $failed = 1;
      }

      echo(" - SELECT: ");
      $query = "SELECT TestField FROM tblArchon_InstallTest";
      $result = $_ARCHON->mdb2->query($query);
      if(!PEAR::isError($result))
      {
         echo("<font color=lightgreen><b>Yes</b></font><br />");
      }
      else
      {

         echo("<font color=red><b>No</b></font><br />");
         $failed = 1;
      }

      echo(" - UPDATE: ");
      $query = "UPDATE tblArchon_InstallTest SET TestField = 2";
      $result = $_ARCHON->mdb2->exec($query);
      if(!PEAR::isError($result))
      {
         echo("<font color=lightgreen><b>Yes</b></font><br />");
      }
      else
      {
         echo("<font color=red><b>No</b></font><br />");
         $failed = 1;
      }

      echo(" - ALTER: ");
      $query = "ALTER TABLE tblArchon_InstallTest ADD TestVarchar varchar(100)";
      $result = $_ARCHON->mdb2->exec($query);
      if(!PEAR::isError($result))
      {
         echo("<font color=lightgreen><b>Yes</b></font><br />");
      }
      else
      {
         echo("<font color=red><b>No</b></font><br />");
         $failed = 1;
      }

      echo(" - DROP: ");
      $query = "DROP TABLE tblArchon_InstallTest";
      $result = $_ARCHON->mdb2->exec($query);
      if(!PEAR::isError($result))
      {
         echo("<font color=lightgreen><b>Yes</b></font><br />");
      }
      else
      {
         echo("<font color=red><b>No</b></font><br />");
         $failed = 1;
      }

      ?>

      <?php
      if($failed == 1)
         echo("ERROR: Database user ".$_ARCHON->db->Server->Login." must be granted all of the above permissions in order for Archon to function properly!<br /><br />Installation cannot continue.");
      else
      {
         echo("Database configuration tests complete, click next to continue.");
         $disablenext = "";
      }
      ?>
</div>
   <?php
   output_install_footer();
}




function core_install_install5()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

   require_once("packages/core/lib/archoninstaller.inc.php");


   if($_REQUEST['exec']==true)
   {
      ignore_user_abort(true);

      header("Connection: close\r\n");
      header("Content-Encoding: none\r\n");
      ob_start();

      echo('submitted');
      $length = ob_get_length();
      header("Content-Length: ".$length);
      ob_end_flush();
      ob_flush();
      flush();


      ArchonInstaller::installDB('packages/core/install');
      ArchonInstaller::updateDBProgressTable('DONE', '');
      die();
   }

   ArchonInstaller::dropDBProgressTable();
   ArchonInstaller::createDBProgressTable();


   ob_start();

   output_install_header();

   ?>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $.ajax({
         url: 'index.php?p=install&f=install5&exec=true',
         global: false
      });
      updateMessageBox();
   });

   /* ]]> */
</script>
<p id="banner" class="info"><strong>Creating Database Structure...</strong>
</p>
<div id="loader" class="center">
   <img src="adminthemes/default/images/bar-loader.gif" alt="loading" />
</div>
<div id="messagebox">
   Current step: <span class="message">Initializing...</span>
</div>
<p id="successmessage" class="hidden"> <strong>Success!</strong> Please click next to continue installing Archon.</p>

   <?php
   $disablenext = true;
   output_install_footer();

   ob_end_flush();
   ob_flush();
   flush();


}




function core_install_install6()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext, $onsubmit;
   //@TODO: set timeout here or something
//   $InstallationID = @file_get_contents('http://www.archon.org/sys/getid.php?version=' . urlencode($_ARCHON->Version));
   $InstallationID = rand(10000, getrandmax());
   if($InstallationID && is_natural($InstallationID))
   {
      $query = "UPDATE tblCore_Configuration SET Value = '$InstallationID' WHERE Directive = 'Installation ID'";
      $_ARCHON->mdb2->exec($query);
   }

   $query = "SELECT ID FROM tblCore_Users";
   $result = $_ARCHON->mdb2->query($query);
   if($result->numRows() > 0)
   {
      header("Location: index.php?p=install&f=install" . ($currentstep+1));
      die();
   }

   $query = "SELECT ID FROM tblCore_Usergroups WHERE Usergroup = 'Administrators'";
   $result = $_ARCHON->mdb2->query($query);
   $usergrouprow = $result->fetchRow();

   $onsubmit = 'core_install_js_user_verify()';
   output_install_header();

   ?>

<script type="text/javascript">
   <!--
   function core_install_js_user_verify()
   {
      if($('[name="SAPassword"]').val() == "")
      {
         alert("You must enter an SA Password!");
         $('[name="SAPassword"]').focus();
         return false;
      }
      else if($('[name="SAPassword"]').val() != $('[name="ConfirmSAPassword"]').val())
      {
         alert("SA Passwords must match!");
         $('[name="SAPassword"]').val("");
         $('[name="ConfirmSAPassword"]').val("");
         $('[name="SAPassword"]').focus();
         return false;
      }
      if($('[name="Login"]').val() == "")
      {
         alert("You must enter a Login!");
         $('[name="Login"]').focus();
         return false;
      }
      else if($('[name="Password"]').val() != $('[name="ConfirmPassword"]').val())
      {
         alert("Passwords must match!");
         $('[name="Password"]').val("");
         $('[name="ConfirmPassword"]').val("");
         $('[name="Password"]').focus();
         return false;
      }
      else if($('[name="Password"]').val() == "")
      {
         alert("You must enter a Password!");
         $('[name="Password"]').focus();
         return false;
      }
      else if($('[name="DisplayName"]').val() == "")
      {
         alert("You must enter a Display Name!");
         $('[name="DisplayName"]').focus();
         return false;
      }
      else
         return true;
   }
   -->
</script>
<input type=hidden name="UsergroupID" value="<?php echo($usergrouprow['ID']); ?>">
<table class="noborder center" style="width:65%">
   <tr>
      <td>
         Set SA Password:<br />
         Note: The sa account will allow administrative access to Archon and should only be used if the user table has been corrupted.  Simply use the username 'sa' (no quotes) and the password set below to use this account.
      </td>
   </tr>
   <tr>
      <td><hr /></td>
   </tr>
   <tr>
      <td align="center">
         <table width="75%" class="noborder">
            <tr>
               <td>Password:</td>
               <td><input type=password name="SAPassword" size=30 maxlength=50></td>
            </tr>
            <tr>
               <td>Confirm Password:</td>
               <td><input type=password name="ConfirmSAPassword" size=30 maxlength=50></td>
            </tr>
         </table>
      </td>
   <tr>
      <td>
         Create Administrator Account:
      </td>
   </tr>
   <tr>
      <td><hr /></td>
   </tr>
   <tr>
      <td align="center">
         <table width="75%" class="noborder">
            <tr>
               <td>Login:</td>
               <td><input type=text name="Login" size=30 maxlength=50></td>
            </tr>
            <tr>
               <td>Password:</td>
               <td><input type=password name="Password" size=30 maxlength=50></td>
            </tr>
            <tr>
               <td>Confirm Password:</td>
               <td><input type=password name="ConfirmPassword" size=30 maxlength=50></td>
            </tr>
            <tr>
               <td>Display Name:</td>
               <td><input type=text name="DisplayName" size=30 maxlength=50></td>
            </tr>
            <tr>
               <td valign="top">Usergroup:</td>
               <td>Administrators</td>
            </tr>
         </table>
      </td>
   </tr>
</table>

   <?php

   output_install_footer();
}




function core_install_install7()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext, $onsubmit;

   $_ARCHON->Security = New Security();
   $_ARCHON->RootDirectory = getcwd();
   require_once("packages/core/lib/country.inc.php");

   $SecurityDisabled = $_ARCHON->Security->Disabled;
   $_ARCHON->Security->Disabled = true;

   if($_REQUEST['login'] && $_REQUEST['password'] && $_REQUEST['displayname'])
   {
      $objUser = New User($_REQUEST);
      $objUser->ID = 0;
      $objUser->IsAdminUser = 1;
      $objUser->LanguageID = 2081;
      $objUser->dbStore();
      $objUser->dbUpdateRelatedUsergroups(array($_REQUEST['usergroupid']));
      $UserID = $objUser->ID;
   }

   $UserID = ($UserID) ? $UserID : 0;

   if($_REQUEST['sapassword'])
   {
      $pwhash = crypt($_REQUEST['sapassword'], crypt($_REQUEST['sapassword']));
      $query = "UPDATE tblCore_Configuration SET Value = '$pwhash' WHERE Directive = 'SA Password'";
      $_ARCHON->mdb2->exec($query);
   }
   $_ARCHON->Security->Disabled = $SecurityDisabled;

   $onsubmit = 'core_install_js_repository_verify()';
   output_install_header();

   $arrCountries = Country::getAllCountries(true);
   if(empty($arrCountries))
   {
      $arrCountries[226] = "United States";
   }
   ?>
<script type="text/javascript">
   <!--
   function core_install_js_repository_verify()
   {
      if($('[name="Name"]').val() == "")
      {
         alert("You must enter a name!");
         return false;
      }
      else if($('[name="CountryID"]').val() <= 0)
      {
         alert("You must choose a country!");
         return false;
      }
      else
         return true;
   }
   -->
</script>

<input type="hidden" name="UserID" value="<?php echo($UserID); ?>">

<table class="noborder center" style="width:65%">
   <tr>
      <td>
         Configure Repository Information:
      </td>
   </tr>
   <tr>
      <td><hr /></td>
   </tr>
   <tr>
      <td align="center">
         <table width="95%" class="noborder">
            <tr>
               <td>Repository Name:</td>
               <td><input type="text" name="Name" size="50" maxlength="100"></td>
            </tr>
            <tr>
               <td>Name of Administrator:</td>
               <td><input type=text name="Administrator" size=50 maxlength=50></td>
            </tr>
            <tr>
               <td>MARC Organization Code:</td>
               <td><input type=text name="Code" size=6 maxlength=10></td>
            </tr>
            <tr>
               <td>Country:</td>
               <td><select name="CountryID">
                     <option value="0">(Select One)</option>
                        <?php

                        foreach($arrCountries as $ID => $Country)
                        {
                           echo("<option value=\"{$ID}\">{$Country}</option>");
                        }
                        ?>
                  </select><td>
            </tr>
            <tr>
               <td>Address:</td>
               <td><input type=text name="Address" size=40 maxlength=100></td>
            </tr>
            <tr>
               <td>&nbsp;</td>
               <td><input type=text name="Address2" size=40 maxlength=100></td>
            </tr>
            <tr>
               <td>City, State, ZIP Code:</td>
               <td><input type=text name="City" size=15 maxlength=75>, <input type=text name="State" value="<?php echo($row['State']); ?>" size=2 maxlength=2>, <input type=text name="ZIPCode" value="<?php echo($row['ZIPCode']); ?>" size=5 maxlength=5>-<input type=text name="ZIPPlusFour" value="<?php echo($row['ZIPPlusFour']); ?>" size=4 maxlength=4></td>
            </tr>
            <tr>
               <td>Phone Number:</td>
               <td><input type=text name="Phone" size=15 maxlength=25>-<input type=text name="PhoneExtension" size=5 maxlength=10></td>
            </tr>
            <tr>
               <td>Fax Number:</td>
               <td><input type=text name="Fax" size=15 maxlength=25></td>
            </tr>
            <tr>
               <td>E-Mail:</td>
               <td><input type=text name="Email" size=20 maxlength=50></td>
            </tr>
            <tr>
               <td>Website URL:</td>
               <td><input type=text name="URL" value="http://<?php echo($_SERVER['HTTP_HOST'].str_replace("/install", "/", substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/")))); ?>" size=50 maxlength=255></td>
            </tr>
         </table>
      </td>
   </tr>
</table>


<div class="notice box">
   Please note that the information provided in this form shall be
   sent once to our secure database so we may assist you should
   any issues arise with the software. Your information will not
   be given to any third parties.
</div>
   <?php

   output_install_footer();
}

function core_install_install8()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

   $_ARCHON->Security = New Security();


   output_install_header();

   //store repository info here

   if($_REQUEST['name'])
   {
      $currentSecurity = $_ARCHON->Security->Disabled;
      $_ARCHON->Security->Disabled = true;

      $objRepository = New Repository($_REQUEST);
      $objRepository->ID = 0;
      $objRepository->dbStore();

      $_ARCHON->Security->Disabled = $currentSecurity;

      $uid = $_REQUEST['userid'];
      //echo($uid. " ". $objRepository->ID);

      if($objRepository->ID && is_natural($objRepository->ID))
      {

         if($uid && is_natural($uid))
         {
            $query = "INSERT INTO tblCore_UserRepositoryIndex (UserID,RepositoryID) VALUES ('{$uid}','{$objRepository->ID}')";
            $_ARCHON->mdb2->exec($query);
         }

         $query = "UPDATE tblCore_Configuration SET Value = '{$objRepository->ID}' WHERE Directive = 'Default Repository';";
         $affected=$_ARCHON->mdb2->exec($query);
         if(PEAR::isError($affected))
         {
            echo($affected->getMessage());
         }


         $query = "SELECT Value FROM tblCore_Configuration WHERE Directive = 'Installation ID'";
         $result = $_ARCHON->mdb2->query($query);
         $row = $result->fetchRow();

//         @file_get_contents('http://www.archon.org/sys/updaterepository.php?installationid=' . $row['Value'] . '&' . http_build_query($objRepository));
      }
   }



//@TODO: Put this into the installer class

   $arrInstalledPackages = $_ARCHON->getAllPackages(false);
   if(count($arrInstalledPackages) > 1)
   {
      header("Location: index.php?p=install&f=install" . ($currentstep + 1));
      die();
   }

   if($handle = opendir("packages/"))
   {
      while(false !== ($dir = readdir($handle)))
      {
         if($dir != ".." && file_exists("packages/$dir/index.php") && file_exists("packages/$dir/install/install.php") && $dir != '.')
         {
            include("packages/$dir/index.php");

            $DependsUpon[$dir] = '';
            $Enhances[$dir] = '';

            $disablecheckbox[$dir] = '';

            $_ARCHON->Packages[$dir]->APRCode = $dir;
            $_ARCHON->Packages[$dir]->DBVersion = $Version;
         }
      }
   }

   if(!empty($_ARCHON->Packages))
   {
      foreach($_ARCHON->Packages as $dir => $objPackage)
      {
         if(!empty($objPackage->DependsUpon))
         {
            foreach($objPackage->DependsUpon as $APRCode => $Version)
            {
               $DependsUpon[$dir] .= $APRCode . ' ' . $Version . '<br />';

               if($APRCode != 'core' || version_compare($_ARCHON->Packages['core']->DBVersion, $Version) == -1)
               {
                  $disablecheckbox[$dir] = ' disabled';
               }
            }
         }

         if(!empty($objPackage->Enhances))
         {
            foreach($objPackage->Enhances as $APRCode => $Version)
            {
               $Enhances[$dir] .= $APRCode . ' ' . $Version . '<br />';
            }
         }
      }
   }


   ?>

<script type="text/javascript">
   <!--

   // offer the most common ones when the page loads.
   $(function() {
      $('[name="install_packages[]"][value="creators"]').attr("checked","checked"); core_install_js_checkClick('creators');
      $('[name="install_packages[]"][value="subjects"]').attr("checked","checked"); core_install_js_checkClick('subjects');
      $('[name="install_packages[]"][value="collections"]').attr("checked","checked"); core_install_js_checkClick('collections');
      $('[name="install_packages[]"][value="digitallibrary"]').attr("checked","checked"); core_install_js_checkClick('digitallibrary');
   });



   var dependencyCounts = Array();
   <?php
   if(!empty($_ARCHON->Packages))
   {
      foreach($_ARCHON->Packages as $dir => $objPackage)
      {
         echo("    dependencyCounts['$dir'] = " . count($objPackage->DependsUpon) . ";\n");
         if($objPackage->DependsUpon['core'] && version_compare($_ARCHON->Packages['core']->DBVersion, $objPackage->DependsUpon['core']->Version) != -1)
         {
            echo("dependencyCounts['$dir']--;\n\n");
         }
      }
   }
   ?>
      function core_install_js_checkClick(aprcode)
      {
   <?php
   if(!empty($_ARCHON->Packages))
   {
      foreach($_ARCHON->Packages as $dir => $objPackage)
      {
         if(!empty($objPackage->DependsUpon))
         {
            foreach($objPackage->DependsUpon as $APRCode => $Version)
            {
               if(version_compare($_ARCHON->Packages[$APRCode]->DBVersion, $Version) != -1)
               {
                  ?>
                        if(aprcode == '<?php echo($APRCode); ?>')
                        {
                           if($('[name="install_packages[]"][value="<?php echo($APRCode); ?>"]').attr("checked"))
                           {
                              dependencyCounts['<?php echo($dir); ?>']--;
                           }
                           else
                           {
                              dependencyCounts['<?php echo($dir); ?>']++;
                              if($('[name="install_packages[]"][value="<?php echo($dir); ?>"]').attr("checked"))
                              {
                                 $('[name="install_packages[]"][value="<?php echo($dir); ?>"]').removeAttr("checked");
                                 core_install_js_checkClick('<?php echo($dir); ?>');
                              }
                           }

                           if(dependencyCounts['<?php echo($dir); ?>'] == 0)
                           {
                              $('[name="install_packages[]"][value="<?php echo($dir); ?>"]').removeAttr("disabled");
                           }
                           else
                           {
                              $('[name="install_packages[]"][value="<?php echo($dir); ?>"]').attr("disabled", "disabled");
                           }
                        }
                  <?php
               }
            }
         }
      }
      echo("\n");
   }
   ?>
      }
      -->
</script>

<p style="width: 65%; margin: 30px auto 10px;">
   Archon may be configured to use a number of 'packages'. Each of these packages will increase Archon's functionality.<br />
   <strong>Below, please choose the packages you wish to install.</strong></p>
<div class="notice box">
   Note that you can only choose to
   install a package if all the packages it depends upon are also being installed.  For example, you can only install the 'collections' package if the 'creators' package is being installed.  Therfore, certain options may be deselected automatically if you deselect certain packages.
</div>
<table style="width:85%" class="noborder center">
   <tr>
      <th>Package Name</th>
      <th>Depends Upon</th>
      <th>Enhances</th>
      <th>Install</th>
   </tr>
      <?php
      if(!empty($_ARCHON->Packages))
      {
         foreach($_ARCHON->Packages as $APRCode => $objPackage)
         {
            if($APRCode == 'core')
            {
               continue;
            }
            ?>
   <tr>
      <td><?php echo($APRCode); ?></td>
      <td><?php echo($DependsUpon[$APRCode]); ?></td>
      <td><?php echo($Enhances[$APRCode]); ?></td>
      <td><input type=checkbox name="install_packages[]" value="<?php echo($APRCode); ?>"<?php echo($disablecheckbox[$APRCode]); ?> onclick="core_install_js_checkClick('<?php echo($APRCode); ?>');"></td>
   </tr>
            <?php
         }
      }
      ?>
</table>

   <?php
   output_install_footer();
}




function core_install_install9()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

//    $_ARCHON->Security = New Security();

   require_once("packages/core/lib/archoninstaller.inc.php");


   if($_REQUEST['exec']==true)
   {
      ignore_user_abort(true);

      header("Connection: close\r\n");
      header("Content-Encoding: none\r\n");
      ob_start();

      echo('submitted');
      $length = ob_get_length();
      header("Content-Length: ".$length);
      ob_end_flush();
      ob_flush();
      flush();



      $SecurityDisabled = $_ARCHON->Security->Disabled;
      $_ARCHON->Security->Disabled = true;

      $arrInstallPackages = (is_array($_REQUEST["install_packages"]))? $_REQUEST["install_packages"] : array();

      if($handle = opendir("packages/"))
      {
         while(false !== ($dir = readdir($handle)))
         {
            if($dir != ".." && file_exists("packages/$dir/index.php") && file_exists("packages/$dir/install/install.php") && $dir != '.')
            {
               if(!$_ARCHON->Packages[$dir] && array_search($dir, $arrInstallPackages) !== false)
               {
                  include("packages/$dir/index.php");

                  $_ARCHON->Packages[$dir]->APRCode = $dir;
                  $_ARCHON->Packages[$dir]->DBVersion = $Version;
               }
            }
         }
      }
      else
      {
         ArchonInstaller::updateDBProgressTable('ERROR', 'Could not open packages directory!');
      }

      foreach($_ARCHON->Packages as $objPackage)
      {
         $Code = $objPackage->APRCode;

         if($Code == 'core')
         {
            continue;
         }

         if($_ARCHON->Packages[$objPackage->APRCode]->Enabled)
         {
            continue;
         }

         if(!file_exists("packages/$objPackage->APRCode/install/install.php"))
         {
            continue;
         }

         include("packages/$objPackage->APRCode/install/install.php");
      }

      ArchonInstaller::updateDBProgressTable('DONE', '');
      die();
   }

   ArchonInstaller::updateDBProgressTable('', '');



   ob_start();

   output_install_header();

   ?>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $.ajax({
         url: 'index.php?p=install&f=install9&exec=true',
         data: {
            'install_packages[]': <?php echo(js_array($_REQUEST['install_packages'])); ?>
         },
         global: false
      });
      updateMessageBox();
   });

   /* ]]> */
</script>
<p id="banner" class="info"><strong>Installing Packages...</strong>
</p>
<div id="loader" class="center">
   <img src="adminthemes/default/images/bar-loader.gif" alt="loading" />
</div>
<div id="messagebox">
   Current step: <span class="message">Initializing...</span>
</div>
<p id="successmessage" class="hidden"> <strong>Success!</strong> Please click next to continue installing Archon.</p>

   <?php
   $disablenext = true;
   output_install_footer();

   ob_end_flush();
   ob_flush();
   flush();


}


function core_install_install10()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;

//    $_ARCHON->Security = New Security();


   require_once("packages/core/lib/archoninstaller.inc.php");
   output_install_header();
   ob_start();

   $arrPhraseLanguages = ArchonInstaller::getPhraseLanguagesArray();

   ob_end_clean();


   ?>
<script type="text/javascript">
   <!--
   $(function() {
      if($('#DefaultLanguageID option[value=0]').attr('selected')) {
         $('input[name="nextbutton"]').attr('disabled', 'disabled');
      }

      $('#DefaultLanguageID').change(function(e) {
         if($(e.target).children('[value=0]').attr('selected')){
            $('input[name="nextbutton"]').attr('disabled', 'disabled');
         }else{
            $('input[name="nextbutton"]').removeAttr('disabled');
         }
      });

      $('#languages :checkbox').click(function (e) {
         var lang = $(e.target).val();
         if($(e.target).attr('checked')){
            $('#DefaultLanguageID option').each(function (){
               if($(this).attr('value') == lang) {
                  $(this).removeAttr('disabled');
               }
            });
         }else{
            $('#DefaultLanguageID option').each(function (){
               if($(this).attr('value') == lang) {
                  $(this).attr('disabled', 'disabled');
                  if($(this).attr('selected')) {
                     $(this).removeAttr('selected');
                     $('#DefaultLanguageID option[value=0]').attr('selected', 'selected');
                     $('#DefaultLanguageID').change();
                  }
               }
            });

            if($('#languages :checkbox:checked').length == 0) {
               $('input[name="nextbutton"]').attr('disabled', 'disabled');
            }else {
               $('input[name="nextbutton"]').removeAttr('disabled');
            }
         }
      });
   });
   -->
</script>


<div class="center">

   <p>The Archon Administrative Interface has support for multiple languages.
      Please select the languages you wish it to support below.</p>
   <p>You may also select the default language to be used in the Administrative Interface.</p>

   <table id="languages" style="width:45%; margin: 20px auto; text-align: left;">
      <tr>
         <th>Language</th>
         <th>Install</th>
      </tr>
         <?php


         foreach($arrPhraseLanguages['languages'] as $objLanguage)
         {
            $checked = ($objLanguage->LanguageShort == 'eng') ? 'checked' : '';
            ?>
      <tr>
         <td><?php echo($objLanguage->toString()) ?></td>
         <td><input type="checkbox" name="languageIDs[]" value="<?php echo($objLanguage->ID); ?>" checked="<?php echo($checked); ?>" /></td>
      </tr>
            <?php
         }
         ?>
      <tr>
         <td colspan="2"><hr style="border: none; background: #ddd;" /></td>
      </tr>
      <tr>
         <td><strong>Default Language:</strong></td>
         <td>
            <select id ="DefaultLanguageID" name="DefaultLanguageID">
               <option value="0">(Select One)</option>
                  <?php
                  foreach($arrPhraseLanguages['languages'] as $objLanguage)
                  {
                     $selected = ($objLanguage->LanguageShort == 'eng') ? ' selected' : '';

//                     $disabled = (array_search($objLanguage->ID, $arrPhraseLanguages['installed']) === false) ? ' disabled="disabled"' : '';

                     echo('<option value="'.$objLanguage->ID.'"'.$selected.'>'.$objLanguage->LanguageLong.'</option>');
                  }
                  ?>
            </select>
         </td>
      </tr>
   </table>
</div>
   <?php

   $_ARCHON->Security->Disabled = $SecurityDisabled;

   output_install_footer();
}




function core_install_install11()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;
   require_once("packages/core/lib/archoninstaller.inc.php");


   if($_REQUEST['exec']==true)
   {
      ignore_user_abort(true);

      header("Connection: close\r\n");
      header("Content-Encoding: none\r\n");
      ob_start();

      echo('submitted');
      $length = ob_get_length();
      header("Content-Length: ".$length);
      ob_end_flush();
      ob_flush();
      flush();

      $in_DefaultLanguageID = $_REQUEST['defaultlanguageid'] ? $_REQUEST['defaultlanguageid'] : 0;

      if($in_DefaultLanguageID && is_natural($in_DefaultLanguageID))
      {
         $CorePackageID = PACKAGE_CORE;
         $query = "UPDATE tblCore_Configuration SET Value = '$in_DefaultLanguageID' WHERE PackageID = '$CorePackageID' AND Directive = 'Default Language';";
         $affected = $_ARCHON->mdb2->exec($query);
         ArchonInstaller::handleError($affected, $query);
      }

      foreach($_REQUEST['languageids'] as $languageID)
      {
         $objLanguage = New Language($languageID);
         $objLanguage->dbLoad();
         $strRequest = 'language_'.$objLanguage->LanguageShort;
         $_REQUEST[$strRequest]=true;
      }


      $SecurityDisabled = $_ARCHON->Security->Disabled;
      $_ARCHON->Security->Disabled = true;

      $_REQUEST['f'] = 'import-phrasexml';
      $_REQUEST['allpackages'] = true;

      include('packages/core/db/import-phrasexml.inc.php');

      $_ARCHON->Security->Disabled = $SecurityDisabled;


      ArchonInstaller::updateDBProgressTable('DONE', '');
      die();
   }


   ArchonInstaller::updateDBProgressTable('', '');



   ob_start();

   output_install_header();

   ?>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $.ajax({
         url: 'index.php?p=install&f=install11&exec=true',
         data: {
            defaultlanguageid: '<?php echo($_REQUEST['defaultlanguageid']); ?>',
            'languageids[]': <?php echo(js_array($_POST['languageIDs'])); ?>
         },
         global: false
      });

      updateMessageBox();
   });

   /* ]]> */
</script>
<p id="banner" class="info"><strong>Installing Phrases...</strong>
</p>
<div id="loader" class="center">
   <img src="adminthemes/default/images/bar-loader.gif" alt="loading" />
</div>
<div id="messagebox">
   <p style="font-weight:normal">The Archon Installer is now loading phrases into the database to prepare for
      multilingual support in the administrative interface. Please be patient as this
      may take a while.</p>
</div>
<p id="successmessage" class="hidden"> <strong>Phrase installation complete!</strong> Please click "Next".</p>


   <?php
   $disablenext = true;
   output_install_footer();

   ob_end_flush();
   ob_flush();
   flush();

//
//   $in_DefaultLanguageID = $_REQUEST['defaultlanguageid'] ? $_REQUEST['defaultlanguageid'] : 0;
//
//   if($in_DefaultLanguageID && is_natural($in_DefaultLanguageID))
//   {
//      $CorePackageID = PACKAGE_CORE;
//      $query = "UPDATE tblCore_Configuration SET Value = '$in_DefaultLanguageID' WHERE PackageID = '$CorePackageID' AND Directive = 'Default Language';";
//      $affected = $_ARCHON->mdb2->exec($query);
//      if (PEAR::isError($affected))
//      {
//         trigger_error($affected->getMessage(), E_USER_ERROR);
//      }
//   }
//
//   foreach($_POST['languageIDs'] as $languageID)
//   {
//      $objLanguage = New Language($languageID);
//      $objLanguage->dbLoad();
//      $strRequest = 'language_'.$objLanguage->LanguageShort;
//      $_REQUEST[$strRequest]=true;
//   }
//
//   output_install_header();
//
//   ? >
//
//
//<p>The Archon Installer is now loading phrases into the database to prepare for
//   multilingual support in the administrative interface. Please be patient as this
//   may take a while.</p>
//
//
//<div style="text-align:left; padding: 20px; margin:10px auto; width:75%;">
//
//
//      <?php
//      $SecurityDisabled = $_ARCHON->Security->Disabled;
//      $_ARCHON->Security->Disabled = true;
//
//      ob_flush();
//      flush();
//      $_REQUEST['f'] = 'import-phrasexml';
//      $_REQUEST['allpackages'] = true;
//
//      include('packages/core/db/import-phrasexml.inc.php');
//
//      $_ARCHON->Security->Disabled = $SecurityDisabled;
//      ? >
//</div>
//
//<p style="text-align: center;"> Phrase install complete! Please click "Next". </p>
//   < ?php
//   output_install_footer();
}




function core_install_install12()
{
   global $_ARCHON, $currentstep, $totalsteps, $disablenext;
   require_once("packages/core/lib/archoninstaller.inc.php");

   ArchonInstaller::dropDBProgressTable();


   output_install_header();
   ?>
<div class="center">
   <p style="text-align: center;"> <strong>  Installation Complete!</strong></p>
   <div style="width:60%; text-align:left; margin:0 auto">
      Archon is now installed, and configured!<br /><br />

      <b><u>If you ARE using the Archon Installer utility:</u></b><br />
      1) Copy the following word to the clipboard (or write it down): <font color=lightgreen><b>ALLDONE</b></font><br />
      2) Return to the installer utility and enter the word above into the prompt.<br />
      3) Click Finish.<br /><br />

      <b><u>If you ARE NOT using the Archon Installer utility:</u></b><br />
      In order to begin using Archon, you <b>MUST</b> delete or rename the
      install.php file from the /packages/core/install/ directory.  Archon will NOT work
      until this file no longer exists.<br /><br />

      Once you have done this, click Finish to access the Administrative Interface.<br /><br />
   </div>
</div>
   <?php
   output_install_footer();
}






function output_install_header($increment_step = true)
{
   global $_ARCHON, $currentstep, $totalsteps, $DBVersion, $onsubmit;

   include("adminthemes/default/installerheader.inc.php");
   if($increment_step)
   {
      $step_value = $currentstep + 1;
   }
   else
   {
      $step_value = $currentstep;
   }



   ?>

<div class="center"><form id="installform" name="install" method="post" action="index.php" accept-charset="UTF-8">
      <input type="hidden" name="p" value="install" />
      <input type="hidden" name="f" value="install<?php echo($step_value); ?>" />
      <div style="font-size:1.1em; font-weight:bolder; padding:0 5px 10px; margin-bottom: 10px; border-bottom: 1px solid #eee"><?php echo($_ARCHON->ProductName); ?> <?php echo($_ARCHON->Version); ?> Installer (Step <?php echo($currentstep); ?> of <?php echo($totalsteps); ?>)</div>
      <div id="installerpage">

            <?php
         }


         function output_install_footer()
         {
            global $_ARCHON, $currentstep, $totalsteps, $disablenext, $callback, $onsubmit;
            $disabled = $disablenext ? 'disabled' : '';

            if($onsubmit)
            {
               $js_onsubmit = "if(!{$onsubmit}){return false;}";

            }
            else
            {
               $js_onsubmit = "";
            }

            ?>
      </div>
      <div id="installercontrols">
            <?php
            if($currentstep > 1)
            {
               ?>
         <input type='button' class="adminformbutton " style="float:left" name="prevbutton" value="Previous" onclick="location.href='?p=install&amp;f=install<?php echo($currentstep - 1); ?>';" />
               <?php
            }

            if($currentstep == $totalsteps)
            {
               ?>
         <input type='button' class="adminformbutton" style="float:right" name="finishbutton" value="Finish" onclick="location.href='?p=admin/core/packages';" />
               <?php
            }
            else
            {

               ?>
         <input type='submit' id="nextbutton" class="adminformbutton  <?php echo($disabled); ?>"  <?php echo($disabled); ?> style="float:right" name="nextbutton" value="Next" onclick="<?php echo($js_onsubmit); ?> $(this).addClass('disabled'); $(this).attr('disabled','disabled'); $('#installform').submit(); return false;" />
               <?php
            }
            ?>

      </div>

   </form>
</div>

   <?php


   $LatestVersion = $_ARCHON->getLatestArchonVersion();

   if(version_compare($_ARCHON->Version, $LatestVersion) < 0)
   {
      echo("<br /><span style='font-size:2;color:green'><b>NOTICE:</b> A newer version of Archon (version $LatestVersion) has been released, visit <a href='http://www.archon.org/'>www.archon.org</a> to upgrade.</span>");
   }
   include('adminthemes/default/installerfooter.inc.php');

}

?>
