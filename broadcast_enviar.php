<?php
/**
 * Usage on CLI: $ php broadcast.php [telegram-chat-id] 
 */

require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
$API_KEY = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$BOT_NAME = 'retosmoocsbot';

include("panel/db.php");

$telegram = new Telegram($API_KEY, $BOT_NAME);

// Get the chat id and message text from the CLI parameters.
// $chat_id = isset($argv[1]) ? $argv[1] : '';

$somethingtodo = false;
$enviados = 0;
// $users = $conn->query("select distinct(evaluator) from voicegrades where sent=0 and grade is null");
$users = $conn->query("select distinct(evaluator) from voicegrades where grade is null and sent < 2");
foreach($users as $user) {
	$chat_id =  $user['evaluator'] ;

	$message = "Tienes nuevas respuesta de voz para evaluar. Pulsa o teclea /evaluar para comenzar dicha evaluación.";

	if ($chat_id !== '' && $message !== '') {
	    $data = [
		'chat_id' => $chat_id,
		'text'    => $message,
	    ];
	    print_r($data);

	    $result = Request::sendMessage($data);

	    // $result = Request::emptyResponse();

	   // $voice = "/opt/bots/pablobot/downloads/$chat_id/voice/file_12.oga";
	   // echo "Sending:" . $voice;
	   // $result = Request::sendVoice($data, $voice);

	    if ($result->isOk()) {
		    echo 'Message succesfully sent to: ' . $chat_id . "\n";
		    echo 'Marking this evaluator tasks as sent' . "\n";

		    $markassent = "update voicegrades set sent=sent+1 where evaluator=". $chat_id ; 
		    $sent = $conn->query($markassent);
	   		$enviados++;
	    } else {
		echo 'Sorry message not sent to: ' . $chat_id;
	    }
	}
}

print_r("Enviados: $enviados \n");

if (!$somethingtodo)
	echo "Nothing to do\n";
