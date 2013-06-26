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
        $repository->Country = $arrCountries[$repository->CountryID]->ISOAlpha2;
    }

    return $Rep;
}
?>
