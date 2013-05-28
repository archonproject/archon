<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

//echo print_r($_ARCHON) ;
//echo print_r($_ARCHON->AdministrativeInterface);


// echo print_r($arrCountries);


if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {

        if ($_REQUEST['batch_start']){

            //Handles the zero condition
            $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

            $arrCreatorsrelated = getrelatedcreators();

            // pulls Batches of 100 across

           // $arrCreators =array_slice($_ARCHON->getAllCreators(),$start-1,100);
            $arrCreators = $_ARCHON->getAllCreators();

            foreach ($arrCreatorsrelated as $creatrel)
             {
                 $arrcreaterel = array($creatrel['RelatedCreatorID']=> $creatrel['CreatorRelationshipTypeID']);
                $arrCreators[$creatrel['CreatorID']]->CreatorRelationships[] = $arrcreaterel;

            }
            echo json_encode(array_slice($arrCreators,$start-1,100));








        }else{
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
        }

    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}

function getrelatedcreators()
{
    global $_ARCHON;


        $query = "SELECT CreatorID,RelatedCreatorID,CreatorRelationshipTypeID FROM tblCreators_CreatorCreatorIndex";
        $result = $_ARCHON->mdb2->query($query);


        if(PEAR::isError($result))
        {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        while($row = $result->fetchRow())
        {
            $arrCreatorsrelated [] = $row;

        }

        $result->free();
        $result->free();
        return $arrCreatorsrelated;



}

?>
