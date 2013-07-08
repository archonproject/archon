<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

//echo print_r($_ARCHON) ;
//echo print_r($_ARCHON->AdministrativeInterface);
//echo print_r($_REQUEST);

// echo print_r($arrCountries);



$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if ($_REQUEST['cid']){
            if ($_REQUEST['batch_start']){
                $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
                $arrout=loadCollectionContent($start);

                //Creators
                $arrCollectionContentCreator = getCollectionContentcreators();



                foreach ($arrCollectionContentCreator as $CollectionContentRelatedObject)
                {
                    extract($CollectionContentRelatedObject);


                    // Multi array  special process probably need to revisit

                    if(isset($arrout['0'][$CollectionContentID])){

                        $arrout['0'][$CollectionContentID]->Creators[] = $CollectionContentRelatedObject['CreatorID'];

                    }
                }

                //Subjects
                $arrCollectionContentSubjects= getCollectionContentSubjects();

                foreach ($arrCollectionContentSubjects as $CollectionContentRelatedObject)
                {
                    extract($CollectionContentRelatedObject);
                    if (isset($arrout['0'][$CollectionContentID])){

                        $arrout['0'][$CollectionContentRelatedObject['CollectionContentID']]->Subjects[] = $CollectionContentRelatedObject['SubjectID'];

                    }
                }
               echo json_encode(RemoveBad($arrout));

            }
            else
            {
                echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
            }
        }
        else
        {
            echo "cid  Not found! and  Please enter a collection ID and resubmit the request.";

        }


} else {
    echo "Please submit your admin credentials to p=core/authenticate";
}


function loadCollectionContent($start){


    global $_ARCHON;

    $cid = $_REQUEST['cid'];




    $objCollection = New Collection($_REQUEST['cid']);
    //echo print_r($objCollection);
    $objCollection->dbLoadContentjson();  //optional parameter limits to only one root node

    $arrContent[]= array_slice($objCollection->Content,$start-1,100,true);
    $arrDisplay[]= array();

    foreach ( $arrContent as $contentObj)
    {
        if ($contentObj['ParentID'] == 0)
        {// Top Node Loaded first
           array_unshift($arrDisplay,$contentObj);
        }
        else
        {//if parent loaded the Add
            if($arrDisplay[$contentObj->ParentID]){
                array_push($arrDisplay,$contentObj);
            }
            else{//if no parent find parent added parent add node remove parent from heap to avoid duplication
                array_push($arrDisplay,$arrContent[$contentObj->ParentID]);
                array_push($arrDisplay,$contentObj);
                unset($arrContent[$contentObj->ParentID]); //remove the already added parent  to stop duplication.
            }


        }
    }

return ($arrDisplay);

}
function getCollectionContentcreators()
{
    global $_ARCHON;


    $query = "SELECT CollectionContentID,CreatorID FROM tblCollections_CollectionContentCreatorIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrCollectionContentCreators [] = $row;

    }

    $result->free();

    return $arrCollectionContentCreators;

}

function getCollectionContentSubjects()
{
    global $_ARCHON;


    $query = "SELECT DISTINCT CollectionContentID,SubjectID FROM tblCollections_CollectionContentSubjectIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrCollectionContentSubjects [] = $row;

    }

    $result->free();

    return $arrCollectionContentSubjects;
}

function RemoveBad($CollectionContent) {

    array_walk_recursive ($CollectionContent, 'Removefield');

    return $CollectionContent;
}

function Removefield($item,$key){
    unset($item->Collection);
    unset($item->LevelContainer);
    unset($item->Parent);
    unset($item->Content);
    unset($item->DigitalContent);
    unset($item->ToStringFields);
    
    if (isset($item->UserFields)){
         foreach ($item->UserFields as $UserField){
              unset($UserField->ContentID);
              unset($UserField->ToStringFields);
              unset($UserField->Content);	     
              $UserField->NoteType = $UserField->EADElement->EADTag;
        	  unset($UserField->EADElementID);
        	  unset($UserField->EADElement);
         }
    $item->Notes = $item->UserFields;
    unset($item->UserFields);
    }

}

?>
