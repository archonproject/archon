<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

//echo print_r($_REQUEST) ;
//echo print_r($_ARCHON);

// echo print_r($arrCountries);

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

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
    echo "Please submit your admin credentials to p=core/authenticate";
}

?>
