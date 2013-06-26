<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

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
    echo "Please submit your admin credentials to p=core/authenticate";
}
?>
