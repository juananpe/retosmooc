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
 * User "/reto2" command
 */
class Reto2Command extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Reto2';
    protected $description = 'Reto 2';
    protected $usage = '/reto2';
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

	error_log(print_r($message->getType() . "\n", 1), 3, "/tmp/error.log");
	if ($message->getType() == "Voice"){
		error_log(print_r($message->getVoice(). "\n", 1), 3, "/tmp/error.log");
		error_log(print_r($message->getVoice()->getFileId() . "\n", 1), 3, "/tmp/error.log");
		error_log(print_r($message->getVoice()->getDuration() . "\n", 1), 3, "/tmp/error.log");
	}
        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = $message->getText(true);

	error_log(print_r($text . "\n", 1), 3, "/tmp/error.log");
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
                if (empty($text) || stripos($text,"S") === false) {
                    $this->conversation->notes['state'] = 0;
                    $this->conversation->update();

                    $keyboard = [['S','N']];
                    $reply_keyboard_markup = new ReplyKeyboardMarkup(
                        [
                            'keyboard' => $keyboard ,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                            'selective' => true
                        ]
                    );
                    $data['reply_markup'] = $reply_keyboard_markup;
                    $data['text'] = '¡Hola! ¡Qué bien que te animes con el segundo reto! ¿Preparado/a?';

                    $result = Request::sendMessage($data);
                    break;
                }
                $this->conversation->notes['sino'] = $text;
                $text = '';
                // no break
            case 1:
		if ($message->getType() != "Voice"){
                    $this->conversation->notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Para que sea un recurso en abierto (REA) necesitamos que lo conozca cuanta más gente mejor…¿Se te ocurre cómo puede anunciarlo la Universidad? ¡¡Cuantas más ideas tengamos, a más gente podremos llegar!! (mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }

                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question1'] = $anterior;
                ++$state;

                // no break
            case 2:
                if ($message->getType()!="Voice" || ($this->conversation->notes['question1'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 2;
                    $this->conversation->update();


		  $data['text']='¡Muy interesante, sí! 😊 ¿Sabes? He estado leyendo y la verdad es que tengo un lío con los tipos de MOOC que existen… 😳 ¡Tal vez tú puedas ayudarme! Tengo entendido que existen unos MOOC que se llaman xMOOC…¿Tú los conoces?  ¿Crees que serían apropiados para los cursos que quiere hacer esta Universidad?  ¿Por qué? (mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question2'] = $anterior;

                // no break
            case 3:

                if ($message->getType()!="Voice" || ($this->conversation->notes['question2'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 3;
                    $this->conversation->update();

                    $data['text'] = 'Pero esos no son la única metodología de cursos MOOC que existe...También he oído hablar de los cursos cMOOC…¿Qué te parecen? ¿Crees que los cMOOC serían una metodología mejor para nuestros cursos MOOC? (mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }
                 $anterior = $message->getVoice()->getFileId();
               $this->conversation->notes['question3'] = $anterior;
                $text = '';

                // no break
             case 4:

                if ($message->getType()!="Voice" || ($this->conversation->notes['question3'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 4;
                    $this->conversation->update();

                    $data['text'] = 'No sé, creo que falta algo…😕 Tiene que haber otro tipo…Déjame pensar…🤔 ¡Mira, he encontrado los cursos sMOOC! 😃 ¿Crees que serían los sMOOC los más adecuados para llevar a cabo la idea?¿Por qué lo piensas así? (mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question4'] = $anterior;
                $text = '';

                $this->conversation->update();
                $out_text = '¡Muchísimas gracias! ¡Me has ayudado un montón! ¡Creo que ahora tengo las ideas mucho más claras! 🤗';
		$out_text .= " Nos vemos en el /reto3";
                unset($this->conversation->notes['state']);
                $this->conversation->stop();

                $data['text'] = $out_text;
		        $result = Request::sendMessage($data);
                break;
        }
        return $result;
    }
}
