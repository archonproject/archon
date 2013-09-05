<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if (isset($_REQUEST['batch_start'])){
    		$start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
			$arrClassifications = $_ARCHON->loadTable("tblCollections_Classifications", "Classification", "ID", NULL, NULL, NULL, false, NULL, false);
			$arrClassificationbatch = (array_slice(RemoveBad($arrClassifications),$start-1,100,true));
			
			header('HTTP/1.0 200 Created');				
			if (empty($arrClassificationbatch)) {
				exit ("No matching record(s) found for batch_start=".$_REQUEST['batch_start']);
			}		
        	
			echo json_encode($arrClassificationbatch);
			}
			else {
				header('HTTP/1.0 400 Bad Request');
				echo "batch_start Not found! Please enter a batch_start and resubmit the request."; 
			}

} else {
	header('HTTP/1.0 400 Bad Request');
    echo "Please submit your admin credentials to p=core/authenticate";
}

//Functions

function RemoveBad($Classification) {
    
	array_walk($Classification, 'RemoveElement');		
    return $Classification;
}
function RemoveElement($item,$key){
	$item->ID = strval($item->ID);
	$item->ParentID = strval($item->ParentID);
	$item->CreatorID = strval($item->CreatorID);
    unset($item->Parent );
	unset($item->Creator );
	unset($item->Collections);
	unset($item->Classifications);
	unset($item->ToStringFields);
}
?>