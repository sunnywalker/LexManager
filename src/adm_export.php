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
// adm_export.php
//
// Purpose: Generate a table dump and serve it to the administrator to download.
// Inputs:
//     'table' (GET, mandatory): the name of the table to be exported
//     'type' (GET, mandatory): the output format (sql)
//
// This PHP file does not produce an HTML page. It produces a raw text file that will be served directly to the administrator to download.
//////

require_once '_lex_admin.php'; //check for login, check for and load config file and connect to the database

// Retrieve table name from GET
$table = mysql_real_escape_string($_GET['table']);

// Generate HTTP headers telling client browser to download the file produced
header("Content-type: text/plain; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"" . $table . ".sql\"");

// Identify output format
switch($_GET['type']) {
	case 'sql':
		// Export SQL
		// This generates a .sql file containing a sequence of commands that will recreate the table

		// Output to file: Drop the table if it already exists, so that it can be rebuilt
		echo("DROP TABLE IF EXISTS `" . $table . "`;\n");

		// Output to file: Show the table structure and the commands needed to rebuild it
		$queryReply = mysql_query("SHOW CREATE TABLE `" . $table . "`;");
		echo(mysql_result($queryReply, 0, 'Create Table') . ";\n");

		// Output to file: Look the table from writing while it is being rebuilt
		echo("LOCK TABLES `" . $table . "` WRITE;\n");

		// Retrieve all rows from the LexManager table
		$queryReply = mysql_query("SELECT * FROM `" . $table . "`;");

		// Output to file: Insert each row into the new table, 50 rows per SQL command
		$counter = 0;
		$displayBuf = "INSERT INTO `" . $table . "` VALUES ";
		for($i = 0; $i < mysql_num_rows($queryReply); $i++) {
			if($counter == 50) {
				$displayBuf = substr($displayBuf, 0, -1) . ";\n";
				echo($displayBuf);
				$displayBuf = "INSERT INTO `" . $table . "` VALUES ";
				$counter = 0;
			}
			$row = mysql_fetch_row($queryReply);
			$displayBuf .= "(";
			foreach($row as $key => $cell) {
				if($key == 0) {
					$displayBuf .= "'" . mysql_real_escape_string($cell) . "'";
				} else {
					$displayBuf .= ",'" . mysql_real_escape_string($cell) . "'";
				}
			}
			$displayBuf .= "),";
			$counter++;
		}
		echo(substr($displayBuf, 0, -1) . ";\n");

		// Output to file: Unlock the table so it may be written to once again
		echo("UNLOCK TABLES;\n");
		break;
}
