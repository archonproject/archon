<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

//echo print_r($_ARCHON) ;
//echo print_r($_ARCHON->AdministrativeInterface);


// echo print_r($arrCountries);


if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {


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

            case 'creatorrelationship';
                echo json_encode(array_values($_ARCHON->getAllCreatorRelationshipTypes()));
                break;

        }

    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}



?>
