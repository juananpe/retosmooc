<?php
/**
 * Usage on CLI: $ php broadcast.php [telegram-chat-id] 
 */

require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

$API_KEY = 'XXXXXXXXXXXXXXXXXXX';
$BOT_NAME = 'dawe2bot';

include("db.php");

$telegram = new Telegram($API_KEY, $BOT_NAME);

// Get the chat id and message text from the CLI parameters.
// $chat_id = isset($argv[1]) ? $argv[1] : '';

$users = $conn->query("select distinct(user_id) from voicecache");
foreach($users as $user) {

	$chat_id =  $user['user_id'] ;

	$query = "SELECT *
	FROM voicegrades v, voicecache c
	where v.evaluator = " . $chat_id . "
	and c.user_id = v.evaluated
	and c.selected = 1
	and v.question = c.question
	and v.challenge = c.challenge
	and v.sent=0
	and v.grade is null";

	$evaluations =  $conn->query($query);
//	list($howmany) = $evaluations->fetch();
	$howmany = $evaluations->rowCount();

	if ($howmany == 0)
		return;

	$plural = ($howmany > 1)?"s":"";

	$message = "Tienes $howmany nueva$plural respuesta$plural de voz para evaluar. Pulsa /evaluar para comenzar dicha evaluación";

	if ($chat_id !== '' && $message !== '') {
	    $data = [
		'chat_id' => $chat_id,
		'text'    => $message,
	    ];


	    // $result = Request::sendMessage($data);

	    print_r($data);
	    $result = Request::emptyResponse();

	   // $voice = "/opt/bots/pablobot/downloads/$chat_id/voice/file_12.oga";
	   // echo "Sending:" . $voice;
	   // $result = Request::sendVoice($data, $voice);

	    if ($result->isOk()) {
		    echo 'Message sent succesfully to: ' . $chat_id . "\n";
		    echo 'Marking this evaluator tasks as sent' . "\n";

		    $markassent = "update voicegrades set sent=1 where evaluator=". $chat_id ; 
		    $sent = $conn->query($markassent);
		    // $sent->rowCount();

		    echo "adding a reference to each recording that this evaluator will evaluate \n"; 
		    foreach($evaluations as $evaluation){
			    $updatevoice = "update voicegrades
					set voice_id = ".$evaluation['id']."
					where question = ".$evaluation['question'] ."
					and challenge = ".$evaluation['challenge'] ."
					and evaluated = ".$evaluation['evaluated'] ."
					and evaluator = ".$evaluation['evaluator'];
			    $affected = $conn->query($updatevoice);
			    // echo "Affected results (should be 1):" . $affected;
			}
	    } else {
		echo 'Sorry message not sent to: ' . $chat_id;
	    }
	}
}
