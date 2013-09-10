<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

//Handles the zero condition
       if (isset($_REQUEST['batch_start'])){
                $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
                // pulls Batches of 100 across

                $SearchFlags = $in_SearchFlags ? $in_SearchFlags : SEARCH_ACCESSIONS;

                $arrAccessions = $_ARCHON->searchAccessions('', $SearchFlags, 0, $objCollection->ID);
                
				header('HTTP/1.0 200 Created');
								
                $arrAccessionbatch = (array_slice($arrAccessions,$start-1,100,true));

				if (empty($arrAccessionbatch)) {
					exit ("No matching record(s) found for batch_start=".$_REQUEST['batch_start']);
				}

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

                        if($accessionRelatedObject['PrimaryCreator'] ==1){
                            $arrAccessionbatch[$accessionRelatedObject['AccessionID']]->PrimaryCreator= $accessionRelatedObject['CreatorID'];
                        }
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
					RemoveBad($arrAccessionbatch);
					$arrAccessionbatch = objectToArray($arrAccessionbatch); 
					array_walk_recursive($arrAccessionbatch, 'myutf8_encode');
                    echo $_ARCHON->bbcode_to_html(json_encode($arrAccessionbatch));
       }
       else
       {
				header('HTTP/1.0 400 Bad Request');
                echo "batch_start Not found! Please enter a batch_start and resubmit the request.";

       }

} else 
{
    echo "Please submit your admin credentials to p=core/authenticate";
}

//FUNCTIONS

function getAccessioncreators()
{
    global $_ARCHON;


    $query = "SELECT AccessionID,CreatorID,PrimaryCreator FROM tblAccessions_AccessionCreatorIndex";
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
                Location,
                Content,
                RangeValue,
                Section,
            	Shelf,
                Extent,
                ExtentUnitID
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

function RemoveBad($AccessionContent) {

    array_walk_recursive ($AccessionContent, 'Removefield');

    return $AccessionContent;
}

function Removefield($item,$key){


	$item->ID = strval($item->ID);
	$item->Enabled = strval($item->Enabled);
	$item->ReceivedExtentUnitID = strval($item->ReceivedExtentUnitID);
	$item->UnprocessedExtentUnitID = strval($item->UnprocessedExtentUnitID);
	$item->MaterialTypeID = strval($item->MaterialTypeID);
	$item->ProcessingPriorityID = strval($item->ProcessingPriorityID);
	
		if (isset($item->Creators)){
        foreach ($item->Creators as &$creator){  
            $creator = strval($creator);
         }
        } 

	if (isset($item->Subjects)){
        foreach ($item->Subjects as &$subject){  
            $subject = strval($subject);
         }
        } 
        
    if (isset($item->Classifications)){
        foreach ($item->Classifications as &$class){  
            $class = strval($class);
         }
        } 
    
        
    if (isset($item->CollectionEntries)){
        foreach ($item->CollectionEntries as &$col){  
            $col = strval($col);
         }
        }     
        
	$item->PrimaryCreator = strval($item->PrimaryCreator);
	
	if (isset($item->LocationEntries)){
        foreach ($item->LocationEntries as &$loc){  
            $loc[ExtentUnitID] = strval($loc[ExtentUnitID]);
         }
        } 
	
    unset($item->ReceivedExtentUnit);
	unset($item->UnprocessedExtentUnit);    
	unset($item->ProcessingPriority);
	unset($item->AccessionDateMonth);
	unset($item->AccessionDateDay);
	unset($item->AccessionDateYear);
	unset($item->ExpectedCompletionDateMonth);
	unset($item->ExpectedCompletionDateDay);
	unset($item->ExpectedCompletionDateYear);
    unset($item->MaterialType);
    unset($item->PrimaryCollectionEntry);
    unset($item->ToStringFields);
    $item->Collections = $item->CollectionEntries;
    $item->Locations = $item->LocationEntries;
    unset($item->CollectionEntries);
    unset($item->LocationEntries);
    
}

function objectToArray( $object ) {
    if( !is_object( $object ) && !is_array( $object ) ) {
        return $object;
    }
    if( is_object( $object ) ) {
        $object = (array) $object;
    }
    return array_map( 'objectToArray', $object );
}

function myutf8_encode (&$value) {
	$value = utf8_encode($value);
}

?>
