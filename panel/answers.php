<?

include("session_check.php");

if (!isset($_SESSION['login'])){
	go("index.php");
}
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
	include('db.php');
?>
<div class="center">
<table id="preguntas">
  <tr>
    <td>Reto</th>
    <td>id</th>
    <th id="tableTitle">Pregunta</th>
    <th>Fecha Creación</th>
    <? if (isset($_SESSION['admin'])) echo "<th>Asignar evaluadores</th>"; ?>
  </tr>
  <?php
	$sql = "SELECT * FROM voicequestion where id > 0   order by challenge, id  ";
	$color = "white";
	foreach ($conn->query($sql) as $question) {
		if($color =="white"){
			$color ="grey";
		}else{
			$color ="white";
		}
		echo '<tr class="clickable '.$color.'" ><td>'. $question['challenge'] .'</td><td>'. $question['id'] .'</td><td onclick="document.location=\'voice.php?id='.$question['id'].'&challenge='.$question['challenge'].'\'">'.$question['texto'].'</td><td class="deadline">'.$question['created_at'].'</td>';
		
		if (isset($_SESSION['admin'])) { 
			echo '<td><a href="assignEvaluators.php?question='. $question['id'] .'&challenge='. $question['challenge'] .'">Evaluar</a></td></tr>';
		}
	} // foreach
	?>
</table>
</div>
</body>
</html>
