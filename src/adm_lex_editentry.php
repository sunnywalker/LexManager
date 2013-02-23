<?php
/*
+-----------------------------------------------------------------------------------------------+
| LexManager, Copyright ©2011 Martin Posthumus                                                  |
|                                                                                               |
| This file is part of LexManager, a free and open-source web-based dictionary managament tool. |
| You may redistribute and/or modify LexManager under the terms of the GNU General Public       |
| License (GPL) as published by the Free Software Foundation, either version 3 of the license   |
| or any later version. For the full text of the GPL3 license, please see                       |
| < http://www.gnu.org/licenses/ >.                                                             |
|                                                                                               |
| LexManager is distributed in the hope that it or some part of it will be useful, but comes    |
| with no warranty for loss of data, as per the GPL3 license.                                   |
+-----------------------------------------------------------------------------------------------+
*/

	//////
	// adm_lex_editentry.php
	//
	// Purpose: Open an entry in a lexicon, load all of its data, and allow the administrator to edit the data
	// Inputs:
	//     'i' (GET, mandatory): the index of the lexicon in the "lexinfo" table
	//     'e' (GET, mandatory): the index of the entry within the lexicon's table
	//     multiple (POST, optional): the new data submitted to replace the current row
	//
	//////

	require_once '_lex_admin.php'; //check for login, check for and load config file and connect to the database

	// Ensure mandatory GET inputs are set, else end execution
	if(isset($_GET['i']) && isset($_GET['e'])) {
		$lexIndex = $_GET['i'];
		$entryIndex = $_GET['e'];
	} else {
		die('<p class=\"statictext warning\">Error: Missing index.</p>');
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>LexManager Administration</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/lex_core.css">
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="images/favicon.ico">
		<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/lex.js"></script>
		<script type="text/javascript" src="js/admin.js"></script>
	</head>
	<body>
		<div id="content">
			<div id="topbar">
				<a href="manager.php" class="title">Administration</a><br/>
				<div id="adminnav">
					<p>• <a href="adm_newlexicon.php">New Lexicon</a></p>
					<p>• <a href="adm_backup.php">Backup Lexicons</a></p>
					<p>• <a href="adm_settings.php">Settings</a></p>
					<p>• <a href="adm_logout.php">Logout</a></p>
				</div>
				<table>
					<tr>
						<?php
							// Output navigation such that it is aware of the current lexicon
							$displayBuf = "<td><a href=\"adm_lex_viewall.php?i=" . $lexIndex . "\" class=\"lexlink\">View All Entries</a></td>\n";
							$displayBuf .= "<td><a href=\"adm_lex_newentry.php?i=" . $lexIndex . "\" class=\"lexlink\">Add New Entry</a></td>\n";
							$displayBuf .= "<td><a href=\"adm_lex_lexsettings.php?i=" . $lexIndex . "\" class=\"lexlink\">Display Settings</a></td>\n";
							echo($displayBuf);
						?>
					</tr>
				</table>
			</div>
			<div id="main">
				<div id="leftbar">
					<?php echo getLexiconList($dbLink, 'adm_viewlex.php'); ?>
				</div>
				<div id="entryview">
					<?php
						// Retrieve table structure and create two parallel arrays containing field labels and field types
						$queryReply = mysql_query("SELECT `FieldLabels`, `FieldTypes` FROM `lexinfo` WHERE `Index_ID`=" . $lexIndex . ";");
						$fieldLabelArray = explode("\n", mysql_result($queryReply, 0, 'FieldLabels'));
						$fieldTypeArray = explode("\n", mysql_result($queryReply, 0, 'FieldTypes'));

						// If data was submitted via POST, update the database
						if(isset($_POST['submit'])) {
							// Iterate over submitted fields by referencing the field label array, and create a SQL update command
							$querystring = "UPDATE `" . $curLex . "` SET ";
							foreach($fieldLabelArray as $key => $fieldLabel) {
								if($fieldLabel != "Index_ID") {
									$cleanedFieldLabel = str_replace(' ', '', $fieldLabel);
									$val = str_replace("\r", "", $_POST[$cleanedFieldLabel]);
									$querystring .= "`" . $fieldLabel . "`='" . mysql_real_escape_string($val) . "', ";
								}
							}
							$querystring = substr($querystring, 0, -2) . " WHERE `Index_ID`=" . $entryIndex . ";";
							$queryReply = mysql_query($querystring);

							// If update was successful, update the timestamp of the most recent edit in "lexinfo", else output an error message
							if($queryReply) {
								echo("<p>Entry #" . $entryIndex . " successfully updated.</p>\n");
								mysql_query("UPDATE `lexinfo` SET `DateChanged`=NOW() WHERE `Index_ID`=" . $lexIndex . ";");
							} else {
								echo("<p>Error: Database update failed.</p>\n");
							}
						}
					?>
					<form id="editentry" action="adm_lex_editentry.php?i=<?php echo $lexIndex; ?>&e=<?php echo $entryIndex; ?>" method="post">
						<table class="lex_newentry">
							<?php
								// Get the current row's contents
								$queryreply = mysql_query("SELECT * FROM `" . $curLex . "` WHERE `Index_ID`=" . $entryIndex . ";");
								$displayBuf = "";

								// Iterate over the table structure and generate a form containing the current row's contents pre-loaded
								foreach($fieldLabelArray as $key => $fieldLabel) {
									// For each field, get the data, a "cleaned" label safe for HTML IDs, and create a new HTML table row showing the field label
									$fieldVal = mysql_result($queryreply, 0, $key);
									$cleanFieldLabel = str_replace(' ', '', $fieldLabel);
									$displayBuf .= "<tr><td><label for=\"" . $cleanFieldLabel . "\">" . $fieldLabel . "</label></td>\n";
									// Show field options based on the field type
									switch($fieldTypeArray[$key]) {
										case 'id':
											// If an ID field, show the ID, but do not allow it to be edited
											$displayBuf .= "<td><input type=\"text\" name=\"" . $cleanFieldLabel . "\" disabled=\"disabled\" size=\"50\" value=\"" . $fieldVal . "\"></td>\n";
											break;
										case 'text':
											// If a text field, show a text input field with the current value
											$displayBuf .= "<td><input type=\"text\" name=\"" . $cleanFieldLabel . "\" size=\"50\" value=\"" . $fieldVal . "\"></td>\n";
											break;
										case 'rich':
											// If a rich text field, show a textarea input with the current raw value
											$displayBuf .= "<td><textarea rows=\"5\" cols=\"50\" name=\"" . $cleanFieldLabel . "\">" . $fieldVal . "</textarea></td>\n";
											break;
										case 'list':
											// If a list, show a series of text input fields, each containing one list item, plus a button to add new list items
											$splitList = explode("\n", $fieldVal);
											$displayBuf .= "<td><ol class=\"listinput\" id=\"" . $cleanFieldLabel . "\">";
											for($i = 0; $i < count($splitList); $i++) {
												$displayBuf .= "<li><input type=\"text\" size=\"50\" value=\"" . $splitList[$i] . "\"></li>";
											}
											$displayBuf .= "</ol>\n";
											$displayBuf .= "<input type=\"button\" class=\"addListInput\" value=\"+\"></td>\n";
											break;
										case 'hidden':
											// If a hidden field, show a text input field with the current value
											$displayBuf .= "<td><input type=\"text\" name=\"" . $cleanFieldLabel . "\" size=\"50\" value=\"" . $fieldVal . "\"> (Hidden)</td>\n";
											break;
										default:
											// If none of the above, show an error message
											$displayBuf .= "<td>No input means specified</td></tr>\n";
											break;
									}
								}
								$displayBuf .= "<tr><td></td><td><input type=\"submit\" name=\"submit\" value=\"Submit\"></td></tr></table>";
								echo($displayBuf);
							?>
						</table>
					</form>
					<noscript>
						<p class="statictext warning">This page requires that JavaScript be enabled.</p>
					</noscript>
					<br/><br/>
				</div>
			</div>
		</div>
	</body>
</html>

<?php
	// Close database connection
	@mysql_close($dbLink);
?>