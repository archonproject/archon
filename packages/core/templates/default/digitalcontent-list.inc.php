<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

//echo print_r($_REQUEST) ;
//echo print_r($_ARCHON);


// echo print_r($arrCountries);


if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {
//Handles the zero condition
        if ($_REQUEST['batch_start']){
                $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

        // pulls Batches of 100 across


                $arrDigitalContent = $_ARCHON->getAllDigitalContent();

        //echo  print_r($arrAccessions);



                $arrDigitalContentbatch = (array_slice($arrDigitalContent,$start-1,100,true));



                 //Creators
                $arrDigitalContentCreator = getDigitalContentCreator ();

                foreach ($arrDigitalContentCreator as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['DigitalContentID'],$arrDigitalContentbatch)){
                    $arrDigitalContentbatch[$accessionRelatedObject['DigitalContentID']]->Creators[] = $accessionRelatedObject['CreatorID'];
                    }
                }
                //Creators
                //Subjects

                $arrDigitalContentSubjects= getDigitalContentSubjects();

                foreach ($arrDigitalContentSubjects as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['DigitalContentID'],$arrDigitalContentbatch)){
                    $arrDigitalContentbatch[$accessionRelatedObject['DigitalContentID']]->Subjects[] = $accessionRelatedObject['SubjectID'];
                    }
                }
                //Subjects
                //Files

                $arrDigitalContentFiles = getDigitalContentFile();

                foreach ($arrDigitalContentFiles as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['DigitalContentID'],$arrDigitalContentbatch)){
                    $arrDigitalContentbatch[$accessionRelatedObject['DigitalContentID']]->Files[] = $accessionRelatedObject['ID'];
                    }
                }
                 //Files

                     echo json_encode($arrDigitalContentbatch);
        }else{
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";

        }


    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}

function getDigitalContentCreator()
{
    global $_ARCHON;


    $query = "SELECT DigitalContentID,CreatorID FROM tblDigitalLibrary_DigitalContentCreatorIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrDigitalContentCreators [] = $row;

    }

    $result->free();

    return $arrDigitalContentCreators;



}

function getDigitalContentSubjects()
{
    global $_ARCHON;


    $query = "SELECT DigitalContentID,SubjectID FROM tblDigitalLibrary_DigitalContentSubjectIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrDigitalContentSubjects [] = $row;

    }

    $result->free();

    return $arrDigitalContentSubjects;



}
function getDigitalContentFile()
{
    global $_ARCHON;


    $query = "SELECT DigitalContentID,ID FROM tblDigitalLibrary_Files";
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
