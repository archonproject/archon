<?php

isset($_ARCHON) or die();

//echo print_r($_REQUEST) ;
//echo print_r($_ARCHON);


// echo print_r($arrCountries);


if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {
//Handles the zero condition
        $start = ( $_REQUEST['substart'] < 1 ? 1: $_REQUEST['substart']);

// pulls Batches of 100 across
        echo json_encode(array_slice($_ARCHON->getAllSubjects(),$start-1,100));
    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}



?>
