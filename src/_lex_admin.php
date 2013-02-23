<?php
session_start();
if ($_SESSION['LM_login'] !== "1" && basename($_SERVER['PHP_SELF']) !== 'adm_login.php') {
	header("Location: adm_login.php");
}

require_once '_common.php'; // Load config and common functions

// Connect to MySQL database
$dbLink = new PDO("mysql:host=$LEX_serverName;dbname=$LEX_databaseName", $LEX_adminUser, $LEX_adminPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES "UTF8"')) or die("<p class=\"statictext warning\">Unable to connect to database.</p>\n");

/**
 * Create the `lexinfo` table if it doesn't exist.
 *
 * @param  PDO    $dbLink  Link to the database connection
 * @return null
 * @version 2013-02-21.00
 * @author Sunny Walker <swalker@hawaii.edu>
 */
function ensureLexInfoExists(PDO &$dbLink) {
	if (!$dbLink instanceof PDO) {
		die("<p class=\"statictext warning\">There was a problem checking on the lexinfo table.</p>");
	}
	$queryReply = $dbLink->query("SHOW tables LIKE 'lexinfo'");
	$numRows = $dbLink->query("SELECT FOUND_ROWS()")->fetchColumn();
	if (!$numRows) {
		// If 'lexinfo' does not exist, create it
		$dbLink->query("CREATE TABLE `lexinfo` (`Index_ID` int(6) NOT NULL AUTO_INCREMENT, `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL, `Alphabet` text COLLATE utf8_unicode_ci NOT NULL, `Collation` text COLLATE utf8_unicode_ci NOT NULL, `Count` int(6) NOT NULL, `FieldTypes` text COLLATE utf8_unicode_ci NOT NULL, `FieldLabels` text COLLATE utf8_unicode_ci NOT NULL, `SearchableFields` text COLLATE utf8_unicode_ci NOT NULL, `DateCreated` datetime NOT NULL, `DateChanged` datetime NOT NULL, PRIMARY KEY (`Index_ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
	}
} // ensureLexInfoExists()

