<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

//Handles the zero condition
        if (isset($_REQUEST['batch_start'])){
                $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

        // pulls Batches of 100 across

                $arrDigitalContent = $_ARCHON->getAllDigitalContent();        
                $arrDigitalContentbatch = (array_slice($arrDigitalContent,$start-1,100,true));
				header('HTTP/1.0 200 Created');				
				if (empty($arrDigitalContentbatch)) {
					exit ("No matching record(s) found for cid=".$_REQUEST['cid']." and batch_start=".$_REQUEST['batch_start']);
				}

                 //Creators
                $arrDigitalContentCreator = getDigitalContentCreator ();

                foreach ($arrDigitalContentCreator as $DigitalRelatedObject)
                {
                    if(array_key_exists($DigitalRelatedObject['DigitalContentID'],$arrDigitalContentbatch)){
                    $arrDigitalContentbatch[$DigitalRelatedObject['DigitalContentID']]->Creators[] = $DigitalRelatedObject['CreatorID'];

                        if($DigitalRelatedObject['PrimaryCreator'] == 1){

                            $arrDigitalContentbatch[$DigitalRelatedObject['DigitalContentID']]->PrimaryCreator = $DigitalRelatedObject['CreatorID'];
                        }
                    }
                }
                //Subjects

                $arrDigitalContentSubjects= getDigitalContentSubjects();

                foreach ($arrDigitalContentSubjects as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['DigitalContentID'],$arrDigitalContentbatch)){
                    $arrDigitalContentbatch[$accessionRelatedObject['DigitalContentID']]->Subjects[] = $accessionRelatedObject['SubjectID'];
                    }
                }
               
                //Files
                $arrDigitalContentFiles = getDigitalContentFile();

                foreach ($arrDigitalContentFiles as $accessionRelatedObject)
                {
                    if(array_key_exists($accessionRelatedObject['DigitalContentID'],$arrDigitalContentbatch)){
                    $arrDigitalContentbatch[$accessionRelatedObject['DigitalContentID']]->Files[] = $accessionRelatedObject['ID'];
                    }
                }
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
					 Normalize($arrDigitalContentbatch);
                     echo $_ARCHON->bbcode_to_html(json_encode($arrDigitalContentbatch));
        }else{
			header('HTTP/1.0 400 Bad Request');
				
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";

        }

} 

else {
	header('HTTP/1.0 400 Bad Request');
				
    echo "Please submit your admin credentials to p=core/authenticate";
}

//FUNCTIONS
function getDigitalContentCreator()
{
    global $_ARCHON;


    $query = "SELECT DigitalContentID,CreatorID,PrimaryCreator FROM tblDigitalLibrary_DigitalContentCreatorIndex";
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

function Normalize($digitalContent)
{
    array_walk_recursive ($digitalContent, 'Normal');

    return $digitalContent;
}

function Normal($item,$key)
{

	$item->ID = strval($item->ID);
	$item->Browsable = strval($item->Browsable);
	$item->CollectionID = strval($item->CollectionID);
	$item->CollectionContentID = strval($item->CollectionContentID);
	$item->HyperlinkURL = strval($item->HyperlinkURL);
	
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
	
	$item->PrimaryCreator = strval($item->PrimaryCreator);
	
    unset($item->Collection);
    unset($item->CollectionContent);
    unset($item->Files);
   // unset($item->PrimaryCreator);
    unset($item->ToStringFields);

}

?>
