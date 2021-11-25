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
<link rel="stylesheet" type="text/css" href="jquery.datetimepicker.css"/ >
<script src="jquery.js"></script>
<script src="build/jquery.datetimepicker.full.min.js"></script>
</head>

<body>
<?php 
    include('menu.php');
	include('db.php');
    require_once('globals.php');
?>
<div class="center">
<?php
$question = $_GET['id'];
$challenge = $_GET['challenge'];

if ($BEST_ANSWER_SUPPORT){
	if (isset($_GET['voice_id']) && is_numeric($_GET['voice_id'])){

		// unset
		$updateselected = "update voicecache set selected = 0 where question=". $_GET['id'] . " and challenge=" . $_GET['challenge'] . " and user_id=". $_SESSION['userid'];
		$conn->query($updateselected);

		// set
		$updateselected = "update voicecache set selected = 1 where id=". $_GET['voice_id'] . " and user_id=". $_SESSION['userid'];
		$conn->query($updateselected);


	}
}
	$sql = $conn->prepare("SELECT texto FROM voicequestion WHERE id=:question and challenge=:challenge");
	$sql->bindParam(':question',$question);
	$sql->bindParam(':challenge',$challenge);
	$sql->execute();
	foreach ($sql->fetchAll() as $row){
		echo "<h3>".$row['texto']."</h3>";
	}
?>
	<div class="left">
	<h4>Respuestas:</h4>
	</div>
	<div class="right">
		<h4>Evaluaciones:</h4>
	</div>
	<div class="users">
		<?php

		$admin = "";
		if (!isset($_SESSION['admin']))
			$admin = " and user.id=" . $_SESSION['userid'];

		$sql = "SELECT voice.filePath , voice.id as voice_id, user.first_name,user.last_name, user.username, user.id as user_id, voice.selected  FROM voicecache as voice LEFT JOIN user ON user.id=voice.user_id WHERE question='".$question."' and challenge=$challenge " . $admin;
		foreach ($conn->query($sql) as $voice) {
			echo '<div class="todos" ><div class="evaluados" id="grades">';
			$sql = "SELECT grades.grade, user.first_name,user.last_name, user.username FROM voicegrades as grades LEFT JOIN user ON user.id=grades.evaluator WHERE question=".$question." and challenge=". $challenge. " and grades.evaluated=" . $voice['user_id'];
			// print_r("id grabación:" . $voice['voice_id'] . "\n<br>");
			foreach ($conn->query($sql) as $grade) {
				if ($voice['selected']){
					if($grade['grade']!=null){
						echo '<p class="evaluator">  '.$grade['first_name'].' '.$grade['last_name'].' ('.$grade['username'].') - '.$grade['grade'].'</p>';
					}else{
						echo '<p class="evaluator">  '.$grade['first_name'].' '.$grade['last_name'].' ('.$grade['username'].') - Sin evaluar</p>';
	
					}
			
				}
			}
			

			if($voice['filePath']!=null){
				// .oga files with OPUS codec are not supported in older version of Android
				// There is a cron that periodically converts .oga to .ogg files with Vorbis codec
				$voice['filePath']=dirname($voice['filePath'])."/".basename($voice['filePath'],".oga").".ogg";

				echo '</div><p>';
				if ($voice['selected'])
					$check ="check.png";
				else
					$check="uncheck.png";

				echo "<a href='?id=".$question."&challenge=". $challenge . "&voice_id=".$voice['voice_id'] ."'><img src='dist/img/".$check."' border='0'></a>";

				echo $voice['first_name'].' '.$voice['last_name'].' ('.$voice['username'].')</p>  <audio controls><source src="downloads/'.$voice['user_id'].'/'. $voice['filePath'] . '" type="audio/ogg">Tu navegador no es compatible. Descarga la <a href="'.$voice['filePath'].'"> nota de audio</a></audio> </div><br>';
			}else{
				echo '</div><p>'.$voice['first_name'].' '.$voice['last_name'].' ('.$voice['username'].') aún no ha respondido a esta pregunta</p> </div><br>';

			}
		}
		?>
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
