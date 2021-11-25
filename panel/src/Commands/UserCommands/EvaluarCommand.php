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

/**
 * User "/evaluar" command
 */
class EvaluarCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'evaluar';
    protected $description = 'evaluar notas de voz';
    protected $usage = '/evaluar';
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

        $query = "SELECT *
            FROM voicegrades v, voicecache c
            where v.evaluator = " . $chat_id . "
            and c.user_id = v.evaluated
            and c.selected = 1
            and v.question = c.question
            and v.challenge = c.challenge
            and v.grade is null";

        $evaluations =  $conn->query($query);
        $howmany = $evaluations->rowCount();
	if ($howmany == 0)
		$state=-1;

	error_log("Howmany:". $howmany . "\n", 3, "/tmp/error.log");

        $options = [];
        foreach($evaluations as $evaluation){
            array_push($options, $evaluation['voice_id']);
        }
        //state machine
        //entrypoint of the machine state if given by the track
        //Every time the step is achived the track is updated
        switch ($state) {
            case 0:
                if (empty($text) || !in_array($text, $options) ){
                    $this->conversation->notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Tienes ' . $howmany . ' respuestas de voz pendientes de revisión. Selecciona cuál de ellas quieres evaluar:';
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

//                $selectPath = "select filePath, user_id from voicecache where id = " . $text;

                $selectPath = "select c.filePath, c.user_id, q.texto
                    from voicecache c, voicequestion q
                    where c.id = ". $text . "
                    and c.question = q.id
                    and c.challenge = q.challenge";

                    $filePathStmt = $conn->query($selectPath);
                    $filePath = $filePathStmt->fetch();

                    $data['caption'] = $filePath['texto'];
                    $result = Request::sendVoice($data, $this->telegram->getUploadPath() . "/" . $filePath['user_id'] . "/" . $filePath['filePath']);

                $this->conversation->notes['note0'] = $text;
                $text = '';
                // no break
            case 1:
                if (empty($text) || !is_numeric($text) || $text <0 || $text > 10 ) {
                    $this->conversation->notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Indica una nota del 0 (muy mal) a 10 (muy bien):';
                    if (!empty($text) && !is_numeric($text)) {
                        $data['text'] = 'Indica una nota del 0 al 10 (en formato numérico)';
                    }
                    $result = Request::sendMessage($data);
                    break;
                }
                $this->conversation->notes['note1'] = $text;
                ++$state;
                $text = '';

                // no break
            case 2:
                    $this->conversation->notes['state'] = 0;
                    // recuperar id de pregunta evaluada
                    $voice_id = $this->conversation->notes['note0'];
                    // recuperar nota de pregunta evaluada
                    $grade = $this->conversation->notes['note1'];

                    // echo "DEBUG:" . print_r($options,1) . "\n";

                    // preparar respuesta
                    $out_text = 'OK! Evaluación recibida.' . "\n";

                    $data['text'] = $out_text;
                    $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);

                    $result = Request::sendMessage($data);

                   $updategrade = "update voicegrades set grade = ".$grade.
                            " where voice_id =". $voice_id . " and evaluator = " . $chat_id;
                    $affected = $conn->query($updategrade);

                    $this->conversation->update(); // save the new 0 state

                    // actualizar opciones
                  $query = "SELECT *
                            FROM voicegrades v, voicecache c
                            where v.evaluator = " . $chat_id . "
                            and c.user_id = v.evaluated
                            and c.selected = 1
                            and v.question = c.question
                            and v.challenge = c.challenge
                            and v.grade is null";

                        $evaluations =  $conn->query($query);
                        $howmany = $evaluations->rowCount();

                        $options = [];
                        foreach($evaluations as $evaluation){
                            array_push($options, $evaluation['voice_id']);
                        }

                        // echo "DEBUG: " . print_r($howmany,1) . "\n";

                if ($howmany > 0 ){
                    $out_text =  'Tienes ' . $howmany . ' respuestas de voz pendientes de revisión. Selecciona cuál de ellas quieres evaluar.';
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

                 }else{ // end of conversation

                    unset($this->conversation->notes['state']);
                    $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
                    $out_text = 'OK! Has terminado de evaluar las respuestas pendientes.' . "\n";
                    $this->conversation->stop();

                }
                $data['text'] = $out_text;
                $result = Request::sendMessage($data);
                break;
	default:
	    	$data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
		$data['text'] = "No tienes respuestas pendientes de evaluación";
		$result = Request::sendMessage($data);
		break;
        }

        return $result;
    }
}
