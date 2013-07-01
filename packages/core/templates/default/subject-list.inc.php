<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

//echo print_r($_REQUEST) ;
//echo print_r($_ARCHON);

// echo print_r($arrCountries);

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
        	//Handles the zero condition
        	$start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
			// pulls Batches of 100 across
        	echo json_encode(array_slice(RemoveBad($_ARCHON->getAllSubjectsforJSON()),$start-1,100,true));
    	} 
    	else {
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
    	}


} 
else {
    echo "Please submit your admin credentials to p=core/authenticate";
}
function RemoveBad($Subjects) {
    
	array_walk($Subjects, 'RemoveElement');		
    return $Subjects;
}

function RemoveElement($item, $key){
	//echo "$key holds $item\n";
	//echo print_r($item);
  
	unset($item->SubjectType);
	unset($item->SubjectSource);
	unset($item->Collections);

}

?>
