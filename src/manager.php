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
	// manager.php
	//
	// Purpose: The main entry point of the administrator to manage LexManager
	// Inputs: none
	//
	//////

	require_once '_lex_admin.php'; //check for login, check for and load config file and connect to the database
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>LexManager Administration</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/lex_core.css">
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="images/favicon.ico">
		<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/lex.js"></script>
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
			</div>
			<div id="main">
				<div id="leftbar">
					<?php
						// Retrieve list of available lexicons
						$queryReply = $dbLink->query("SELECT `Index_ID`, `Name` FROM `lexinfo` ORDER BY `Name`;");
						$numTables = $dbLink->query("SELECT FOUND_ROWS()")->fetchColumn();
						$displayBuf = "";

						// Display list of lexicons with links to their individual administration pages
						if (!$numTables) {
							echo "<p>No lexicons found.</p>\n";
						} else {
							while ($lang = $queryReply->fetch(PDO::FETCH_OBJ)) {
								$displayBuf .= "<p><a href=\"adm_viewlex.php?i=" . $lang->Index_ID . "\" class=\"lexlink\">" . htmlspecialchars($lang->Name) . "</a></p>\n";
							}
							echo $displayBuf;
						}
					?>
				</div>
				<div id="entryview">
					<p class="statictext">Welcome to the LexManager Administration page.</p>
					<p class="statictext">From here you can control all of the lexicons within LexManager. Select an option from the top right corner to <a href="adm_newlexicon.php">create</a>, import, and <a href="adm_export.php">export</a> lexicons, or select a specific lexicon in the list to the left to see the options available for that particular language.</p>
					<p class="statictext">From a particular lexicon's page you can add, edit, and remove entries or modify the structure and appearance of the lexicon as a whole.</p>
					<?php
						// If no lexicons have yet been created, display a prompt guiding the administrator to the New Lexicon page
						if (!$numTables) {
							$displayBuf = "<p class=\"warning\">It appears you have no lexicons set up. If you would like to set up a new lexicon, please select \"New Lexicon\" above. If you believe this message is in error, check your MySQL and LexManager configurations.</p>";

							echo $displayBuf;
						}
					?>

					<noscript>
						<p class="statictext warning">This page requires that JavaScript be enabled.</p>
					</noscript>
					<br/><br/>
				</div>
			</div>
		</div>
	</body>
</html>
