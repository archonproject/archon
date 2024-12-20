<?php
error_reporting(0);
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

    if (isset($_REQUEST['batch_start'])){
    		$start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
			$arrClassifications = getClassifications();
			$arrClassificationbatch = (array_slice($arrClassifications,$start-1,100,true));

			header('HTTP/1.0 200 Created');				
			if (empty($arrClassificationbatch)) {
				exit ("No matching record(s) found for batch_start=".$_REQUEST['batch_start']);
			}		
        	$arrClassificationbatch = objectToArray($arrClassificationbatch); 
			if ($_ARCHON->db->ServerType == 'MSSQL'AND !$_ARCHON->config->DatabaseEncodingUTF8) {array_walk_recursive($arrClassificationbatch, 'myutf8_encode');}  //fix unicode for MSSQL migrations; function will incorrectly transform mysql unicode

			echo json_encode($arrClassificationbatch);
			}
			else {
				header('HTTP/1.0 400 Bad Request');
				echo "batch_start Not found! Please enter a batch_start and resubmit the request."; 
			}

} else {
	header('HTTP/1.0 400 Bad Request');
    echo "Please submit your admin credentials to p=core/authenticate";
}

//Functions

function getClassifications()
{
    global $_ARCHON;

    $query = "SELECT
                ID,
                ParentID,
                ClassificationIdentifier,
                CreatorID,
                Title,
                Description
                FROM
                tblCollections_Classifications
                ORDER BY ParentID ASC, ID DESC";
    $result = $_ARCHON->mdb2->query($query);

    if(pear_isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }

    while($row = $result->fetchRow())
    {
        $arrClassifications[] = $row;
    }

    $result->free();

    return $arrClassifications;

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