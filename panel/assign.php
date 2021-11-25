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
	$question = $_POST['question'];
	$challenge = $_POST['challenge'];
	$evaluated = isset($_POST['evaluated'])?$_POST['evaluated']:[];
	$deadline = $_POST['deadline'];

	// delete previous assignment
	$delete = $conn->prepare("DELETE FROM voicegrades where question = :question and challenge = :challenge");
	$delete->bindParam(':question', $question);
	$delete->bindParam(':challenge', $challenge);
	$delete->execute();

	// generate new assignment
	$grade = $conn->prepare("INSERT INTO voicegrades (question, challenge, evaluated, evaluator) VALUES(:question, :challenge, :evaluated, :evaluator)");
	$grade->bindParam(':question', $question);
	$grade->bindParam(':challenge', $challenge);
	$grade->bindParam(':evaluated', $user);
	$grade->bindParam(':evaluator', $evaluator);
	foreach ($evaluated as $user) {
		$evaluators = $_POST['evaluators'.$user];
		foreach($evaluators as $evaluator){
			$grade->execute();
		}
	}
?>
<p>Evaluadores asignados</p>
</body>
</html>
