<?php require_once('config.php'); ?>
<html><head><title><?php echo $default_title ?> Admin</title><head>
<body bgcolor=white>

<h1>Admin</h1>
<h3>People</h3>
<table border=1><tr><th>Name</th><th>id</th></tr>
<?php

if (array_key_exists('newperson', $_REQUEST)) {
   $newperson = $_REQUEST['newperson'];
   if (!($insert = $choresdb->prepare('INSERT INTO person(name) VALUES (?)'))) {
      echo "Couldn't add ". htmlentities($newperson) .
      	   " to database.<br>";
   } else {
      if (!($insert->bind_param('s', $newperson))) {
      	 echo "Couldn't bind person to INSERT statement.<br>";
      } else {
      	$insert->execute();
      }
   }	   
}

$userq = $choresdb->query("SELECT * from person");
while ($row = $userq->fetch_assoc()) {
      echo "<tr><td>" . htmlentities($row['name']) .
      	   "</td><td>" . htmlentities($row['id']) .
	   "</tr>\n";
}
?>
</table>

<form method=post action=admin.php>
Add person: <input id="newperson" type=text name=newperson>
<input type=submit name=submit value=Add>
</form>


<h3>Chores</h3>
<table border=1><tr><th>Name</th><th>Description</th><th>id</th></tr>
<?php

if (array_key_exists('newchore', $_REQUEST) &&
    array_key_exists('newchoredesc', $_REQUEST)) {
   $newchore = $_REQUEST['newchore'];
   $newchoredesc = $_REQUEST['newchoredesc'];
   if (!($insert = $choresdb->prepare(
            'INSERT INTO chore(name,description) VALUES (?,?)'))) {
      echo "Couldn't add ". htmlentities($newchore) .
      	   " to database.<br>";
   } else {
      if (!($insert->bind_param('ss', $newchore, $newchoredesc))) {
      	 echo "Couldn't bind chore to INSERT statement.<br>";
      } else {
      	$insert->execute();
      }
   }	   
}

$userq = $choresdb->query("SELECT * from chore");
while ($row = $userq->fetch_assoc()) {
      echo "<tr><td>" . htmlentities($row['name']) .
      	   "</td><td>" . htmlentities($row['description']) .
      	   "</td><td>" . htmlentities($row['id']) .
	   "</tr>\n";
}
?>
</table>

<form method=post action=admin.php>
Add chore: <input id="newchore" type=text name=newchore><br>
Description: <textarea id="newchoredesc" rows=5 cols=100 name=newchoredesc>
</textarea><br>
<input type=submit name=submit value=Add>
</form>




</body></html>
