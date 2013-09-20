<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

   if (isset($_REQUEST['batch_start'])){
        	$start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
         
           	$arrCreators =array_slice($_ARCHON->getAllCreatorsJSON(),$start-1,100,true);
		   	header('HTTP/1.0 200 Created');				
				if (empty($arrCreators)) {
					exit ("No matching record(s) found for batch_start=".$_REQUEST['batch_start']);
				}
            array_walk($arrCreators, 'GetRelatedCreators');
			array_walk($arrCreators,'Normalize');
			$arrCreators = objectToArray($arrCreators);
			array_walk_recursive($arrCreators, 'myutf8_encode');		
			echo ($_ARCHON->bbcode_to_html(json_encode($arrCreators)));
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

        $query = "SELECT RelatedCreatorID,CreatorRelationshipTypeID, Description FROM tblCreators_CreatorCreatorIndex WHERE CreatorID=". $item->ID;
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
    
    switch ($item->CreatorTypeID)  ////if miscoded  set to right value to allow migration
    {
    	case '19';		//keep permitted values untouched
		case '20';
		case '21';
		case '22';
		case '23';
		break;
		
    	case '27';		//corporate
    	case '26';
    	case '57';						
   	 	$item->CreatorTypeID = "22";
    	break;

    	case '24';		// personal
    	case '54';						
   	 	$item->CreatorTypeID = "19";
    	break;
    		   		
    	default;		//set anything else to corporate
    	$item->CreatorTypeID = "22";
    	break;
     }    
	
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

function objectToArray( $object ) {
    if( !is_object( $object ) && !is_array( $object ) ) {
        return $object;
    }
    if( is_object( $object ) ) {
        $object = (array) $object;
    }
    return array_map( 'objectToArray', $object );
}

function myutf8_encode (&$value)

{
	$value = utf8_encode($value);
}

?>
