<?
session_start();

$error="";

function go($nora){
$uneko_zerbitzaria  = $_SERVER['HTTP_HOST'];
$uneko_karpeta   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header("Location: http://$uneko_zerbitzaria$uneko_karpeta/$nora");
exit;
}

if (isset($_GET['action']) && $_GET['action']=='logout'){
	unset($_SESSION['login']);
	session_destroy();
}
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
<link rel="stylesheet" type="text/css" href="jquery.datetimepicker.css"/ >
<script src="jquery.js"></script>
<script src="build/jquery.datetimepicker.full.min.js"></script>
</head>

<body>
<?php 
    include('menu.php');
	include('db.php');
?>
<div class="center">
	<form action="assign.php" method="post" enctype="multipart/form-data">
	<div class="left">

		<?php
    $sql = "SELECT texto, created_at FROM voicequestion WHERE id=".$_GET["question"] . " and 
	    challenge=".$_GET['challenge'];
    $stmt = $conn->query($sql);
    $question = $stmt->fetch();
?>	
		<p>Pregunta</p>
		<textarea name="question" rows="3" cols="45"><?php print_r( $question['texto'] ) ?></textarea>
		<br>
	<h4>Pregunta respondida por:</h4>
  <p><input type="checkbox" onchange="checkAll(this)" name="chk[]" />Marcar/desmarcar todos</p>

	</div>
	<div class="right">
		<p>Fecha de creación (Y-m-d H:M):</p>
		<input id="datetimepicker" type="text" name="deadline" value="<?php echo $question['created_at'] ?>"><br><br>
		<h4>Evaluarán la respuesta:</h4>
		<?php

		?>
	</div>
	<div class="users">
<?php

    // get users that tried to record a voice for this question and challenge
	 $userlist = array();
	  $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.username
		 FROM user u
		     INNER JOIN voicecache v ON v.user_id = u.id
		 AND v.question = ". $_GET['question'] . " AND v.challenge=".$_GET['challenge'];
	     foreach ($conn->query($sql) as $evaluated) {
	 array_push($userlist, (object) $evaluated);
	 }


	$evaluators = array();
	$assignments = "select question, challenge, evaluated, evaluator, grade, sent, first_name, last_name, username
	 from voicegrades v , user u
	where 
	  v.evaluator = u.id  AND
	  question = ". $_GET['question'] . " AND challenge=".$_GET['challenge'];

	foreach($conn->query($assignments) as $assignment) {
		array_push($evaluators, (object) $assignment);
	}


	foreach($userlist as $user) {
	   foreach($evaluators as $evaluator) {
	      if ($user->id == $evaluator->evaluated) {
		   $user->evaluators[] = $evaluator;
	      }
	   }
	}

    foreach ($userlist as $evaluated) {
			echo '<div class="todos" ><div class="evaluados" id='.$evaluated->id.'>';

			$evaluators = array();
			// first build a list of evaluators for this user
			if (isset($evaluated->evaluators)){
				foreach ($evaluated->evaluators as $evaluator) {
					$evaluators[] = $evaluator->evaluator;
				}
			}

			// all users that did this question are potential evaluators
			foreach($userlist as $evaluator){
				if ($evaluator->id == $evaluated->id)
					continue;
				$yesno = '';
				if (in_array($evaluator->id, $evaluators))
					$yesno = 'checked';
				echo '<p class="evaluator"><input type="checkbox" name="evaluators'.$evaluated->id.'[]" value="'.$evaluator->id.'" ' . $yesno  . '>  '.$evaluator->first_name.' '.$evaluator->last_name.' ('.$evaluator->username.') </p>';
			
			}
			

			// list of users that did this question on this challenge
			$checked = "";
			if (isset($evaluated->evaluators) ) 
				$checked = 'checked';
			echo '</div><p> <input onchange="showHideDiv('.$evaluated->id.',this)" type="checkbox" name="evaluated[]" value="'.$evaluated->id.'" ' . $checked  .'>  '.$evaluated->first_name.' '.$evaluated->last_name.' ('.$evaluated->username.')</p></div><br>';
		}
		?>
	</div>
	<div class="bottom">
		<br>
		<input type="hidden" name="question" value="<?php echo $_GET['question']?>">
		<input type="hidden" name="challenge" value="<?php echo $_GET['challenge']?>">
		<input type="submit" value="Enviar" >
		<br>
		<br>
		<br>
	</div>
	</div>

</div>
</form>
<script>
jQuery.datetimepicker.setLocale('es');
jQuery('#datetimepicker').datetimepicker({
	minDate:'-1970/01/02',
	format:'Y-m-d H:i'
});
 function checkAll(ele) {
     var checkboxes = document.getElementsByTagName('input');
     if (ele.checked) {
		 var elems = document.getElementsByClassName('evaluados');
		 for(var i = 0; i < elems.length; i++) {
			elems[i].style.display= 'block';
		}
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = true;
             }
         }
     } else {
		var elems = document.getElementsByClassName('evaluados');
		 for(var i = 0; i < elems.length; i++) {
			elems[i].style.display= 'none';
		}
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
 function showHideDiv(div,ele){
	 if (ele.checked) {
		 document.getElementById(div).style.display = 'block';
	 }else{
		 document.getElementById(div).style.display = 'none';
	 }
 }
</script>
</body>
</html>
