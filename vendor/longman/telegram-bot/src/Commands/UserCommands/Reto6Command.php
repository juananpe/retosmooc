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
 * User "/reto6" command
 */
class Reto6Command extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Reto6';
    protected $description = 'Reto 6';
    protected $usage = '/reto6';
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

        /*
	error_log(print_r($message->getType() . "\n", 1), 3, "/tmp/error.log");
	if ($message->getType() == "Voice"){
		error_log(print_r($message->getVoice(). "\n", 1), 3, "/tmp/error.log");
		error_log(print_r($message->getVoice()->getFileId() . "\n", 1), 3, "/tmp/error.log");
		error_log(print_r($message->getVoice()->getDuration() . "\n", 1), 3, "/tmp/error.log");
    }
         */

        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = $message->getText(true);

//	error_log(print_r($text . "\n", 1), 3, "/tmp/error.log");
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        //Preparing Respose
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

        //state machine
        //entrypoint of the machine state if given by the track
        //Every time the step is achived the track is updated
        switch ($state) {
           case 0:
		        if ($message->getType() != "Voice"){
                    $this->conversation->notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'También necesitaremos saber si el sMOOC ha funcionado correctamente...¿Qué partes consideras necesario evaluar (contenidos, usabilidad y accesibilidad), objetivos, estrategias, la plataforma tecnológica, monitorización del aprendizaje...)? (mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }

                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question1'] = $anterior;
                // no break
            case 1:
                if ($message->getType()!="Voice" || ($this->conversation->notes['question1'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 1;
                    $this->conversation->update();

                    $data['text']='Centrándote en uno de los aspectos mencionados en la pregunta anterior, debemos pensar alguna pregunta para evaluar ese aspecto…🤔  ¿Se te ocurre alguna? (mensaje de voz de 1 minuto máximo)';

                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question2'] = $anterior;
                // no break
            case 2:
                if ($message->getType()!="Voice" || ($this->conversation->notes['question2'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 2;
                    $this->conversation->update();

                    $data['text'] = '¿Qué causas de abandono se te ocurren que podrían generar que algunos y algunas estudiantes del sMOOC no terminen el curso? (mensaje de voz de 1 minuto máximo)';

                   $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question3'] = $anterior;
            case 3:
                if ($message->getType()!="Voice" || ($this->conversation->notes['question3'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 3;
                    $this->conversation->update();

                    $data['text'] = '¿Qué herramientas para hacer cuestionarios conoces para poder evaluar nuestro sMOOC? (mensaje de voz de 1 minuto máximo)';

                   $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question4'] = $anterior;


                // no break
                $this->conversation->update();
                $out_text = '¡Fantástico! ¡Ya hemos conseguido completar todos los retos! 👏👏👏 ¡Muchas gracias por tu colaboración! 😃';
		$out_text .= 'Te agradecería mucho que nos ayudaras a mejorar esta herramienta de retos de voz cumplimentando la siguiente encuesta: https://goo.gl/forms/dzBvrN58qeAA3wJI2'; 

                unset($this->conversation->notes['state']);
                $this->conversation->stop();

                $data['text'] = $out_text;
	        $result = Request::sendMessage($data);

		$data['caption'] = '';
                $result = Request::sendPhoto($data, $this->telegram->getUploadPath() . "/miguel3.png");
                break;
        }
        return $result;
    }
}
