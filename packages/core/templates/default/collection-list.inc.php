<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

//echo print_r($_ARCHON) ;
//echo print_r($_ARCHON->AdministrativeInterface);
//echo print_r($_REQUEST);

// echo print_r($arrCountries);


$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if ($_REQUEST['batch_start']){

            //Handles the zero condition
            $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);





            // pulls Batches of 100 across





            $arrCollectionbatch=(array_slice($_ARCHON->getAllCollections(),$start-1,100,true));

            //Creators
            $arrCollectionCreator = getCollectioncreators();

            foreach ($arrCollectionCreator as $CollectionRelatedObject)
            {
                if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){
                    $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->Creators[] = $CollectionRelatedObject['CreatorID'];
                   
                }
            }
            //Creators
            //Subjects

            $arrCollectionSubjects= getCollectionSubjects();

            foreach ($arrCollectionSubjects as $CollectionRelatedObject)
            {
                if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){
                    $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->Subjects[] = $CollectionRelatedObject['SubjectID'];

                }
            }
            //Subjects
            //Locations

            $arrCollectionlocations = getCollectionlocations();

            foreach ($arrCollectionlocations as $CollectionRelatedObject)
            {
                if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){
                    $arrcreaterel = $CollectionRelatedObject;
                    $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->LocationEntries[] = array_slice($arrcreaterel,1);

                }
            }
            //Locations

            //DigitalObjects

            $arrCollectionDigitalContentIDs =  getCollectionDigitalContentIDs();

            foreach ($arrCollectionCreator as $CollectionRelatedObject)
            {
                if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){
                    $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->DigitalContent[] = $CollectionRelatedObject['ID'];

                }
            }


             echo json_encode(array_values($arrCollectionbatch));
        }
        else
        {
            echo "batch_start  Not found! and  Please enter a batch_start and resubmit the request.";

        }


} else {
    echo "Please submit your admin credentials to p=core/authenticate";}

function getCollectioncreators()
{
    global $_ARCHON;


    $query = "SELECT CollectionID,CreatorID FROM tblCollections_CollectionCreatorIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrCollectionCreators [] = $row;

    }

    $result->free();

    return $arrCollectionCreators;



}
function getCollectionDigitalContentIDs()
{
    global $_ARCHON;


    $query = "SELECT CollectionID,ID FROM tblDigitalLibrary_DigitalContent";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrCollectionDigitalContentIDs [] = $row;

    }

    $result->free();

    return $arrCollectionDigitalContentIDs;



}
function getCollectionSubjects()
{
    global $_ARCHON;


    $query = "SELECT DISTINCT CollectionID,SubjectID FROM tblCollections_CollectionSubjectIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrCollectionSubjects [] = $row;

    }

    $result->free();

    return $arrCollectionSubjects;



}
function getCollectionlocations()
{
    global $_ARCHON;


    $query = "SELECT
                 CollectionID,
                LocationID,
                Location,
                Description,
                RepositoryLimit,
                Content,
                Shelf,
                Extent,
                Section,
                RangeValue,
                ExtentUnitID
                FROM
                tblCollections_Locations
                INNER JOIN tblCollections_CollectionLocationIndex ON LocationID = tblCollections_Locations.ID
                ";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrCollectionlocations [] = $row;

    }

    $result->free();

    return $arrCollectionlocations;



}



?>
