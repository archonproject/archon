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
                    $arrCreatorSources= $_ARCHON->getAllCreatorSources();
                    array_walk($arrCreatorSources, 'RemoveCreatorElements');
                	echo json_encode(array_slice($arrCreatorSources,$start-1,100,true));
                	break;
            	
            	case 'extentunits';
                	echo json_encode(array_slice($_ARCHON->getAllExtentUnits(),$start-1,100,true));
                	break; 
		
				case 'processingpriorities';
					$arrprocessingpriorities = $_ARCHON->getAllProcessingPriorities();
					array_walk($arrprocessingpriorities,'RemoveProcessingPriorities');
         	       echo json_encode(array_slice($arrprocessingpriorities,$start-1,100,true));
         	       break;
		
         	 	case 'filetypes';
					$arrfiletypes = $_ARCHON->getAllFileTypes();
					array_walk($arrfiletypes,'RemoveFiletypes');
         	       echo json_encode(array_slice($arrfiletypes,$start-1,100,true));
         	       break;
         	       
        	    case 'materialtypes';
        	        echo json_encode(array_slice($_ARCHON->getAllMaterialTypes(),$start-1,100,true));
        	        break;
        	        
       	  	   	case 'levelcontainers';
						$arrLevelContainer = $_ARCHON->getAllLevelContainers();
						array_walk($arrLevelContainer, 'RemoveContainer');
       	         	echo json_encode(array_slice($arrLevelContainer,$start-1,100,true));
                	break;
                	
            	/*case 'descriptiverules';
                	echo json_encode(array_slice($_ARCHON->getAllDescriptiveRules(),$start-1,100,true));
                	break;*/
                	
            	case 'usergroups';
					$arrusergroups = $_ARCHON->getAllUsergroups();
					array_walk($arrusergroups, 'RemoveUserGroupElements');
                	echo json_encode(array_slice($arrusergroups,$start-1,100,true));
                	break;
                	
            	case 'subjectsources';
            		echo json_encode(array_slice($_ARCHON->getAllSubjectSources(),$start-1,100,true));
                	break;        	
        
            	default;
       				echo ("enum_type not found.  Allowed values:'creatorsources', 'extentunits', 'filetypes', 'materialtypes', 'levelcontainers','usergroups', and 'subjectsources'.  Please try again.");   
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
function RemoveCreatorElements($item,$key){
	unset($item->Citation);
	unset($item->Description); 
}

function RemoveContainer($item,$key){
	unset($item->PrimaryEADLevel);
	unset($item->GlobalNumbering);
}


function RemoveFiletypes($item,$key){
	unset($item->MediaType);
	unset($item->ToStringFields);

}

function RemoveProcessingPriorities($item,$key){
	unset($item->Description);
	unset($item->DisplayOrder);
	unset($item->ToStringFields);

}

?>