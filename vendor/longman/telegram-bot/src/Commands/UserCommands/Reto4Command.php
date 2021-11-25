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
 * User "/reto4" command
 */
class Reto4Command extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Reto4';
    protected $description = 'Reto 4';
    protected $usage = '/reto4';
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
                if ($message->getType()!="Voice") {
                    $this->conversation->notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Muchas gracias por continuar con los retos, ¡lo cierto es que tus aportaciones son inspiradoras y fundamentales para la toma de decisiones de la universidad!🔝

¡Venga, ánimo! Seguimos con otro ingrediente imprescindible para enriquecer el sMOOC: contar con profesionales de diversos ámbitos y organizarlos en equipo.¿Cómo nos organizamos?¿Qué tareas tenemos que desarrollar?  Nómbralas y fundamenta tu respuesta (mensaje de voz 🎤 de 1 minuto máximo)';
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

                    $data['text']='Ahora, respecto a los participantes… ¿qué diferencia encuentras entre el rol de un estudiante de un curso sMOOC y el rol que tendría un estudiante en un curso virtual tradicional? (mensaje de voz 🎤 de 1 minuto máximo)';
	                $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);

                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question2'] = $anterior;
                $text = '';
                // no break
            case 2:
                if ($message->getType()!="Voice" || ($this->conversation->notes['question2'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 2;
                    $this->conversation->update();

                    $data['text'] = '¿Qué tipo de recursos crees que merece la pena considerar en función de los contenidos que hemos previsto? ¡A mí me gustaría incluir una gran cantidad de recursos diferentes! 😆 ¿Qué recursos consideras fundamentales para tu sMOOC y por qué? (mensaje de voz 🎤 de 1 minuto máximo)';

                   $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                 $this->conversation->notes['question3'] = $anterior;
                $text = '';

                // no break
             case 3:
                if (empty($text) || (stripos($text,"S") === false && stripos($text,"N") === false)) {
                    $this->conversation->notes['state'] = 3;
                    $this->conversation->update();

                    $data['text'] = 'Me estoy animando tanto con tu colaboración que quizás me estoy pasando del poco presupuesto con el que contamos 😅 ¿Sería interesante enriquecerlo con Recursos Educativos Abiertos REA ?';
                    $keyboard = [['S','N']];
                    $reply_keyboard_markup = new ReplyKeyboardMarkup(
                        [
                            'keyboard' => $keyboard ,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => false,
                            'selective' => true
                        ]
                    );
                    $data['reply_markup'] = $reply_keyboard_markup;

                    $result = Request::sendMessage($data);
                    break;
                }
                $this->conversation->notes['question4'] = $text;
                // $text = '';
               // no break
             case 4:
                 if (stripos($text,"N") !== false){
                    $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
                    goto salir;
                 }

                 // stripos($text,"S")
                if ($message->getType()!="Voice"){

                    $this->conversation->notes['state'] = 4;
                    $this->conversation->update();

                    $data['text'] = '¿Qué Recursos Educativos Abiertos conoces?mensaje de voz 🎤 de 1 minuto máximo)';
                    $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question5'] = $anterior;
                $text = '';
                // no break

salir:
                $this->conversation->update();
                $out_text = '¡Esto se está poniendo muy emocionante! ¡¿Nos vemos en el /reto5?';

                unset($this->conversation->notes['state']);
                $this->conversation->stop();

                $data['text'] = $out_text;
                $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
		        $result = Request::sendMessage($data);
                break;
        }
        return $result;
    }
}
