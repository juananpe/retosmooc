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
 * User "/reto3" command
 */
class Reto3Command extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Reto3';
    protected $description = 'Reto 3';
    protected $usage = '/reto3';
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

                    $data['text'] = '¡Hola! ¿Te he contado que gracias a nuestras ideas parece que por fin se han decidido? 😉  ¡¡Sí!! ¡Así que quieren seguir pensando cómo van a poner en práctica el sMOOC!  El vicerrector de ordenación académica y su equipo necesitan definir el plan del proyecto: Pre-proyecto (definición); Proyecto (diseño, creación y animación) y Post-Proyecto (cierre) ¡Vamos a ayudarles! 📝

Durante la 1ª fase tenemos que concretar el tema del sMOOC. ¿Quieres tratar de diseñar un sMOOC conmigo? 😅 ¿Sobre qué tema podríamos hacerlo y en qué plataforma? (mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }

                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question1'] = $anterior;
                $text = '';
                // no break
            case 1:
                if ($message->getType()!="Voice" || ($this->conversation->notes['question1'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 1;
                    $this->conversation->update();

                    $data['text']='¡Estupendo! En esta segunda fase vamos a centrarnos en el diseño pedagógico, concretamente en el plan de estudio. ¿Qué objetivo general y qué objetivos específicos propones en nuestro sMOOC? (mensaje de voz de 1 minuto máximo)';

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

                    $data['text'] = '¿Y ya has pensado en qué contenidos ofreceremos? 🙂 (mensaje de voz de 1 minuto máximo)';

                   $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question3'] = $anterior;

                // no break
             case 3:
                if ($message->getType()!="Voice" || ($this->conversation->notes['question3'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 3;
                    $this->conversation->update();

                    $data['text'] = 'Y como actividades, ¿has pensado en alguna? (mensaje de voz de 1 minuto máximo)';
	                $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);

                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question4'] = $anterior;
                $text = '';
                // no break

                $this->conversation->update();
                $out_text = '¡Plan de estudio completado! 🆙¡Buen trabajo! ¡Te espero en el /reto4!';

                unset($this->conversation->notes['state']);
                $this->conversation->stop();

                $data['text'] = $out_text;
		        $result = Request::sendMessage($data);
                break;
        }
        return $result;
    }
}
