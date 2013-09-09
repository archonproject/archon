<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];

if ($_ARCHON->Security->Session->verifysession($session))

{
 if (isset($_REQUEST['batch_start'])) {  // isset accounts for the zero condition
 	
 	 	$start = ( $_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
 	 	header('HTTP/1.0 200 Created');
		$arrUser = $_ARCHON->getAllUsers();
		$arrUserBatch = array_slice(Normalize($arrUser),$start-1,100,true);
		$arrUserBatch = objectToArray($arrUserBatch);	
		array_walk_recursive($arrUserBatch, 'myutf8_encode');	
		echo (empty($arrUserBatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrUserBatch));
 
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

function Normalize($Users) {
    global $_ARCHON;
    foreach ($Users as $user) {
        if ($user->IsAdminUser == 0  || $user->ID == -1 || in_array(5, $user->Usergroups)){  //remove public users, sa user, and denied users
            unset($Users[$user->ID]);
        }
    }	
	array_walk($Users, 'Normal');		
    return $Users;
}

function Normal($item, $key){
	$item->ID = strval($item->ID);
	$item->IsAdminUser = strval($item->IsAdminUser);
	$item->RepositoryLimit = strval($item->RepositoryLimit);
	if (isset($item->Usergroups)){
        foreach ($item->Usergroups as &$ug){  
            $ug = strval($ug);
         }
        } 
        
    if (isset($item->Repositories)){
        foreach ($item->Repositories as &$repos){  
            $repos = strval($repos);
         }
        } 
	
	unset($item->RegisterTime);
	unset($item->Pending);
	unset($item->PendingHash);
	unset($item->LanguageID);
	unset($item->CountryID);
	unset($item->Locked);
	unset($item->PasswordHash);
	unset($item->Password);
	unset($item->Language);
	unset($item->Country);
	unset($item->Permissions);
	unset($item->HomeWidgets);
	unset($item->Cart);
	
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
