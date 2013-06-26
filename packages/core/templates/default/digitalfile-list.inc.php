<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();
//$_ARCHON->Security->Session->destroy();
//echo print_r($_SERVER);

$session= $_SERVER['HTTP_SESSION'];
 if ($_ARCHON->Security->Session->verifysession($session)){
  
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
    echo "Please submit your admin credentials to p=core/authenticate";
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
