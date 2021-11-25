<?php

require_once("db.php");
require_once("globals.php");

// CONSTANTS
$MAX_EVALUACIONES_POR_EVALUADOR = 5;
$NUM_EVALUADORES_POR_PREGUNTA = 1;

// Structures
$evaluaciones = array();
$c_evaluados = array();
$c_evaluadores = array();
$users = array();


function obtain_users_voice($challenge, $question){
	global $conn;

	$users = array();	
	$sql = "select user_id, max(id) as id  from voicecache where question=$question and challenge=$challenge group by user_id";

	$res = $conn->query($sql);
	while($user = $res->fetch(PDO::FETCH_ASSOC)){
		$users[] = array('id'=>$user['user_id'], 'voice_id'=>$user['id']);
	}

	return $users;
}

function obtain_voice($user,$challenge,$question){
	global $conn;

	$voice_id = 0;
	$sql = "select * from voicecache where user_id=$user and challenge=$challenge and question=$question";
	$res = $conn->query($sql);
	$linea = $res->fetch(PDO::FETCH_ASSOC);
	return $linea['id'];
}

function insertar($evaluaciones){
	global $conn;

	foreach($evaluaciones as $evaluator=>$evaluados){
		foreach($evaluados as $evaluation){
			$insert = "INSERT INTO voicegrades 
				set challenge=".$evaluation['challenge'] .", question=".$evaluation['question'] .",
				evaluated=". $evaluation['evaluated'] .", evaluator=". $evaluator .", 
				voice_id=". $evaluation['voice_id'];
				try { 
					$conn->query($insert);
				} catch (Exception $e){
					die(print_r($e, 1));
				}
		}
	}

}


function safeinit(){
	global $conn;

	$sql = "select * from voicegrades";
	$res = $conn->query($sql);
	if ($res->rowCount() > 0)
		die("There are already some grades(".  $res->rowCount() .") in the DB. Aborting. \n");

}


$NUMQXCHALLENGE = array('0'=>0, '1'=>3, '2'=>4, '3'=>4, '4'=>3, '5'=>3, '6'=>4);
$num_voices = 0;
safeinit();
for($challenge=1; $challenge <= $NUM_CHALLENGES; $challenge++){
//	for($question=1; $question <= $NUMQXCHALLENGE[$challenge]; $question++){

		$question = rand(1,$NUMQXCHALLENGE[$challenge]);

//		if ($challenge == 4 && $question==4)
//			continue;


		$users = obtain_users_voice($challenge, $question);
		foreach($users as $user){
			// print_r("Asignando a $user (challenge: $challenge ) ( question : $question ) \n");

			// WARNING: sólo cogemos la última grabación que haya hecho el usuario para esta (q,c)
			// $voice_id = obtain_voice($user, $challenge, $question);
			$voice_id = $user['voice_id'];
			// 	print_r("VoiceID: $voice_id \n");
			if ($voice_id > 0){
				// print_r($voice_id .", ");
				$num_voices++;
				$c_evaluados[] = array('evaluated'=>$user['id'], 'voice_id'=>$voice_id, 
					'challenge'=>$challenge, 'question'=>$question);
				$c_evaluadores[]= $user['id'] ;
			}

		}

//		shuffle($c_evaluados);
//		shuffle($c_evaluadores);

		print_r("Realizando asignaciones random (challenge: $challenge ) ( question : $question ) Users: ". count($users). "\n");

		foreach($users as $evaluator){
					$evaluation = $c_evaluados[rand(0, count($c_evaluados)-1)];
					if ($evaluation['evaluated'] != $evaluator['id']){
						if (!isset($evaluaciones[$evaluator['id']]) || count($evaluaciones[$evaluator['id']]) < $MAX_EVALUACIONES_POR_EVALUADOR){
							$evaluaciones[$evaluator['id']][] = $evaluation;
						}
					}
		}
		$c_evaluados = array();
		$c_evaluadores = array();
//	} foreach question
}


insertar($evaluaciones);

print_r("Total de voces que recibirán al menos una evaluación: $num_voices \n");

