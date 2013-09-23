<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
        //Handles the zero condition
        header('HTTP/1.0 200 Created');
        $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
		$arrSubj = $_ARCHON->getAllSubjectsforJSON();
		$arrSubjBatch = array_slice($arrSubj,$start-1,100,true);
		array_walk($arrSubjBatch, 'Normalize');	  //works recursively, but only objects
		$arrSubjBatch = objectToArray($arrSubjBatch); 
		if ($_ARCHON->db->ServerType == 'MSSQL') {array_walk_recursive($arrSubjBatch, 'myutf8_encode');}  //fix unicode for MSSQL migrations; function will incorrectly transform mysql unicode		
		echo (empty($arrSubjBatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : $_ARCHON->bbcode_to_html(json_encode($arrSubjBatch)));
    } 
    else {
    	header('HTTP/1.0 400 Bad Request');
        echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
    }
} 
else {
	header('HTTP/1.0 400 Bad Request');
    echo "Please submit your admin credentials to p=core/authenticate";
}

//FUNCTIONS

function Normalize($item, $key) {  //don't need to call & reference since this is applied to object
	$item->ID = strval($item->ID);
	$item->SubjectSourceID = strval($item->SubjectSourceID);
	$item->SubjectTypeID = strval($item->SubjectTypeID);
	$item->ParentID = strval($item->ParentID);
	unset($item->SubjectType);
	unset($item->SubjectSource);
	unset($item->Collections);
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

