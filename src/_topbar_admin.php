			<div id="topbar">
				<a href="manager.php" class="title">Administration</a><br/>
				<div id="adminnav">
<?php if (basename($_SERVER['PHP_SELF'])!=='manager.php') { ?>
					<p>• <a href="manager.php">Admin Home</a></p>
<?php
}
if (basename($_SERVER['PHP_SELF'])!=='adm_newlexicon.php') {
?>
					<p>• <a href="adm_newlexicon.php">New Lexicon</a></p>
<?php } ?>
					<p>• <a href="adm_backup.php">Backup Lexicons</a></p>
					<p>• <a href="adm_settings.php">Settings</a></p>
					<p>• <a href="adm_logout.php">Logout</a></p>
				</div>
			</div>
