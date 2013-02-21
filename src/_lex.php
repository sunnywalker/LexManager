<?php
// Import configuration
if (!file_exists('cfg/lex_config.php')) {
	die("<p class=\"statictext warning\">You are missing a configuration file. You must have a valid configuration file to use LexManager. Go to the <a href=\"adm_setup.php\">Configuration Setup</a> page to create one.</p>");
} else {
	require_once 'cfg/lex_config.php';
}

// Connect to MySQL database
$dbLink = new PDO("mysql:host=$LEX_serverName;dbname=$LEX_databaseName", $LEX_publicUser, $LEX_publicPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES "UTF8"')) or die("<p class=\"statictext warning\">Unable to connect to database.</p>\n");
