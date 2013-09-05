<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

   if (isset($_REQUEST['batch_start'])){

            //Handles the zero condition

            $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
         
           $arrCreators =array_slice($_ARCHON->getAllCreatorsJSON(),$start-1,100,true);
		   header('HTTP/1.0 200 Created');				
				if (empty($arrCreators)) {
					exit ("No matching record(s) found for batch_start=".$_REQUEST['batch_start']);
				}
		    
            array_walk($arrCreators, 'GetRelatedCreators');
			array_walk($arrCreators,'Normalize');
         
			echo ($_ARCHON->bbcode_to_html( json_encode($arrCreators)));
        }
        else
        {
			header('HTTP/1.0 400 Bad Request');
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
        }
} 
else 
{
		header('HTTP/1.0 400 Bad Request');
        echo "Please submit your admin credentials to p=core/authenticate";
}

//FUNCTIONS

function getRelatedCreators($item)
{
    global $_ARCHON;

        $query = "SELECT RelatedCreatorID,CreatorRelationshipTypeID FROM tblCreators_CreatorCreatorIndex WHERE CreatorID=". $item->ID;
        $result = $_ARCHON->mdb2->query($query);

        if(PEAR::isError($result))
        {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        while($row = $result->fetchRow())
        {
            $arrCreatorsrelated [] = $row;
        }

        $result->free();
        $item->CreatorRelationships = $arrCreatorsrelated;
}

function Normalize($item,$key){
	if (isset($item->CreatorRelationships)){
        foreach ($item->CreatorRelationships as &$rel){  
            $rel[RelatedCreatorID] = strval($rel[RelatedCreatorID]);
            $rel[CreatorRelationshipTypeID] = strval($rel[CreatorRelationshipTypeID]);
         }
        } 
	$item->CreatorTypeID = strval($item->CreatorTypeID);
	$item->CreatorSourceID = strval($item->CreatorSourceID);
	$item->ID = strval($item->ID);
	$item->RepositoryID = strval($item->RepositoryID);
	unset($item->LanguageID);
	unset($item->ScriptID);
	unset($item->CreatorType);
	unset($item->CreatorSource);
	unset($item->Repository);
	unset($item->Script);
	unset($item->ToStringFields);
	unset($item->Collections);
	unset($item->Books);
	unset($item->Accessions); 
	unset($item->DigitalContent);
	unset($item->Language);
	unset($item->Creators);
}
?>
