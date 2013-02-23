<?php
require_once '_common.php'; // Load config and common functions

// Connect to MySQL database
$dbLink = new PDO("mysql:host=$LEX_serverName;dbname=$LEX_databaseName", $LEX_publicUser, $LEX_publicPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES "UTF8"')) or die("<p class=\"statictext warning\">Unable to connect to database.</p>\n");
