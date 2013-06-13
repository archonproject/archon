<?php
header('Content-Type: application/json');

isset($_ARCHON) or die();

//echo print_r($_ARCHON) ;
//echo print_r($_ARCHON->AdministrativeInterface);
//echo print_r($_REQUEST);

// echo print_r($arrCountries);


if ($_REQUEST['apilogin'] && $_REQUEST['apipassword']) {
    if (!$_ARCHON->Security->verifyCredentials($_REQUEST['apilogin'], $_REQUEST['apipassword'])) {
        $_ARCHON->declareError("Authentication Failed");
    }
    if (!$_ARCHON->Error) {

        if ($_REQUEST['cid']){

            loadCollectionContent();
        }
        else
        {
            echo "cid  Not found! and  Please enter a collection ID and resubmit the request.";

        }

    } else {
        echo "Authentication Failed";
    }
} else {
    echo "Please provide Username and Password";
}


function loadCollectionContent(){


    global $_ARCHON;

    $cid = $_REQUEST['cid'];



        // get all root nodes
       // $arrObjects[] = $_ARCHON->getChildCollectionContent(0, $cid);

        // traverse the path of nodes, adding all content at that level to the array




       /* $i = !$add_ghost_node ? count($arrNodes) : count($arrNodes) + 1;
        $deepest_i = $i - 1;
        $found_leaf = false;*/

        // iterate through array of nodes in bottom up order*/
       // $arrObjects = array_reverse($arrObjects);

    $objCollection = New Collection($_REQUEST['cid']);
    //echo print_r($objCollection);
    $objCollection->dbLoadContentjson();  //optional parameter limits to only one root node
    $out = json_encode (array_values($objCollection->Content));
    echo ($out);

    //echo json_encode(array_values($arrObjects));





}

?>
