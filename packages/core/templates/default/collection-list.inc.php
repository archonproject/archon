<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if (isset($_REQUEST['batch_start'])){

            //Handles the zero condition
            $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
		
            // pulls Batches of 100 across

            $arrCollectionbatch=(array_slice(RemoveBad($_ARCHON->getAllCollections()),$start-1,100,true));
			header('HTTP/1.0 200 Created');				
			if (empty($arrCollectionbatch)) {
				exit ("No matching record(s) found for batch_start=".$_REQUEST['batch_start']);
			}
			
            //Creators
            $arrCollectionCreator = getCollectioncreators();

            foreach ($arrCollectionCreator as $CollectionRelatedObject)
            {
                if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){
                    $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->Creators[] = $CollectionRelatedObject['CreatorID'];
                   if($CollectionRelatedObject['PrimaryCreator'] ==1){
						$arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->PrimaryCreator= $CollectionRelatedObject['CreatorID'];
				   
				   }
                }
            }

            //Subjects
            $arrCollectionSubjects= getCollectionSubjects();

            foreach ($arrCollectionSubjects as $CollectionRelatedObject)
            {
                if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){
                    $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->Subjects[] = $CollectionRelatedObject['SubjectID'];

                }
            }
            
           
            //Languages
        $arrAllLanguages = $_ARCHON->getAllLanguages();
        $arrCollectionLanguages= getCollectionLanguages();
        foreach ($arrCollectionLanguages as $CollectionRelatedObject)
        {
            if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){


                $arrcreaterel = $arrAllLanguages[$CollectionRelatedObject['LanguageID']]->LanguageShort;
                $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->Languages[] = $arrcreaterel;

            }
        }

 //Locations

            $arrCollectionlocations = getCollectionlocations();

                   foreach ($arrCollectionlocations as $CollectionRelatedObject)
        {
          
            if(array_key_exists($CollectionRelatedObject['CollectionID'],$arrCollectionbatch)){
                $arrcreaterel = $CollectionRelatedObject;
                $arrCollectionbatch[$CollectionRelatedObject['CollectionID']]->Locations[] = array_slice($arrcreaterel,1);

            }
        }


        echo $_ARCHON->bbcode_to_html(json_encode(($arrCollectionbatch)));
        }
        else
        {
			header('HTTP/1.0 400 Bad Request');
            echo "batch_start  Not found! and  Please enter a batch_start and resubmit the request.";
        }


} else {
    echo "Please submit your admin credentials to p=core/authenticate";}

//FUNCTIONS

function getCollectioncreators()
{
    global $_ARCHON;


    $query = "SELECT CollectionID,CreatorID,PrimaryCreator FROM tblCollections_CollectionCreatorIndex";
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

function getCollectionLanguages()
{
    global $_ARCHON;


    $query = "SELECT CollectionID,LanguageID FROM tblCollections_CollectionLanguageIndex";
    $result = $_ARCHON->mdb2->query($query);


    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrCollectionLanguages [] = $row;

    }

    $result->free();

    return $arrCollectionLanguages;
}


function getCollectionlocations()
{
    global $_ARCHON;


    $query = "SELECT
                CollectionID,
                Location,
                Content,
                RangeValue,
                Section,
            	Shelf,
                Extent,
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


function RemoveBad($CollectionFields) {
    
	array_walk($CollectionFields, 'Removefield');		
    return $CollectionFields;
}

function Removefield($item,$key){
	unset($item->AcquisitionDateMonth);
	unset($item->AcquisitionDateDay);
	unset($item->AcquisitionDateYear);
	unset($item->PublicationDateMonth);
	unset($item->PublicationDateDay);
	unset($item->PublicationDateYear);
	unset($item->Content);
	unset($item->Languages);
	unset($item->Books);
	unset($item->Repository);
	unset($item->Classification);
	unset($item->ExtentUnit);
	unset($item->MaterialType);
	unset($item->FindingLanguage);
	unset($item->DescriptiveRules);
	unset($item->PrimaryCreators);
	unset($item->ToStringFields);
	unset($item->ignoreCart);
	unset($item->DigitalContent);
	unset($item->LocationEntries);
}

?>
