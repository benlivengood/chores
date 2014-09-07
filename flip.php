<?php require_once('config.php'); 

$today = new DateTime();
$today->setTimeZone(new DateTimeZone($default_timezone));
$error = "Not started.";

if (array_key_exists('id', $_REQUEST)) {
   $id = intval($_REQUEST['id']);
   if (!($flipq = $choresdb->prepare("UPDATE assignment SET complete = 1 - complete WHERE id=? AND day=?"))) {
      $error = "Couldn't prepare statement.";
   } else {
      if (!($flipq->bind_param("is",$id, $today->format('Y-m-d')))) {
         $error = "Couldn't bind id parameter to statement.";
      } else {
         if (!$flipq->execute()) {
      	    $error = "Couldn't execute statement.";
         } else {
	    if (mysqli_stmt_affected_rows($flipq) == 1) $error = "Flipped.";
	    else $error = "Couldn't flip.";
	 }
      }  
   }
} else $error = "No id specified.";

echo $error;

?>
