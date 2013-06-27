<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){
  if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
        	$start = ($_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
 			echo json_encode(array_slice(SetCountry($_ARCHON->getAllRepositories()),$start-1,100,true));
	} 
    	else {
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
    	}
} else {
    echo "Please submit your admin credentials to p=core/authenticate";
}

function SetCountry($Rep) {
    global $_ARCHON;
    $arrCountries = $_ARCHON->getAllCountries();
    foreach ($Rep as $repository) {
        $repository->Country = $arrCountries[$repository->CountryID]->ISOAlpha3;
    }
	array_walk($Rep, 'RemoveElement');
    return $Rep;
}

function RemoveElement($item, $key){
  
	unset($item->Administrator);
	unset($item->CountryID);
	unset($item->TemplateSet);
	unset($item->ResearchFunctionality);
	
}
?>
