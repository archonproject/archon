<?php
header('Content-Type: application/json');
isset($_ARCHON) or die();

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){

  if (isset($_REQUEST['batch_start'])){  // isset accounts for the zero condition
        	$start = ($_REQUEST['batch_start'] < 1 ? 1: $_REQUEST['batch_start']);
 			$enumtype =$_REQUEST['enum_type'];
 			header('HTTP/1.0 200 Created');				
			
 		  switch ($enumtype) {
            	
            	case 'countries';
            	
            		$arrEnum = $_ARCHON->getAllCountries();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
                    array_walk($arrEnumbatch, 'RemoveCountries');
                    $arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break;
            		       	
            	case 'creatorsources';
                    $arrEnum = $_ARCHON->getAllCreatorSources();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
                    array_walk($arrEnumbatch, 'RemoveCreators');
                    $arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break;
            	
            	case 'extentunits';
				
				    $arrEnum = $_ARCHON->getAllExtentUnits();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
					$arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break; 
		
				case 'processingpriorities';

					$arrEnum = $_ARCHON->getAllProcessingPriorities();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
                    array_walk($arrEnumbatch, 'RemoveProcessingPriorities');
                    $arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break;
		
         	 	case 'filetypes';

					$arrEnum = $_ARCHON->getAllFileTypes();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
                    array_walk($arrEnumbatch, 'RemoveFileTypes');
                    $arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break;
   
        	    case 'materialtypes';

				    $arrEnum = $_ARCHON->getAllMaterialTypes();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
					$arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break; 
        	        
       	  	   	case 'containertypes';
				
					$arrEnum = getcontainertypes();		
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'NormalizeArray');
					array_walk($arrEnumbatch, 'RemoveContainerTypes');
					$arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break; 
					
            	/*case 'descriptiverules';
                	echo json_encode(array_slice($_ARCHON->getAllDescriptiveRules(),$start-1,100,true));
                	break;*/
                	
            	case 'usergroups';
				
					$arrEnum = $_ARCHON->getAllUsergroups();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
                    array_walk($arrEnumbatch, 'RemoveUserGroups');
                    $arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break;
					
            	case 'subjectsources';
            	
					$arrEnum = $_ARCHON->getAllSubjectSources();
					$arrEnumbatch = array_slice($arrEnum,$start-1,100,true);
					array_walk($arrEnumbatch, 'Normalize');
					$arrEnumbatch = objectToArray($arrEnumbatch);	
					array_walk_recursive($arrEnumbatch, 'myutf8_encode');
					echo (empty($arrEnumbatch) ? "No matching record(s) found for batch_start=" . $_REQUEST['batch_start'] : json_encode($arrEnumbatch));
                	break;
        
            	default;
					
       				echo ("enum_type not found.  Allowed values: 'countries', 'creatorsources', 'extentunits', 'filetypes', 'materialtypes', 'levelcontainers','usergroups', and 'subjectsources'.  Please try again.");   
					break;			
			}	
			 			
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


function Normalize ($item, $key)

{
$item->ID = strval($item->ID);
}

function NormalizeArray (&$item, $key)

{
$item[ID] = strval($item[ID]);
}

function getcontainertypes()
{
 global $_ARCHON;

    $query = "SELECT ID, LevelContainer as ContainerType FROM tblCollections_LevelContainers WHERE PhysicalContainer = '1'";
    $result = $_ARCHON->mdb2->query($query);
    if(PEAR::isError($result))
    {
        trigger_error($result->getMessage(), E_USER_ERROR);
    }
    while($row = $result->fetchRow())
    {
        $arrContentFile [] = $row;
    }
    $result->free();
    return $arrContentFile;
}

function RemoveUserGroups($item, $key){
  
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

function RemoveCreators($item,$key){
	unset($item->Citation);
	unset($item->Description); 
}

function RemoveContainer($item,$key){
	unset($item->PrimaryEADLevel);
	unset($item->GlobalNumbering);
}

function RemoveFiletypes($item,$key){
	unset($item->MediaTypeID);
	unset($item->MediaType);
	unset($item->ToStringFields);

}

function RemoveProcessingPriorities($item,$key){
	unset($item->Description);
	unset($item->DisplayOrder);
	unset($item->ToStringFields);

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