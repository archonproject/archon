<?php
/**
 * Created by JetBrains PhpStorm.
 * User: randy
 * Date: 6/22/13
 * Time: 11:20 AM
 * To change this template use File | Settings | File Templates.
 */

isset($_ARCHON) or die();


//$_ARCHON->Security->Session->destroy();

	if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Archon Migration"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You must enter a valid login ID and password to access this resource\n';
    exit;
} else {
    
		
			if (!$_ARCHON->Security->verifyCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
				$_ARCHON->declareError("Authentication Failed");
				echo "Authentication Failed";
			}
			else {
               
					if (!$_ARCHON->Security->Session->User->IsAdminUser)
						{

							echo "Please Use an Administrative Account";
						}
					else{
						//echo array('session'=> $_ARCHON->Security->Session->Hash);
						echo json_encode(array('session'=>session_id()));
						}
			}
		}
		
		

 