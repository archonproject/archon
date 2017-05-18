<?php

isset($_ARCHON) or die();

$query = "SELECT ID,Title,SortTitle FROM tblCollections_Collections WHERE Enabled = 1 ORDER BY SortTitle";
$result = $_ARCHON->mdb2->query($query);
if (pear_isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}
$arrCollections = array();
while ($row = $result->fetchRow())
{
   $arrCollections[$row['ID']]['ID'] = $row['ID'];
   $arrCollections[$row['ID']]['Title'] = $row['Title'];
   $arrCollections[$row['ID']]['SortTitle'] = $row['SortTitle'];
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <title>EAD List</title>
   </head>
   <body>
      <p>
         <?php

         if(!$_ARCHON->Error)
         {
            if(CONFIG_COLLECTIONS_ENABLE_PUBLIC_EAD_LIST)
            {
               foreach($arrCollections as $objCollection)
               {
                  ?>
         <a href='?p=collections/ead&amp;id=<?php echo($objCollection['ID']); ?>&amp;templateset=ead&amp;disabletheme=1&amp;output=<?php echo(encode(formatFileName($objCollection['SortTitle']),ENCODE_HTML)); ?>'><?php echo(encode(trim($objCollection['Title']),ENCODE_HTML)); ?></a><br/>
                  <?php
               }
            }
            else
            {
               echo("Public EAD List not enabled.");
            }
         }

         ?>
      </p>
   </body>
</html>