<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

//echo print_r($_ARCHON) ;
//echo print_r($_ARCHON->AdministrativeInterface);


// echo print_r($arrCountries);

$session= $_SERVER['HTTP_SESSION'];
if ($_ARCHON->Security->Session->verifysession($session)){


    $enumtype =$_REQUEST['enum_type'];

        switch ($enumtype) {
            case 'creatorsources';
                echo json_encode(array_values($_ARCHON->getAllCreatorSources()));
                break;
            case 'extentunits';
                echo json_encode(array_values($_ARCHON->getAllExtentUnits()));
                break; 
            case 'filetypes';
                echo json_encode(array_values($_ARCHON->getAllFileTypes()));
                break;
            case 'materialtypes';
                echo json_encode(array_values($_ARCHON->getAllMaterialTypes()));
                break;
            case 'levelcontainers';
                echo json_encode(array_values($_ARCHON->getAllLevelContainers()));
                break;
            case 'descriptiverules';
                echo json_encode(array_values($_ARCHON->getAllDescriptiveRules()));
                break;
            case 'creatorrelationshiptypes';
                echo json_encode(array_values($_ARCHON->getAllCreatorRelationshipTypes()));
                break;
            case 'usergroups';
					$arrusergroups = $_ARCHON->getAllUsergroups();
                echo json_encode(removeElement($arrusergroups));
                break;
            case 'subjectsources';
            	echo json_encode(array_values($_ARCHON->getAllSubjectSources()));
                break;         
        	case 'subjecttypes';
                echo $_ARCHON->getSubjectTypeJSONList();
                break; 
            default;
       			echo ("enum_type not found.  Allowed values:'creatorsources', 'extentunits', 'filetypes', 'materialtypes', 'levelcontainers', 'descriptiverules', 'creatorrelationships', 'usergroups', 'subjectsources', and 'subjecttypes'.  Please try again.");   
				break;
        }


} else {
    echo "Please submit your admin credentials to p=core/authenticate";
}



function removeElement($obj){

array_walk_recursive($obj, 'groupUserRemoveElement');

return  $obj;
}




function groupUserRemoveElement($item,$key){
	echo "------\n";
	echo print_r($item);
    echo "------";
	unset($item->DefaultPermissions);
	unset($item->DefaultPersmissionsRead);
	unset($item->DefaultPersmissionsAdd);
	unset($item->DefaultPersmissionsUpdate);
	unset($item->DefaultPersmissionsDelete);
	unset($item->DefaultPersmissionsFullControl);

	

}

?>
