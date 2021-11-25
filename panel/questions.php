<?

include("./session_check.php");

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
?>
<div class="center">
<h2>Añadir preguntas (En construcción) </h2>
</div>
</body>
</html>
