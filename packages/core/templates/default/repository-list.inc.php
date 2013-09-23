<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

  	if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
  	
  	$start = ($_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

 	 	header('HTTP/1.0 200 Created');
		$arrRep = $_ARCHON->getAllRepositories();
		$arrRepBatch = array_slice($arrRep,$start-1,100,true);
		$arrRepBatch = objectToArray($arrRepBatch);
		array_walk ($arrRepBatch, 'Normalize');
		if ($_ARCHON->db->ServerType == 'MSSQL') {array_walk_recursive($arrRepBatch, 'myutf8_encode');}  //fix unicode for MSSQL migrations; function will incorrectly transform mysql unicode
		echo (empty($arrRepBatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrRepBatch));
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

function objectToArray( $object ) {
    if( !is_object( $object ) && !is_array( $object ) ) {
        return $object;
    }
    if( is_object( $object ) ) {
        $object = (array) $object;
    }
    return array_map( 'objectToArray', $object );
}

function Normalize (&$item, $key){
  	$item[ID] = strval($item[ID]);
	unset($item[Administrator]);
	unset($item[Country]);
	unset($item[TemplateSet]);
	unset($item[ResearchFunctionality]);
	
}

function myutf8_encode (&$value) {
	$value = utf8_encode($value);
}
?>
