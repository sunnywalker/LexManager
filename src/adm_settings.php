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
		<title>LexManager Settings</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/lex_core.css">
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="images/favicon.ico">
		<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/lex.js"></script>
	</head>
	<body>
		<div id="content">
			<?php include '_topbar_admin.php'; ?>
			<div id="main">
				<div id="leftbar">
					<?php echo getLexiconList($dbLink, 'adm_viewlex.php'); ?>
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
