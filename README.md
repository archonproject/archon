Archon Installation

Installation Requirements

Archon is a content management system for archival information.

Version 3.21 r-2 is the last version of archon and includes code that allows for migration to ArchivesSpace: http://archivesspace.org

The interface for accessing and editing descriptive records uses a standard web browser, with JavaScript enabled. It has been tested with Firefox 3, Safari, and Opera 8 and higher, on PC, Mac and Linux desktops. The public interface will work in the Internet Explorer 7 or Higher, but the staff interface will not work in Internet Explorer.

To install Archon you must have access to both a web server and a database server (either MySQL or Microsoft SQLServer). Specifically, you need the following in place before running the install packet. If you do not understand any of these requirements, check with your web host or systems administrator.

   1. A standard installation of MySQL or Microsoft SQL Server. We have tested the application with MySQL 5.0 and SQL Server 7 and 2000, but it should work with later releases of these platforms as well.
   2. A blank database on the database server. When creating the database, create a user with full (owner) privileges. You do not need to specify any table names, data definitions, or the like. They will be created or updated automatically when you run the install packet.
  3. A web server of any type with PHP 5.1 or higher installed. The latest version of PHP is strongly recommended. Depending on which database server you plan to use, the mysql or mssql library must be enabled. In addition, the PEAR/MDB2 libraries must be installed. (see instructions and information ). The gd library must be enabled if you plan to use the Digital Library Manager, and the zip library may be installed to allow zip files to be used by the import utilities, but neither of these are absolutely required.
    
Please Note: We do not recommend installing Archon on a Windows (IIS) server. Although it is possible to install it in a Windows environment or another operating system, it was developed and tested on a LAMP (Linux, Apache, MySQL, PHP) server. If you choose to use a Windows server, we strongly advise using Apache or Zend Server over IIS, but please be advised that we are not in a position to offer support or installation assistance.

Server Configuration Recommendations

Archon should work on most preexisting installations of PHP 5.1 or higher, with one of the database servers listed above. If Archon does not install properly or if you are having trouble using any of the features, we recommend the following minimum configuration settings for the php.ini file:

    memory_limit=16M (but more is better and will speed the application up)
    file_uploads=on
    max_execution_time=30 (or higher; Archon may attempt to increase the execution time for certain scripts but will be unable to do if safe mode is on.)
    upload_max_filesize= 8M (or a value larger than the largest files you intend to upload through the digital library manager
    post_max_size=8M (or a value greater than max_upload_filesize

*NOTE: A higher memory limit will be needed if you plan to record very, very lengthy finding aids or upload very large digital objects.

If you are having trouble uploading files, we recommend the following configuration for mysql (in the my.ini or my.cnf file):

max_allowed_packet=8MB (or a value equal to PHP's upload_max_filesize).

For more information regarding installation requirements, or do diagnose installation problems, please visit the Installation/Configuration section of the forum: http://forums.archon.org/viewforum.php?f=4 .

Installing/upgrading overview

A single installation/upgrade packet is provided for Archon 3.x. It is available through the download page on the project website, www.archon.org.

In order for the installer/upgrader to work correctly, you must provide valid credentials so that Archon's scripts can establish a connection to the MySQL or SQL Server where the data is stored. You must provide an accurate server address, database name, login, and passwords, in the file 'config.inc.php' found in the root of the archon distribution.

Once information has been provided in the config.inc.php file, simply place the entire Archon distribution on a web server. If you open a browser to the base address of our installation, an automated installer will being working, and will guide you through the installation process.

If your attempt to install Archon fails, please check with your local system administrator or your web host, as needed, to ensure that you are supplying the correct connection credentials.

Detailed Installation Instructions

A: Prepare to Install Archon

Download the zip file from the project website. Save the file on a local drive.

Create a new blank database on a MySQL or MSSQL database server. The database can be given any name you like. If you do not have authority to create a database on your database server or web host, contact your systems administrator and request that they create a database.

Create a user/password combination for the blank database with ALL privileges to the database, including, SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, and DROP. (Depending on how your database administrator defines "ALL," it may be necessary to manually select each of these privileges.) Archon's web scripts will use this account to access the database, and Archon will not function if the user you create for Archon does not have these credentials.

Note the name of the database server, database name, user login, and user password.

B: Place the Archon files on a webserver

Copy or FTP the Archon folder from the zip file to your webserver. Open config.inc.php and provide the connection credentials.

C: Complete the Web-Based Installation Utility

The web-based installation utility includes 12 steps. The installer attempts to connect to the database, creates tables, and completes initial configuration for your installation of Archon.

To begin, simply navigate to the URL for the root of the archon installation, e.g. http://www.example.org/archon/

Step 1: Click through the welcome screen.

Step 2: Read and accept the open-source license agreement

Step 3: Read the information and click "Next" to continue

Step 4: Test database configuration. If everything is OK, click "Next," otherwise, reconfigure database or correct information supplied in initial (non-web-based) install utility, then run the web-based installation utility again.

Step 5: Installation utility creates database and table structure. After the structure has been created and the page loads completely into the web browser, scroll to the bottom of the screen and click "next."

Step 6: Create sa (Super Administrator) and Administrator user login credentials.

NOTE: The sa account will allow full administrative access to Archon. Unlike the other passwords, the SA password is not stored in the user table. It is a 'failsafe' password allowing access to the Archon administrative (staff) interface in case the user table is corrupted. All passwords are stored as one-way hashes and cannot be read by humans or decrypted.

Step 7: Enter repository information. Only the name is required.

Step 8: Select the packages you wish to install.

NOTE: you can install missing packages or uninstall unneeded packages later if desired.

Step 9 . Depending on the options you selected, one or more package installers will run. Follow instructions on each screen carefully.

Step 10 : Select languages for the administrative interface. Two languages are supplied: English and Spanish. If you are interested in translating Archon to another language, please contact us.

Step 11 : The installer will import previously chosen language XML files.

NOTE: The language import may take a long time. Do not interrupt the process (you will see a message that says "DONE" this when it is finished).

Step 12: Installation is complete. Follow the instructions on the screen carefully to finalize the installation. Archon will not run unless these instructions are completed.

D: Finalize the Installation

Delete or rename the file packages/core/install/install.php.

Your installation of Archon is complete! Go to the web address to start using Archon.
