<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

//echo print_r($_REQUEST) ;
//echo print_r($_ARCHON);


// echo print_r($arrCountries);

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

//Handles the zero condition
    if (isset($_REQUEST['fileid'])){



        $arrfileblob = (getfileblobbyID());

           // echo print_r(base64_encode($arrfileblob[0]['FileContents']));
                    if (isset($arrfileblob)){
                        $arrfileblob =array_values($arrfileblob);
                        $arrfileblob[0]['FileContents']=base64_encode($arrfileblob[0]['FileContents']);
                       echo json_encode(array_values($arrfileblob));
                    }
                    else {

                        echo "Could not locate File with that ID.\n";
                    }

        }else{
            echo "fileid  Not found! Please enter a fileid and resubmit the request.";

        }



} else {
    echo "Please submit your admin credentials to p=core/authenticate";
}

function getfileblobbyID()
{
    global $_ARCHON;
$ID = $_REQUEST['fileid'];


    $query = "SELECT ID,FileContents FROM tblDigitalLibrary_Files WHERE ID = ?";
    $prep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_RESULT);
    $result = $prep->execute(array($ID));




    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }




    while($row = $result->fetchRow())
    {
        $arrContentFile [] = $row;

    }

    $result->free();

    return $arrContentFile;



}




?>
