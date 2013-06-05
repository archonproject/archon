<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

//echo print_r($_REQUEST) ;
//echo print_r($_ARCHON);

// echo print_r($arrCountries);

if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    	if (!$_ARCHON->Error) {		
			if ($_REQUEST['batch_start']){
			//Handles the zero condition
        	$start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
			// pulls Batches of 100 across
        	echo json_encode(array_slice($_ARCHON->getAllSubjectsforJSON(),$start-1,100,true));
    	} 
    	else {
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
    	}
    }
    else {
        echo "Authentication Failed";
    }
} 
else {
echo "Please provide Username and Password";
}

?>
