<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();
if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {

      if ($_REQUEST['batch_start']){
    		$start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
	
			$arrClassifications = $_ARCHON->loadTable("tblCollections_Classifications", "Classification", "ID", NULL, NULL, NULL, false, NULL, false);
    
			$arrClassificationbatch = (array_slice($arrClassifications,$start-1,100,true));
			
        	echo json_encode(array_values($arrClassificationbatch));
			}
			else {
				echo "batch_start Not found! Please enter a batch_start and resubmit the request."; 
			}
    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}
?>
