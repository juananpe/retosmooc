<?php
/**
 * Usage on CLI: $ php broadcast.php [telegram-chat-id] 
 */

require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

$API_KEY = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$BOT_NAME = 'retosmoocsbot';

include("panel/db.php");

$telegram = new Telegram($API_KEY, $BOT_NAME);

// Get the chat id and message text from the CLI parameters.
// $chat_id = isset($argv[1]) ? $argv[1] : '';

$somethingtodo = false;
$enviados = 0;


$users = $conn->query("select distinct(evaluated) from voicegrades where grade is not null and sent=3");

foreach($users as $user) {
	$chat_id =  $user['evaluated'] ;

	$message = "Tienes nuevas respuestas de voz que han sido evaluadas. Pulsa o teclea /consultar para revisar las evaluaciones recibidas.";

	if ($chat_id !== '' && $message !== '') {
	    $data = [
		'chat_id' => $chat_id,
		'text'    => $message,
	    ];
	    print_r($data);

//	   $result = Request::sendMessage($data);

	   $result = Request::emptyResponse();

	    if ($result->isOk()) {
		    echo 'Message succesfully sent to: ' . $chat_id . "\n";
		    echo 'Marking this evaluator tasks as sent' . "\n";

		    $markassent = "update voicegrades set sent=3 where evaluated=". $chat_id . " and grade is not null"; 
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
