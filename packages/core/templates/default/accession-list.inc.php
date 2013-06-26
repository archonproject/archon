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

                $SearchFlags = $in_SearchFlags ? $in_SearchFlags : SEARCH_ACCESSIONS;

                $arrAccessions = $_ARCHON->searchAccessions('', $SearchFlags, 0, $objCollection->ID);
                //echo  print_r($arrAccessions);



                $arrAccessionbatch = (array_slice($arrAccessions,$start-1,100,true));


                //Collections and Classifications
                $arrAccessionCollection = getAccessioncollections();

                foreach ($arrAccessionCollection as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['AccessionID'],$arrAccessionbatch)){
                    $arrAccessionbatch[$accessionRelatedObject['AccessionID']]->CollectionEntries[] = $accessionRelatedObject['CollectionID'];
                    $arrAccessionbatch[$accessionRelatedObject['AccessionID']]->Classifications[] = $accessionRelatedObject['ClassificationID'];
                    }
                }
                //Collections and Classifications

                 //Creators
                $arraccessCreator = getAccessioncreators();

                foreach ($arraccessCreator as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['AccessionID'],$arrAccessionbatch)){
                    $arrAccessionbatch[$accessionRelatedObject['AccessionID']]->Creators[] = $accessionRelatedObject['CreatorID'];
                    }
                }
                //Creators
                //Subjects

                $arrAccessSubjects= getAccessionSubjects();

                foreach ($arrAccessSubjects as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['AccessionID'],$arrAccessionbatch)){
                    $arrAccessionbatch[$accessionRelatedObject['AccessionID']]->Subjects[] = $accessionRelatedObject['SubjectID'];
                    }
                }
                //Subjects
                //Locations

                $arrAccesslocations = getAccessionlocations();

                foreach ($arrAccesslocations as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['AccessionID'],$arrAccessionbatch)){
                    $arrAccessionbatch[$accessionRelatedObject['AccessionID']]->LocationEntries[] = array_slice($accessionRelatedObject,1);
                    }
                }
                 //Locations


                     echo json_encode(array_values($arrAccessionbatch));
       }else{
                echo "batch_start Not found! Please enter a batch_start and resubmit the request.";

       }



} else {
    echo "Please submit your admin credentials to p=core/authenticate";
}

function getAccessioncreators()
{
    global $_ARCHON;


    $query = "SELECT AccessionID,CreatorID FROM tblAccessions_AccessionCreatorIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arraccessCreators [] = $row;

    }

    $result->free();

    return $arraccessCreators;



}
function getAccessioncollections()
{
    global $_ARCHON;


    $query = "SELECT AccessionID,CollectionID,ClassificationID FROM tblAccessions_AccessionCollectionIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrAccessioncollection [] = $row;

    }

    $result->free();

    return $arrAccessioncollection;



}
function getAccessionSubjects()
{
    global $_ARCHON;


    $query = "SELECT AccessionID,SubjectID FROM tblAccessions_AccessionSubjectIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrAccessSubjects [] = $row;

    }

    $result->free();

    return $arrAccessSubjects;



}
function getAccessionlocations()
{
    global $_ARCHON;


    $query = "SELECT
                AccessionID,
                LocationID,
                Location,
                Description,
                Content,
                RangeValue,
                Section,
                RepositoryLimit
                FROM
                tblAccessions_AccessionLocationIndex
                INNER JOIN tblCollections_Locations ON tblAccessions_AccessionLocationIndex.LocationID = tblCollections_Locations.ID";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrAccesslocations [] = $row;

    }

    $result->free();

    return $arrAccesslocations;



}


?>
