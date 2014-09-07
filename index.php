<?php require_once('config.php'); 

$local = new DateTime();
$local->setTimeZone(new DateTimeZone($default_timezone));
$local->setTime(0,0,0);

$displayday = clone $local;

if (array_key_exists('displayday', $_REQUEST)) {
   try {
      $displayday = new DateTime($_REQUEST['displayday'], new DateTimeZone($default_timezone));
      $displayday->setTime(0,0,0);
   } catch (Exception $e) {
      $displayday = clone $local;      
   }     
}


$lastweek = clone $displayday;
$lastweek->sub(new DateInterval("P1W"));
$nextweek = clone $displayday;
$nextweek->add(new DateInterval("P1W"));

$lastlink = "?displayday=" . $lastweek->format('Y-m-d');
$nextlink = "?displayday=" . $nextweek->format('Y-m-d');

$weekday = $displayday->format("w");

$firstday = clone $displayday;
$lastday = clone $displayday;
$firstday->sub(new DateInterval("P" . intval($weekday) . "D"));
$lastday->add(new DateInterval("P" . (6 - intval($weekday)) . "D"));

if (array_key_exists('assign', $_REQUEST)) {
   $cleanupq = $choresdb->prepare("DELETE FROM assignment WHERE chore_id=? AND day BETWEEN ? AND ?");
   if (($assignq = $choresdb->prepare("INSERT INTO assignment (person_id,chore_id,day,complete) VALUES (?,?,?,0)"))) {
      foreach($_POST as $cid => $pid) {
         if ($pid == 0) continue;

	
	 $firstdelete = max($firstday, $local);
         $cleanupq->bind_param("iss", intval($cid), $firstdelete->format('Y-m-d'),
                         $lastday->format('Y-m-d'));
   	 $cleanupq->execute();

         for($i = 0; $i < 7; $i++) {
            $today = clone $firstday;
   	    $today->add(new DateInterval("P" . $i . "D"));
      	    if ($today >= $local &&
	        $assignq->bind_param("iis", intval($pid), intval($cid), 
	       			      $today->format('Y-m-d'))) {
	       $assignq->execute();
	    }	    
	 }
      }
   }
   if (array_key_exists('displayday', $_REQUEST)) {
      header("Location: index.php?displayday=" . $displayday->format('Y-m-d'));
   } else {
      header("Location: index.php");
   }
}

?>
<html><head>
<title><?php echo $default_title ?></title>
<link rel="stylesheet" type="text/css" href="chores.css">
<script src="flip.js" type="text/javascript"></script>
<head>
<body bgcolor=white>

<?php

$chorerow = Array();

$people = Array();

if (($userq = $choresdb	->query("SELECT * FROM person ORDER BY name"))) {
   while ($row = $userq->fetch_assoc()) {
      $people[] = Array(htmlentities($row['name']), intval($row['id']));
   }
}

if (!($assignq = $choresdb->prepare("SELECT assignment.id,assignment.day,person.name,chore.name,chore.id,assignment.complete,chore.description FROM chore LEFT JOIN (SELECT * FROM assignment WHERE day BETWEEN ? AND ?) assignment ON chore_id=chore.id LEFT JOIN person ON person_id=person.id ORDER BY chore.name"))) {

} else {
   if (!($assignq->bind_param("ss", $firstday->format("Y-m-d"),
				      $lastday->format("Y-m-d")))) {

   } else {
      if (!$assignq->execute()) {
      
      } else {
         if (!($assignq->bind_result($aid, $adate, $pname, $cname,
	                             $cid, $complete, $description))) {
	 } else {
            while ($assignq->fetch()) {
	       if (!in_array($cid, $chorerow)) {
	          $chorerow[] = intval($cid);
		  $cidname[$cid] = htmlentities($cname);
		  $ciddesc[$cid] = htmlentities($description);
	       }
	       $choreperson[$cid][$adate] = htmlentities($pname);
	       $choredone[$cid][$adate] = intval($complete);
	       $choreassign[$cid][$adate] = intval($aid);
	    }
	 }
      }  
   }
}


?>
<form action=index.php method=post>
<?php
if (array_key_exists('displayday', $_REQUEST)) {
   echo '<input type=hidden name=displayday value="' . 
   	$displayday->format('Y-m-d') . '">';
}
?>
<table class="cal">
<tr><th class="corner">
<div class=left><a href="<?php echo $lastlink ?>"><img src="left.png"></a></div>
<div class=right><a href="<?php echo $nextlink ?>"><img src="right.png"></a></div>
Chore</th><?php

for($i = 0; $i < 7; $i++) {
   $today = clone $firstday;
   $today->add(new DateInterval("P" . $i . "D"));
   echo "<th>" . $today->format("l") . "<br>" .
   $today->format("F") . "<br>" .
   $today->format("j") . "</th>";
}
?></tr>

<?php 
foreach ($chorerow as $cid) {
   echo '<tr><td class=chore><div title="' . $ciddesc[$cid] . '">' .
   	$cidname[$cid] . '<br>';
   echo '<select name="' . $cid . '"><option value=0></option>';
   foreach ($people as $p) {
      echo '<option value="' . $p[1] . '">' . $p[0] . '</option>';
   }
   echo '</select><input type=submit name=assign value="Assign"></div></td>';
   for($i = 0; $i < 7; $i++) {
      $today = clone $firstday;
      $today->add(new DateInterval("P" . $i . "D"));
      $thisday = $today->format('Y-m-d');
      if ($choredone[$cid][$thisday] == '0') echo "<td class=undone";
      else if ($choredone[$cid][$thisday] == '1') echo "<td class=done";
      else echo "<td";
      if ($choreperson[$cid][$thisday]) {
         echo ' id="' . $choreassign[$cid][$thisday] . '" ';
	 echo 'onClick="flip(' . $choreassign[$cid][$thisday] . ');">';
         echo $choreperson[$cid][$thisday];
      } else echo ">";
      echo "</td>\n";
   }
   echo "</tr>\n";
}
?></table></form>

<h1>Weekly totals for <?php echo $firstday->format('F j') ?> to <?php
echo $lastday->format('F j') ?></h1>
<table class=pay><tr><th>Name</th><th>Chores Completed</th><th>Pay</th></tr>
<?php

if (!($payq = $choresdb->prepare("SELECT count(*),name FROM assignment JOIN person ON person_id=person.id WHERE complete=1 AND day BETWEEN ? AND ? GROUP BY person_id ORDER BY name"))) {
   echo "<tr><td>:(</td></tr>";
} else {
   if (!($payq->bind_param("ss", $firstday->format("Y-m-d"),
		           $lastday->format("Y-m-d")))) {
      echo "<tr><td>:&lt;</td></tr>";
   } else {
      if (!$payq->execute()) {
         echo "<tr><td>:'(</td></tr>";
      } else {
         if (!($payq->bind_result($count,$name))) {
	    echo "<tr><td>X-/</td></tr>";
	 } else {
            while ($payq->fetch()) {
	       echo "<tr><td>" . $name . "</td><td>" . $count . "</td>";
	       printf('<td class=num>$%0.2f</td></tr>', $count);
	    }
	 }
      }  
   }
}


?>
</table>

</body></html>
