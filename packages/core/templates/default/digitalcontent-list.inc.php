<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

//echo print_r($_REQUEST) ;
//echo print_r($_ARCHON);


// echo print_r($arrCountries);

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

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
            //Languages
            $arrAllLanguages = $_ARCHON->getAllLanguages();
            $arrDCLanguages= getDigitalContentLanguage();
            foreach ($arrDCLanguages as $DCRelatedObject)
            {
                if(array_key_exists($DCRelatedObject['DigitalContentID'],$arrDigitalContentbatch)){


                    $arrcreaterel = $arrAllLanguages[$DCRelatedObject['LanguageID']]->LanguageShort;
                    $arrDigitalContentbatch[$DCRelatedObject['DigitalContentID']]->Languages[] = $arrcreaterel;

                }
            }

                     echo json_encode(Removebad($arrDigitalContentbatch));
        }else{
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";

        }



} else {
    echo "Please submit your admin credentials to p=core/authenticate";
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

function getDigitalContentLanguage()
{
    global $_ARCHON;


    $query = "SELECT DigitalContentID,LanguageID FROM tblDigitalLibrary_DigitalContentLanguageIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrDigitalContentLanguage [] = $row;

    }

    $result->free();

    return $arrDigitalContentLanguage;



}

function RemoveBad($digitalContent)
{
    array_walk_recursive ($digitalContent, 'Removefield');

    return $digitalContent;
}


function Removefield($item,$key)
{


    unset($item->Collection);
    unset($item->CollectionContent);
    unset($item->Files);
    unset($item->ToStringFields);

}

?>
