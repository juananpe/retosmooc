<?
	include("./session_check.php");
?>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="utf-8">
<link rel="shortcut icon" href="favicon.png" type="image/x-icon"> 
<title>DAWEB</title>
<link href="master.css" rel="stylesheet"  />
</head>

<body>
<?php 
    include('menu.php');
?>
<div class="center">

<?
  require_once("db.php");
  require_once("globals.php");

 for($challenge=1; $challenge<=$NUM_CHALLENGES;$challenge++){ 

  echo "<h2>Usuarios que han respondido al reto".$challenge."</h2>";

	$selection = "SELECT 
			u.username, u.first_name, u.last_name, c.notes, c.created_at, c.updated_at
			FROM conversation c, user u
			where command LIKE 'Reto". $challenge ."'
			and c.user_id = u.id 
			and u.id != 4694560
			order by c.updated_at DESC";
			// and status = 'stopped'

$emaitzak = $conn->query($selection);

echo "Total: " . $emaitzak->rowCount();
?>


<table>
<tr>
<th>username</th><th>Nombre</th><th>Apellidos</th><th>Cuándo</th><th>email</th></tr>

<?	while ($lerro = $emaitzak->fetch(PDO::FETCH_ASSOC)) {
		echo "\n<tr>";
		echo "<td>".$lerro['username'] . "</td>";
		echo "<td>".$lerro['first_name']. "</td>";
		echo "<td>".$lerro['last_name'] . "</td>";
		echo "<td>".$lerro['updated_at'] . "</td>";

		$obj = json_decode($lerro['notes']);

		if (isset($obj->email)) {
			echo "<td>".$obj->email . "</td>";
		}
		echo "</tr>\n";
	}

	echo "</table>";	
}
?>
</div>
</body>
</html>
