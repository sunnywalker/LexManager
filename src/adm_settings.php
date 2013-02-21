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
	// adm_settings.php
	//
	// Purpose: Allow the administrator to change settings affecting LexManager as a whole
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
					<p>• <a href="manager.php">Admin Home</a></p>
					<p>• <a href="adm_newlexicon.php">New Lexicon</a></p>
					<p>• <a href="adm_backup.php">Backup Lexicons</a></p>
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
					<p class="statictext">Here you can configure database and webapp settings and modify themes.</p>

					<p class="statictext warning">Nothing has been implemented here yet.</p>

					<noscript>
						<p class="statictext warning">This page requires that JavaScript be enabled.</p>
					</noscript>
					<br/><br/>
				</div>
			</div>
		</div>
	</body>
</html>
