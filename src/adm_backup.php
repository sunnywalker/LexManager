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
	// adm_backup.php
	//
	// Purpose: Present the administrator with options to export the tables used by LexManager
	// Inputs: none
	//////

	// Check if user is logged in
	session_start();
	if($_SESSION['LM_login'] !== "1") {
		header("Location: adm_login.php");
	}

	// Import configuration
	include('cfg/lex_config.php');

	// Connect to MySQL database
    $dbLink = mysql_connect($LEX_serverName, $LEX_adminUser, $LEX_adminPassword);
    @mysql_select_db($LEX_databaseName) or die("      <p class=\"statictext warning\">Unable to connect to database.</p>\n");
    $charset = mysql_query("SET NAMES utf8");
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
        <script type="text/javascript" src="js/amin.js"></script>
    </head>
    <body>
    	<div id="content">
        	<div id="topbar">
            	<a href="manager.php" class="title">Administration</a><br/>
                <div id="adminnav">
                	<p>• <a href="manager.php">Admin Home</a></p>
                	<p>• <a href="adm_newlexicon.php">New Lexicon</a></p>
                    <p>• <a href="adm_settings.php">Settings</a></p>
                    <p>• <a href="adm_logout.php">Logout</a></p>
                </div>
            </div>
            <div id="main">
	        	<div id="leftbar">
					<?php
						// Retrieve list of available lexicons
                        $queryReply = mysql_query("SELECT `Index_ID`, `Name` FROM `lexinfo` ORDER BY `Name`;");
                        $numTables = @mysql_num_rows($queryReply);
                        $displayBuf = "";

						// Display list of lexicons with links to their individual administration pages
						if(!$numTables) {
							echo("<p>No lexicons found.</p>\n");
						} else {
							for ($i = 0; $i < $numTables; $i++) {
	                            $langID = mysql_result($queryReply, $i, 'Index_ID');
								$langName = mysql_result($queryReply, $i, 'Name');
	                            $displayBuf .= "<p><a href=\"adm_viewlex.php?i=" . $langID . "\" class=\"lexlink\">" . $langName . "</a></p>\n";
							}
							echo($displayBuf);
						}
                    ?>
	            </div>
	            <div id="entryview">
	            	<p class="statictext">On this page you can create backups and exportable versions of your lexicons. Performing regular backups is highly recommended.</p>

					<table class="lex_viewall">
                    	<tr>
                        	<th colspan="2">Lexicon Tables</th>
                        </tr>
                        <?php
							// Retrieve and display list of lexicons with export options
							$displayBuf = "";
							for($i = 0; $i < $numTables; $i++) {
								$displayBuf .= "<tr><td>" . mysql_result($queryReply, $i, 'Name') . "</td>";
								$displayBuf .= "<td><input type=\"button\" class=\"export_sql\" value=\"Export SQL\"></td></tr>";
							}
							echo($displayBuf);
						?>
                    </table>
                    <hr>
                    <table class="lex_viewall">
                    	<tr>
                        	<th colspan="2">All Tables</th>
                        </tr>
                        <?php
							// Retrieve and display list of all tables used by LexManager with export options
							$displayBuf = "";
							$queryReply = mysql_query("SHOW TABLES;");
							for($i = 0; $i < mysql_num_rows($queryReply); $i++) {
								$displayBuf .= "<tr><td>" . mysql_result($queryReply, $i, 0) . "</td>";
								$displayBuf .= "<td><input type=\"button\" class=\"export_sql\" value=\"Export SQL\"></td></tr>";
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

<?php
	// Close database connection
	@mysql_close($dbLink);
?>