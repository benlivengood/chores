<?php
$default_timezone = 'America/Los_Angeles';
$default_title = 'Chores';
date_default_timezone_set($default_timezone);
$choresdb = new mysqli("localhost", "chores", "A GOOD PASSWORD", "chores");
if ($choresdb->connect_errno) {
   exit("Unable to connect to database.");
}

?>