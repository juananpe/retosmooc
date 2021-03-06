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
                    $data['text'] = '??Hola! ??Qu?? bien que te animes con el segundo reto! ??Preparado/a?';

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

                    $data['text'] = 'Para que sea un recurso en abierto (REA) necesitamos que lo conozca cuanta m??s gente mejor?????Se te ocurre c??mo puede anunciarlo la Universidad? ????Cuantas m??s ideas tengamos, a m??s gente podremos llegar!! (mensaje de voz de 1 minuto m??ximo)';
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


		  $data['text']='??Muy interesante, s??! ???? ??Sabes? He estado leyendo y la verdad es que tengo un l??o con los tipos de MOOC que existen??? ???? ??Tal vez t?? puedas ayudarme! Tengo entendido que existen unos MOOC que se llaman xMOOC?????T?? los conoces?  ??Crees que ser??an apropiados para los cursos que quiere hacer esta Universidad?  ??Por qu??? (mensaje de voz de 1 minuto m??ximo)';
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

                    $data['text'] = 'Pero esos no son la ??nica metodolog??a de cursos MOOC que existe...Tambi??n he o??do hablar de los cursos cMOOC?????Qu?? te parecen? ??Crees que los cMOOC ser??an una metodolog??a mejor para nuestros cursos MOOC? (mensaje de voz de 1 minuto m??ximo)';
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

                    $data['text'] = 'No s??, creo que falta algo??????? Tiene que haber otro tipo???D??jame pensar??????? ??Mira, he encontrado los cursos sMOOC! ???? ??Crees que ser??an los sMOOC los m??s adecuados para llevar a cabo la idea???Por qu?? lo piensas as??? (mensaje de voz de 1 minuto m??ximo)';
                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['question4'] = $anterior;
                $text = '';

                $this->conversation->update();
                $out_text = '??Much??simas gracias! ??Me has ayudado un mont??n! ??Creo que ahora tengo las ideas mucho m??s claras! ????';
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
