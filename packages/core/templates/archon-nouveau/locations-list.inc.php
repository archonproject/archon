<?php
header('Content-Type: application/json');
$arrLocRep;
isset($_ARCHON) or die();

//echo print_r($_ARCHON) ;
//echo print_r($_ARCHON->AdministrativeInterface);


// echo print_r($arrCountries);


if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {

       // if ($_REQUEST['batch_start']){

            //Handles the zero condition
          //  $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

            // pulls Batches of 100 across
        $arrLocRep= getLocRep();

        $arrLocations = $_ARCHON->getAllLocations();

        foreach($arrLocRep as $loc)
        {
            $arrLocations[$loc['LocationID']]->RelatedRepositoryIDs[] =$loc['RepositoryID'];

        }

        echo json_encode(array_values( $arrLocations));
           // echo json_encode($_ARCHON->getAllLocations());

       // }else{
         //   echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
       // }

    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}



Function getLocRep()
{ global $_ARCHON;
    static $prep = NULL;
    if(!isset($prep))
    {
        $query = "SELECT LocationID,RepositoryID FROM tblCollections_LocationRepositoryIndex";
        $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

        while($row = $result->fetchRow())
        {
            $arrLocRep [] = $row;
           // $this->Repositories[$row['ID']] = New Repository($row);
           /// $this->RelatedRepositoryIDs[] = $row['ID'];
        }

    $result->free();
    $result->free();
    return $arrLocRep;
}

}
?>
