<?php
isset($_ARCHON) or die();

	echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "<br />");
	echo("Full list of record series in Archon <br />");
	echo("<span style='font-size:14px'>");
	echo date("Y-m-d H:i:s");
	echo("</span>");
	echo("</h1>\n");
?>
	

<div >

<?php
if(!$_ARCHON->Error)
   {
     $Characters = range('A','Z');
	 $Characters[] = "#";
	 $count = 0;
	 $publiccount = 0;
	 $arrExtentUnits = $_ARCHON->getAllExtentUnits();
	 
	echo("<table id='list-styled'>");
	echo("<tr>
    <th>Published</th>
	<th>Repository ID</th>
    <th>Record Series Number</th>
    <th>Title</th>
	<th>Inclusive Dates</th>
    <th>Total Extent</th>
	<th class='minimize-info'>Archon system ID</th>
	<th>Content</th>
	<th>Location</th>
	<th>Range</th>
	<th>Section</th>
	<th>Shelf</th>
	<th>Extent</th>
	<th class='minimize-info'>Location ID</th>
	<th>Split content count</th>
	<th>Content first</th>
	<th>Content second</th>
  	</tr>");

	 foreach($Characters as $Char) {  
         //echo("<table>");
		 $arrCollections = $_ARCHON->getCollectionsForChar($Char, false, $_SESSION['Archon_RepositoryID'], array('ID', 'Title', 'SortTitle', 'ClassificationID', 'InclusiveDates', 'CollectionIdentifier', 'RepositoryID', 'Extent', 'ExtentUnitID', 'Enabled'));
         
		

		if(!empty($arrCollections))
		{
         //echo("<div class='listitemhead bold'>$Char</div><br/><br/>\n<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>\n");

         foreach($arrCollections as $objCollection)
         {
            $objCollection->dbLoadLocationEntries();
			if(!empty($objCollection->LocationEntries)){
				foreach($objCollection->LocationEntries as $objLocationEntry){
					echo("<tr>");
					echo("<td>");
					if($objCollection->Enabled)
					{
						$accessible = $objCollection->getString('Enabled');
						if($accessible == 1)
						{
							echo("Yes");
							$publiccount++;
						} else {
							echo("unpublished");
						}
					} else {
						echo("unpublished");
					}
					echo("</td><td>");
					if($objCollection->RepositoryID){
						echo($objCollection->RepositoryID);
					} else {
						echo("none");
					}
					echo("</td><td>");
					if($objCollection->ClassificationID)
					{
						$objCollection->Classification = New Classification($objCollection->ClassificationID);
						//$objCollection->Classification->dbLoad(true);
						echo($_ARCHON->Error);
						echo($objCollection->Classification->toString(LINK_NONE, true, false, true, false) . '/');
					}
					//echo($objCollection->toString(LINK_TOTAL, true, false));
					if($objCollection->CollectionIdentifier)
					{
						echo("<a href='?p=collections/controlcard&amp;id=");
						echo($objCollection->getString('ID'));
						echo("'>");
						echo($objCollection->getString('CollectionIdentifier'));
						echo("</a>");
					}
					echo("</td><td>");
					if($objCollection->Title)
					{
						echo($objCollection->getString('Title'));
					}
					echo("</td><td>");
					if($objCollection->InclusiveDates)
					{
						echo($objCollection->getString('InclusiveDates'));
					}
					echo("</td><td>");
					if($objCollection->Extent)
					{
						echo(preg_replace('/\.(\d)0/', ".$1", $objCollection->getString('Extent')));
						if($objCollection->ExtentUnitID)
						{
							$objCollection->ExtentUnit = $arrExtentUnits[$objCollection->ExtentUnitID];
							echo($_ARCHON->Error);
							echo(" " . $objCollection->getString('ExtentUnit'));
						}
					}
					
					echo("</td><td class='minimize-info'>");
					echo($objCollection->getString('ID'));
					//echo("</div>");
					echo("</td>");

					echo("<td>");
					echo($objLocationEntry->toString(LINK_NONE, false, '</td><td>', true));
					echo("</td>");
					echo("<td class='minimize-info'>");
					echo($objLocationEntry->getString('ID'));
					echo("</td>");
					//split content value on dashes
					$objLocContentStr=$objLocationEntry->getString('Content');
					$objLocContentArray=explode("-",$objLocContentStr);
					echo("<td>");
					echo(count($objLocContentArray));
					echo("</td>");
					echo("<td>");
					echo($objLocContentArray[0]);
					echo("</td>");
					echo("<td>");
					echo($objLocContentArray[1]);
					echo("</td>");
					echo("</tr>\n");
					unset($objLocContentStr,$objLocContentArray);
				}
			} else {
					echo("<tr>");
					echo("<td>");
					if($objCollection->Enabled)
					{
						$accessible = $objCollection->getString('Enabled');
						if($accessible == 1)
						{
							echo("Yes");
							$publiccount++;
						} else {
							echo("unpublished");
						}
					} else {
						echo("unpublished");
					}
					echo("</td><td>");
					if($objCollection->RepositoryID){
						echo($objCollection->RepositoryID);
					} else {
						echo("none");
					}
					echo("</td><td>");
					if($objCollection->ClassificationID)
					{
						$objCollection->Classification = New Classification($objCollection->ClassificationID);
						//$objCollection->Classification->dbLoad(true);
						echo($_ARCHON->Error);
						echo($objCollection->Classification->toString(LINK_NONE, true, false, true, false) . '/');
					}
					//echo($objCollection->toString(LINK_TOTAL, true, false));
					if($objCollection->CollectionIdentifier)
					{
						echo("<a href='?p=collections/controlcard&amp;id=");
						echo($objCollection->getString('ID'));
						echo("'>");
						echo($objCollection->getString('CollectionIdentifier'));
						echo("</a>");
					}
					echo("</td><td>");
					if($objCollection->Title)
					{
						echo($objCollection->getString('Title'));
					}
					echo("</td><td>");
					if($objCollection->InclusiveDates)
					{
						echo($objCollection->getString('InclusiveDates'));
					}
					echo("</td><td>");
					if($objCollection->Extent)
					{
						echo(preg_replace('/\.(\d)0/', ".$1", $objCollection->getString('Extent')));
						if($objCollection->ExtentUnitID)
						{
							$objCollection->ExtentUnit = $arrExtentUnits[$objCollection->ExtentUnitID];
							echo($_ARCHON->Error);
							echo(" " . $objCollection->getString('ExtentUnit'));
						}
					}
					
					echo("</td><td class='minimize-info'>");
					echo($objCollection->getString('ID'));
					//echo("</div>");
					echo("</td>");
					echo("</tr>");
			}
         }

         //echo("</div>\n");
		}
		//echo("</table>");
	 }
	 echo("</table>");
   }
?>

</div>