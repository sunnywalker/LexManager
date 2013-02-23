<?php
// Import configuration or die with setup link
if (!file_exists('cfg/lex_config.php')) {
	die("<p class=\"statictext warning\">You are missing a configuration file. You must have a valid configuration file to use LexManager. Go to the <a href=\"adm_setup.php\">Configuration Setup</a> page to create one.</p>");
} else {
	include 'cfg/lex_config.php';
}

/**
 * Convert a language name into its ASCII counterpart for use as a database table name.
 *
 * @param  string $langName  UTF-8 name of the language
 * @return string
 * @version 2013-02-21.00
 * @author Sunny Walker <swalker@hawaii.edu>
 */
function langNameToTableName($langName) {
	$chars = array(
		'À'=>'A', 'à'=>'a',
		'Á'=>'A', 'á'=>'a',
		'Â'=>'A', 'â'=>'a',
		'Ã'=>'A', 'ã'=>'a',
		'Ä'=>'A', 'ä'=>'a',
		'Å'=>'A', 'å'=>'a',
		'Ā'=>'A', 'ā'=>'a',
		'Æ'=>'A', 'æ'=>'a',
		'Þ'=>'B', 'þ'=>'b',
		'Č'=>'C', 'č'=>'c',
		'Ć'=>'C', 'ć'=>'c',
		'Ç'=>'C', 'ç'=>'c',
		'Đ'=>'Dj', 'đ'=>'dj',
		'È'=>'E', 'è'=>'e',
		'É'=>'E', 'é'=>'e',
		'Ê'=>'E', 'ê'=>'e',
		'Ë'=>'E', 'ë'=>'e',
		'Ē'=>'E', 'ē'=>'e',
		'Ì'=>'I', 'ì'=>'i',
		'Í'=>'I', 'í'=>'i',
		'Î'=>'I', 'î'=>'i',
		'Ï'=>'I', 'ï'=>'i',
		'Ī'=>'I', 'ī'=>'i',
		'Ñ'=>'N', 'ñ'=>'n',
		'Ò'=>'O', 'ò'=>'o',
		'Ó'=>'O', 'ó'=>'o',
		'Ô'=>'O', 'ô'=>'o',
		'Õ'=>'O', 'õ'=>'o',
		'Ö'=>'O', 'ö'=>'o',
		'Ō'=>'O', 'ō'=>'o',
		'Ø'=>'O', 'ø'=>'o',
		'ð'=>'o',
		'Ŕ'=>'R', 'ŕ'=>'r',
		'Š'=>'S', 'š'=>'s',
		'ß'=>'Ss',
		'Ù'=>'U', 'ù'=>'u',
		'Ú'=>'U', 'ú'=>'u',
		'Û'=>'U', 'û'=>'u',
		'Ü'=>'U', 'ü'=>'u',
		'Ū'=>'U', 'ū'=>'u',
		'Ý'=>'Y', 'ý'=>'y',
		'Ÿ'=>'y', 'ÿ'=>'y',
		'Ž'=>'Z', 'ž'=>'z',
		'ʻ'=>'', '\''=>'', '"'=>'',
		' '=>''
	);

	return strtr($langName, $chars);
} // langNameToTableName()

/**
 * Build the list of lexicons for the sidebar.
 *
 * @param  PDO    $dbLink   Database connection PDO object
 * @param  string $pageURL  Page the lexicons link to (default: ./)
 * @return string
 * @version 2013-02-21.00
 * @author Sunny Walker <swalker@hawaii.edu>
 */
function getLexiconList(PDO &$dbLink, $pageURL = './', &$numTables) {
	// Retrieve list of available lexicons
	$queryReply = $dbLink->query("SELECT `Index_ID`, `Name` FROM `lexinfo` ORDER BY `Name`;");
	$numTables = (int) $dbLink->query("SELECT FOUND_ROWS()")->fetchColumn();
	$return = "";

	// Display list of lexicons with links to their individual administration pages
	if (!$numTables) {
		$return = "<p>No lexicons found; <a href=\"adm_newlexicon.php\">create a new lexicon</a>.</p>\n";
	} else {
		while ($lang = $queryReply->fetch(PDO::FETCH_OBJ)) {
			$return .= "<p><a href=\"" . $pageURL . "?i=" . $lang->Index_ID . "\" class=\"lexlink\">" . htmlspecialchars($lang->Name) . "</a></p>\n";
		}
	}
	return $return;
} // getLexiconList()
