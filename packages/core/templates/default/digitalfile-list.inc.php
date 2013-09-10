<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
 if ($_ARCHON->Security->Session->verifysession($session)){
  
//Handles the zero condition
        if (isset($_REQUEST['batch_start'])){
                $start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);

        // pulls Batches of 100 across
        $arrDigitalContentFiles =  array_slice(getDigitalContentFile(),$start-1,100,true);
		
		header('HTTP/1.0 200 Created');				
				if (empty($arrDigitalContentFiles)) {
					exit ("No matching record(s) found for batch_start=".$_REQUEST['batch_start']);
				}
	 	array_walk($arrDigitalContentFiles, 'Normalize');
	 	$arrDigitalContentFiles = objectToArray($arrDigitalContentFiles); 
		array_walk_recursive($arrDigitalContentFiles, 'myutf8_encode');
        echo json_encode(array_values($arrDigitalContentFiles));
		
        }else{
		header('HTTP/1.0 400 Bad Request');
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
        }  
} else {
	header('HTTP/1.0 400 Bad Request');
    echo "Please submit your admin credentials to p=core/authenticate";
}

function getDigitalContentFile()
{
        global $_ARCHON;

        $query = "SELECT    ID,
                            DefaultAccessLevel as AccessLevel,
                            DigitalContentID,
                            Title,
                            Filename,
                            FileTypeID,
                            Size as Bytes,
                            DisplayOrder FROM tblDigitalLibrary_Files";
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

function Normalize (&$item, $key)

{
$item[ID] = strval($item[ID]);
$item[AccessLevel] = strval($item[AccessLevel]);
$item[DigitalContentID] = strval($item[DigitalContentID]);
$item[FileTypeID] = strval($item[FileTypeID]);
$item[Bytes] = strval($item[Bytes]);
$item[DisplayOrder] = strval($item[DisplayOrder]);
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
