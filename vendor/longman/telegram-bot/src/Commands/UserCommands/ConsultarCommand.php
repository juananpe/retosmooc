<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ForceReply;
use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use \PDO;
/**
 * User "/consultar" command
 */
class ConsultarCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'consultar';
    protected $description = 'consultar evaluaciones recibidas en las notas de voz';
    protected $usage = '/consultar';
    protected $version = '0.0.1';
    protected $need_mysql = true;
    /**#@-*/

    /**
     * Conversation Object
     *
     * @var \Longman\TelegramBot\Conversation
     */
    protected $conversation;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();

        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = $message->getText(true);

        $chat_id = $chat->getId();
        $user_id = $user->getId();

        //Preparing Response
        $data = [];
        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            //reply to message id is applied by default
            //Force reply is applied by default to so can work with privacy on
            $data['reply_markup'] = new ForceReply([ 'selective' => true]);
        }
        $data['chat_id'] = $chat_id;

        //Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        //cache data from the tracking session if any
        if (!isset($this->conversation->notes['state'])) {
            $state = '0';
        } else {
            $state = $this->conversation->notes['state'];
        }

        include("db.php");

	$DEBUG = false;
	if ($DEBUG) $chat_id = 169068707;

        $query = "SELECT *
            FROM voicegrades v, voicecache c
            where v.evaluated = " . $chat_id . "
            and c.user_id = v.evaluated
            and v.voice_id  = c.id
            and v.question = c.question
            and v.challenge = c.challenge
            and v.grade is not null";

            // and c.selected = 1
        $evaluations =  $conn->query($query);
        $howmany = $evaluations->rowCount();

        $options = [];
        foreach($evaluations as $evaluation){
            array_push($options, $evaluation['voice_id'] . "|" . $evaluation['evaluator']);
        }

        //state machine
        //entrypoint of the machine state if given by the track
        //Every time the step is achived the track is updated
        switch ($state) {
            case 0:
                if (empty($text) || !in_array($text, $options) ){
                    $this->conversation->notes['state'] = 0;
                    $this->conversation->update();
                    if ($howmany == 0){
                        $data['text'] = "No tienes respuestas de voz que te hayan sido evaluadas";
                        $this->conversation->stop();
                        $result = Request::sendMessage($data);
                        break;
                    }else if ($howmany == 1)
                        $data['text'] = 'Tienes 1 respuesta de voz evaluada.';
                    else if ($howmany > 1)
                        $data['text'] = 'Tienes ' . $howmany . ' respuestas que te han sido evaluadas. Selecciona cuál de ellas quieres revisar:';

                    $howmany--;
                    $keyboard = [$options];
                    $reply_keyboard_markup = new ReplyKeyboardMarkup(
                        [
                            'keyboard' => $keyboard ,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                            'selective' => true
                        ]
                    );
                    $data['reply_markup'] = $reply_keyboard_markup;

                    // $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
                    $result = Request::sendMessage($data);


                    break;
                }

		if ($DEBUG) print_r("DEBUG:" . $text . "\n\n");
		list($voice_id, $evaluator) = split("\|", $text);
		if ($DEBUG) print_r("DEBUG:" . $voice_id . "-" . $evaluator . "\n\n");

                $selectPath = "select c.filePath, c.user_id, q.texto
                    from voicecache c, voicequestion q
                    where c.id = ". $voice_id . "
                    and c.question = q.id
                    and c.challenge = q.challenge";

                    $filePathStmt = $conn->query($selectPath);
                    $filePath = $filePathStmt->fetch();
                    $filePath['filePath'] = pathinfo($filePath['filePath'], PATHINFO_DIRNAME) . '/' . pathinfo($filePath['filePath'], PATHINFO_FILENAME);

                    // $data['caption'] = $filePath['texto'];
                    $result = Request::sendVoice($data, $this->telegram->getUploadPath() . "/" . $filePath['user_id'] . "/" . $filePath['filePath']);
                    $data['text'] = $filePath['texto'];
                    //                    $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
                    $data['parse_mode'] = 'MARKDOWN';
                    $result = Request::sendMessage($data);


		   $conn->query("SET NAMES utf8mb4");

			// send grade and explanation
			$selectGrade = "select * from voicegrades where voice_id = $voice_id and evaluator = $evaluator";
			$gradeStmt = $conn->query($selectGrade);
			$gradeDetails = $gradeStmt->fetch();

			$data['text'] = "*Nota*:" . $gradeDetails['grade'] ;
			$data['text'] .= "\n *Comentarios*: " . $gradeDetails['explanation'];
			$result = Request::sendMessage($data);


                $text = '';



		// no break
	case 1:

                    $this->conversation->notes['state'] = 0;
                    $this->conversation->update(); // save the new 0 state

		$query = "SELECT *
		    FROM voicegrades v, voicecache c
		    where v.evaluated = " . $chat_id . "
		    and c.user_id = v.evaluated
		    and v.voice_id  = c.id
		    and v.question = c.question
		    and v.challenge = c.challenge
		    and v.grade is not null";


                        $evaluations =  $conn->query($query);
                        $howmany = $evaluations->rowCount();

                        $options = [];
                        foreach($evaluations as $evaluation){
                            array_push($options, $evaluation['voice_id'] . "|" . $evaluation['evaluator']);
                        }

                        // echo "DEBUG: " . print_r($howmany,1) . "\n";

                if ($howmany > 0 ){
                    $out_text =  'Tienes ' . $howmany . ' respuestas de voz evaluadas. Selecciona cuál de ellas quieres revisar:';
                    $keyboard = [$options];
                    $reply_keyboard_markup = new ReplyKeyboardMarkup(
                        [
                            'keyboard' => $keyboard ,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                            'selective' => true
                        ]
                    );
                    $data['reply_markup'] = $reply_keyboard_markup;
                    // print_r($reply_keyboard_markup);

                 }

                $data['text'] = $out_text;
                $result = Request::sendMessage($data);
                break;
        }

        return $result;
    }
}
