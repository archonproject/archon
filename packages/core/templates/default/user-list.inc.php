<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];

if ($_ARCHON->Security->Session->verifysession($session))

{

 if (isset($_REQUEST['batch_start'])) {  // isset accounts for the zero condition
        	
        	$start = ($_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
 			echo json_encode(array_slice(RemoveBad($_ARCHON->getAllUsers()),$start-1,100,true));
	} 
    else {
     echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
    	}
    	
} 

else {
    echo "Please submit your admin credentials to p=core/authenticate";
}

function RemoveBad($Users) {
    global $_ARCHON;
    foreach ($Users as $user) {
        if ($user->IsAdminUser == 0  || $user->ID == -1 || in_array(5, $user->Usergroups)){  //remove public users, sa user, and denied users
            unset($Users[$user->ID]);
        }
    }	
	array_walk($Users, 'RemoveElement');		
    return $Users;
}

function RemoveElement($item, $key){
	//echo "$key holds $item\n";
	//echo print_r($item);
  
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

?>
