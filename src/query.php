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
	// query.php
	//
	// Purpose: Given a variety of query types, generate a list of matching results
	// Inputs:
	//     'i' (GET, mandatory): the index of the lexicon in the "lexinfo" table
	//     'a' (GET, optional): an alphabetical query; retrieve all entries beginning with the given letter
	//     'q' (GET, optional): a search query
	//
	//////

	// FUNCTIONS

	/**
	 * Compare two words against a collation
	 *
	 * @param  string $word1      A word
	 * @param  string $word2      Another word
	 * @param  array  $collation  An array containing all recognized characters used by the language
	 * @param  array  $values     An array parallel to $collation that assigns a numerical value to each character
	 * @return integer  1 if $word1 precedes $word2 alphabetically; 0 if $word1 follows $word2 alphabetically
	 */
	function compare($word1, $word2, $collation, $values) {
		$word1Array;
		$word2Array;

		// Convert the first word into an array of letters, disregarding characters with no collation value (such as punctuation)
		$counter = 0;
		for ($i = 0; $i < mb_strlen($word1); $i++) {
			$location = array_search(mb_substr($word1, $i, 2), $collation, true);
			if ($location !== false) {
				$word1Array[$counter] = $values[$location];
				$i++;
				$counter++;
			} else {
				$location = array_search(mb_substr($word1, $i, 1), $collation, true);
				if ($location !== false) {
					$word1Array[$counter] = $values[$location];
					$counter++;
				} else {
				}
			}
		}

		// Convert the second word into an array of letters, disregarding characters with no collation values (such as punctuation)
		$counter = 0;
		for ($i = 0; $i < mb_strlen($word2); $i++) {
			$location = array_search(mb_substr($word2, $i, 2), $collation, true);
			if ($location !== false) {
				$i++;
				$word2Array[$counter] = $values[$location];
				$counter++;
			} else {
				$location = array_search(mb_substr($word2, $i, 1), $collation, true);
				if($location !== false) {
					$word2Array[$counter] = $values[$location];
					$counter++;
				} else {
				}
			}
		}

		// Find the shorter word
		$lengthOfShorterWord = (count($word1Array) < count($word2Array)) ? count($word1Array) : count($word2Array);

		// Go letter-by-letter through both words until one is found should precede the other
		for ($i = 0; $i < $lengthOfShorterWord; $i++) {
			if ($word1Array[$i] < $word2Array[$i]) {
				return 1;
			} elseif ($word1Array[$i] > $word2Array[$i]) {
				return 0;
			} else {
				continue;
			}
		}
		return 0;
	} // query()

	/**
	 * Sort an array of words against a collation using a Quicksort algorithm
	 *
	 * @param  array $array      An array of words and the index values
	 * @param  array $collation  An array containing all recognized characters used by the language
	 * @param  array $values     An array parallel to $collation that assigns a numerical value to each character
	 * @return array  A sorted array
	 */
	function sortAlphabetical($array, $collation, $values) {
		if (count($array) <= 1) {
			return $array;
		}
		$left = $right = array();

		reset($array);
		$pivot_key = key($array);
		$pivot = array_shift($array);

		foreach ($array as $key => $entry) {
			if (compare($entry['Word'], $pivot['Word'], $collation, $values) == 1) {
				$left[$key] = $entry;
			} else {
				$right[$key] = $entry;
			}
		}

		return array_merge(sortAlphabetical($left, $collation, $values), array($pivot_key => $pivot), sortAlphabetical($right, $collation, $values));
	} // sortAlphabetical()


	require_once '_lex.php'; // Check for and load config file and connect to the database

	// Ensure mandatory GET inputs are set, else end execution
	if (isset($_GET['i'])) {
		$lexIndex = (int) $_GET['i'];
	} else {
		die('<p class=\"statictext warning\">Error: Missing index.</p>');
	}

	// Retrieve the language name and its collation from 'lexinfo'
	$queryReply = $dbLink->prepare("SELECT `Name`, `Collation` from `lexinfo` WHERE `Index_ID`=:index_id");
	$queryReply->execute(array(':index_id' => $lexIndex));
	$curLex = $queryReply->fetchColumn();

	// Convert the collation string into an array of equivalent values (such that each array index contains multiple characters that collate identically, e.g,, 'Aa')
	$collationList = explode(" ", $queryReply->fetchColumn(1));
	$collationArray = array();
	$collationValueArray = array();

	// Set encoding for multibye PHP string functions
	mb_internal_encoding("UTF-8");

	// Based on the new collation array, generate an array of every single character having a collation value and a parallel array with the actual collation values for each character
	// This is mindful of letters that may be composed of multiple glyphs
	$counter = 0;
	foreach ($collationList as $key => $letters) {
		while (mb_strlen($letters) > 0) {
			$curChar = mb_substr($letters, 0, 1);
			if ($curChar == "[") {
				$goTo = strpos($letters, "]");
				$curChar = substr($letters, 1, $goTo - 1);
				$letters = substr($letters, $goTo + 1);
			} else {
				$letters = mb_substr($letters, 1);
			}
			$collationArray[$counter] = $curChar;
			$collationValueArray[$counter] = $key;
			$counter++;
		}
	}

	// Create a SQL query based on the provided query type
	if (isset($_GET['a'])) {
		// If an Alphabetical Query
		// Retrieve the letter and its collation value
		$letter = $_GET['a'];
		$letterVal = $collationValueArray[array_search($letter, $collationArray, true)];

		// Create an array of characters with the same collation value (i.e., that are considered variants of the same letter)
		$equiv = array_keys($collationValueArray, $letterVal);
		$equivLetters;
		foreach ($equiv as $key => $val) {
			$equivLetters[$key] = $collationArray[$val];
		}

		// Query the database for words beginning with each letter in the equivalence array
		$query = "SELECT `Index_ID`, `Word` FROM `" . $curLex . "` WHERE (";
		$ors = array();
		foreach ($equivLetters as $aLetter) {
			$ors[] = "`Word` LIKE '" . $aLetter . "%'";
		}
		$query .= implode(' OR ', $ors) . ");";
		$queryReply = $dbLink->query($query);
		$totalEntries = (int) $dbLink->query('SELECT FOUND_ROWS()')->fetchColumn();

		// Iterate through the returned values and add valid values to an array
		$resultArray = array();
		while ($tmp = $queryReply->fetch(PDO::FETCH_ASSOC)) {
			$tmp = mysql_fetch_assoc($queryReply);
			if (array_search(mb_substr($tmp['Word'], 0, 1), $equivLetters, true) !== false) {
				// If the entry is valid, add it to the results array
				$resultArray[] = $tmp;
			} else {
				// If the entry is invalid, skip it and decrement the variable containing the number of returned results
				// This can happen if two characters that a particular collation considers to be unique are interpreted by MySQL as being the same letter.
				// For instance, MySQL considers the two Cyrillic letters 'ye' and 'yo' to be the variants of the same letter (as they are in Russian). Thus,
				// an query looking for words beginning with 'ye' will also return words beginning with 'yo' and vice versa. This is fine for Russian, but completely
				// incorrect for a language that considers these to be two completely distinct letters.
				$totalEntries--;
			}
		}

		// Output the total number of valid returned entries
		echo "<p class=\"count\">" . $totalEntries . " match" . (($totalEntries == 1) ? "" : "es") . " returned.</p>\n";

		// If the results array is non-zero, sort it and output the results
		if (isset($resultArray)) {
			$sortedResultArray = sortAlphabetical($resultArray, $collationArray, $collationValueArray);

			foreach ($sortedResultArray as $entry) {
				echo("<p><a href=\"view.php?i=" . $lexIndex . "&e=" . $entry['Index_ID'] . "\" class=\"entrylink\">" . $entry['Word'] . "</p>\n");
			}
		}
	} elseif (isset($_GET['q'])) {
		// If a Search Query
		// Retrieve the query
		$query = $_GET['q'];

		// Retrieve the list of fields that are searchable and split it into an array
		$queryReply = $dbLink->prepare("SELECT `SearchableFields` FROM `lexinfo` WHERE `Index_ID`=:lexIndex;");
		$queryReply->execute(array(':lexIndex' => $lexIndex));
		$searchableList = explode("\n", $queryReply->fetchColumn());

		// Query the database for words matching the search term, examining only the searchable fields
		$mysqlWhereTerms = "";
		foreach($searchableList as $key => $field) {
			$mysqlWhereTerms .= "`" . $field . "` LIKE '%" . $query . "%'";
			if(isset($searchableList[$key + 1])) {
				$mysqlWhereTerms .= " OR ";
			}
		}
		$queryReply = mysql_query("SELECT `Index_ID`, `Word` FROM `" . $curLex . "` WHERE " . $mysqlWhereTerms . ";");
		$totalEntries = mysql_num_rows($queryReply);

		// Iterate through the returned values and add valid values to an array
		$resultArray;
		for ($i = 0; $i < $totalEntries; $i++) {
			$resultArray[$i] = mysql_fetch_assoc($queryReply);
		}

		// Output the total number of valid returned entries
		echo "<p class=\"count\">" . $totalEntries . " match" . (($totalEntries == 1) ? "" : "es") . " returned.</p>\n";

		// If the results array is non-zero, sort it and output the results
		if (isset($resultArray)) {
			$sortedResultArray = sortAlphabetical($resultArray, $collationArray, $collationValueArray);

			foreach($sortedResultArray as $entry) {
				echo "<p><a href=\"view.php?i=" . $lexIndex . "&e=" . $entry['Index_ID'] . "\" class=\"entrylink\">" . $entry['Word'] . "</p>\n";
			}
		}
	}


	// Call the wordLookup() JavaScript function (in admin.js) to bind new click events to the displayed list
	// (since this file will generally be called by AJAX, the function must be run every time a new page component is loaded)
	echo "<script type=\"text/javascript\">\nwordLookup();\n</script>";
