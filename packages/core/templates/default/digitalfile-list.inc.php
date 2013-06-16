<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();



if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {
//Handles the zero condition
        if ($_REQUEST['batch_start']){
                $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

        // pulls Batches of 100 across


            $arrDigitalContentFiles = getDigitalContentFile();

        //echo  print_r($arrAccessions);



                     echo json_encode(array_values($arrDigitalContentFiles));
        }else{
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";

        }


    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}


function getDigitalContentFile()
{
        global $_ARCHON;


        $query = "SELECT     ID,
                            DefaultAccessLevel,
                            DigitalContentID,
                            Title,
                            Filename,
                            FileTypeID,
                            Size,
                            DisplayOrder FROM tblDigitalLibrary_Files";
        $result = $_ARCHON->mdb2->query($query);


        if(PEAR::isError($result))
        {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        while($row = $result->fetchRow())
        {
            $arrDigitalContentFiles [] = $row;

        }

        $result->free();

        return $arrDigitalContentFiles;



}


?>
