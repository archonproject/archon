<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

 //echo print_r($_REQUEST) ;
 //echo print_r($_ARCHON);


 // echo print_r($arrCountries);
$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    echo json_encode(SetCountry($_ARCHON->getAllUsers()));

} else {
    echo "Please submit your admin credentials to p=core/authenticate";
}


function SetCountry($Rep) {
    global $_ARCHON;
    $arrCountries = $_ARCHON->getAllCountries();
    foreach ($Rep as $repository) {
        //echo print_r($repository->CountryID);
        // echo print_r($repository);
        if ($repository->IsAdminUser == 0){
            unset($Rep[$repository->ID]);
        }
        else{
            $repository->Country = $arrCountries[$repository->CountryID]->ISOAlpha2;

        }
      
    }

	
	array_walk($Rep, 'RemoveElement');
	
	
    return $Rep;
}



function RemoveElement($item, $key){
	//echo "$key holds $item\n";
	//echo print_r($item);
  
	unset($item->RegisterTime);
	unset($item->Pending );
	unset($item->PendingHash );
	unset($item->LanguageID );
	unset($item-> CountryID );
	unset($item->Locked );
	unset($item->PasswordHash );
	unset($item->Password);
	unset($item->Language);
	unset($item->Country);
	unset($item->Persmissions);
	unset($item->HomeWidgets);
	unset($item->Cart);


}

?>
