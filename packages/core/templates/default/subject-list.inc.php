<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
        //Handles the zero condition
        header('HTTP/1.0 200 Created');
        $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
		$arrSubj = $_ARCHON->getAllSubjectsforJSON();
		$arrSubjBatch = array_slice($arrSubj,$start-1,100,true);
		array_walk($arrSubjBatch, 'RemoveSubjElements');			
		echo (empty($arrSubjBatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : $_ARCHON->bbcode_to_html(json_encode($arrSubjBatch)));
    } 
    else {
    	header('HTTP/1.0 400 Bad Request');
        echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
    }
} 
else {
	header('HTTP/1.0 400 Bad Request');
    echo "Please submit your admin credentials to p=core/authenticate";
}

//FUNCTIONS

function RemoveSubjElements($item, $key){  
	unset($item->SubjectType);
	unset($item->SubjectSource);
	unset($item->Collections);
}

?>
