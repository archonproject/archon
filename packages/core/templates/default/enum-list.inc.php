<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

  if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
        	$start = ($_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
 			$enumtype =$_REQUEST['enum_type'];
 			
 			
 		
 		  switch ($enumtype) {
            	
            	case 'creatorsources';
                	echo json_encode(array_slice(array_values($_ARCHON->getAllCreatorSources()),$start-1,100,true));
                	break;
            	
            	case 'extentunits';
                	echo json_encode(array_slice(array_values($_ARCHON->getAllExtentUnits()),$start-1,100,true));
                	break; 
		
         	 	case 'filetypes';
         	       echo json_encode(array_slice(array_values($_ARCHON->getAllFileTypes()),$start-1,100,true));
         	       break;
         	       
        	    case 'materialtypes';
        	        echo json_encode(array_slice(array_values($_ARCHON->getAllMaterialTypes()),$start-1,100,true));
        	        break;
        	        
       	  	   	case 'levelcontainers';
       	         	echo json_encode(array_slice(array_values($_ARCHON->getAllLevelContainers()),$start-1,100,true));
                	break;
                	
            	case 'descriptiverules';
                	echo json_encode(array_slice(array_values($_ARCHON->getAllDescriptiveRules()),$start-1,100,true));
                	break;
                	
            	case 'usergroups';
					$arrusergroups = $_ARCHON->getAllUsergroups();
					array_walk($arrusergroups, 'RemoveUserGroupElements');
                	echo json_encode(array_slice($arrusergroups,$start-1,100,true));
                	break;
                	
            	case 'subjectsources';
            		echo json_encode(array_slice(array_values($_ARCHON->getAllSubjectSources()),$start-1,100,true));
                	break;        	
        
            	default;
       				echo ("enum_type not found.  Allowed values:'creatorsources', 'extentunits', 'filetypes', 'materialtypes', 'levelcontainers', 'descriptiverules', 'usergroups', and 'subjectsources'.  Please try again.");   
					break;			
			}	
			 			
		}
 		
    	else {
            echo "batch_start Not found! Please enter a batch_start and resubmit the request.";
    	}
        
} 

else {
    echo "Please submit your admin credentials to p=core/authenticate";
}




function RemoveUserGroupElements($item, $key){
  
	unset($item->Permissions);
	unset($item->DefaultPermissions);
	unset($item->DefaultPermissionsRead);
	unset($item->DefaultPermissionsAdd);
	unset($item->DefaultPermissionsUpdate);
	unset($item->DefaultPermissionsDelete);
	unset($item->DefaultPermissionsDelete);
	unset($item->DefaultPermissionsFullControl);
	unset($item->Users);	
}


?>