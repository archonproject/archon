<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

  	if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
  	
  	$start = ($_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

 	 	header('HTTP/1.0 200 Created');
		$arrRep = SetCountry($_ARCHON->getAllRepositories());
		$arrRepBatch = array_slice($arrRep,$start-1,100,true);		
		echo (empty($arrRepBatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrRepBatch));
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
function SetCountry($Rep) {
    global $_ARCHON;
    $arrCountries = $_ARCHON->getAllCountries();  //Country currently broken, look to model in creators lookup
    foreach ($Rep as $repository) {
        $repository->CountryID = $arrCountries[$repository->CountryID]->ISOAlpha3;
    }
	array_walk($Rep, 'RemoveElement');
    return $Rep;
}

function RemoveElement($item, $key){
  	$item->ID = strval($item->ID);
  	//$item->CountryID = strval($item->CountryID);
	unset($item->Administrator);
	unset($item->Country);
	unset($item->TemplateSet);
	unset($item->ResearchFunctionality);
	
}
?>
