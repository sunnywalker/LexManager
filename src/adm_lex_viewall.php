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
	// adm_lex_viewall.php
	//
	// Purpose: Display a table showing the contents of the lexicon
	// Inputs:
	//     'i' (GET, mandatory): the index of the lexicon in the "lexinfo" table
	//     'start' (GET, optional): the starting offset for the first record shown; assumed to be 0 (start from the first record) if not given
	//     'num' (GET, optional): the number of records to show at once; assumed to be 50 if not given
	//
	//////

	require_once '_lex_admin.php'; //check for login, check for and load config file and connect to the database

	// Ensure mandatory GET inputs are set, else end execution
	if (isset($_GET['i'])) {
		$lexIndex = (int) $_GET['i'];
	} else {
		die('<p class=\"statictext warning\">Error: No index provided.</p>');
	}

	// Check if optional GET inputs are set, else give them default values
	if (isset($_GET['start']) && isset($_GET['num'])) {
		$startFrom = mysql_real_escape_string($_GET['start']);
		$maxEntriesDisplayed = mysql_real_escape_string($_GET['num']);
	} else {
		$startFrom = 1;
		$maxEntriesDisplayed = 50;
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
					?>
					<p>Show <input type="text" size="5" id="maxEntriesDisplayed" value="<?php echo($maxEntriesDisplayed); ?>"> entries starting from #<input type="text" size="5" id="startFrom" value="<?php echo($startFrom); ?>">. <input type="button" id="showEntries" value="Go"><input type="hidden" id="lexIndex" value="<?php echo($lexIndex); ?>"></p>
					<table class="lex_viewall">
						<tr>
							<th>View</th>
							<?php
								// Output the table header cells
								$displayBuf = "";
								foreach($fieldLabelArray as $value) {
									$displayBuf .= "<th>" . (($value == "Index_ID") ? "ID" : $value) . "</th>\n";
								}
								echo($displayBuf);
								$fieldCount = count($fieldLabelArray);
							?>
						</tr>
						<?php
							// Retrieve a given number of entries from the database, as defined by $startFrom and $maxEntriesDisplayed
							$queryreply = mysql_query("SELECT * FROM `" . $curLex . "` LIMIT " . ($startFrom - 1) . ", " . $maxEntriesDisplayed . ";");
							$numrows = mysql_num_rows($queryreply);
							$displayBuf = "";

							// Set encoding for multibyte PHP string functions
							mb_internal_encoding("UTF-8");

							// Iterate over the returned entries and print out a table row for each
							for($i = 0; $i < $numrows; $i++) {
								// Create 'View' and 'Edit' links as the first cell of each row
								$displayBuf .= "<tr><td><a class=\"viewlink\" href=\"view.php?i=" . $lexIndex . "&e=" . mysql_result($queryreply, $i, 0) . "\">View</a><br><a class=\"editlink\" href=\"adm_lex_editentry.php?i=" . $lexIndex . "&e=" . ($i + 1) . "\">Edit</a></td>";

								// Iterate over the fields of each row and output their contents, trimming to 100 multibyte characters
								for($j = 0; $j < $fieldCount; $j++) {
									$fieldVal = mysql_result($queryreply, $i, $j);
									if(mb_strlen($fieldVal) > 100) {
										$fieldVal = mb_substr($fieldVal, 0, 100) . "...";
									}
									$displayBuf .= "<td>" . $fieldVal . "</td>";
								}
								$displayBuf .= "</tr>\n";
							}
							echo($displayBuf);
						?>
					</table>

					<noscript>
						<p class="statictext warning">This page requires that JavaScript be enabled.</p>
					</noscript>
					<br/><br/>
				</div>
			</div>
		</div>
	</body>
</html>
