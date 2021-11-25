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
 * User "/reto" command
 */
class RetoCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'Reto';
    protected $description = 'Reto 1';
    protected $usage = '/reto';
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
                if (empty($text) || strpos($text,"@") === false) {
                    $this->conversation->notes['state'] = 0;
                    $this->conversation->update();
    
                    $data['text'] = 'Escribe tu email para que te enviemos los resultados totales del reto, una vez que lo hayan contestado todos los miembros de la comunidad';
                    $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }
                $this->conversation->notes['email'] = $text;
                $text = '';
                // no break
            case 1:
		if ($message->getType() != "Voice"){
                    $this->conversation->notes['state'] = 1;
                    $this->conversation->update();
    
                    $data['text'] = 'Una universidad está en proceso de incrementar su oferta de formación para el próximo curso. El equipo del rector está muy interesado en comenzar ofreciendo cursos masivos online abiertos (MOOCs). ¿Qué opinas sobre estas intenciones de la universidad? (mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }
		
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['opinionmooc'] = $anterior;
                ++$state;

                // no break
            case 2:
                if ($message->getType()!="Voice" || ($this->conversation->notes['opinionmooc'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 2;
                    $this->conversation->update();

	        
                  $data['caption'] = 'Hay un becario de esta universidad, Miguel, que os quiere saludar.';
                  $result = Request::sendPhoto($data, $this->telegram->getUploadPath() . "/miguel1.png");
                  $data['caption'] = '';
                  $result = Request::sendPhoto($data, $this->telegram->getUploadPath() . "/miguel2.png");
		  $data['text']='Buenos días!!!!Me llamo Miguel y me encanta estar en la Universidad “Comunicatin”, porque es muy grande y a distancia!!!!! y así conozco gente de todos los países!!.. Siempre está probando hacer cosas nuevas e interesantes…. Ahora he escuchado que mi Universidad está pensando en organizar una serie de cursos en abierto y gratuitos que les llama MOOC, y me encantaría poder animarles para que se decidan del todo, porque creo que es una muy buena idea…¿Se te ocurre qué razones podría explicarles para que se decidan a animarse???(mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }
                $anterior = $message->getVoice()->getFileId();
                $this->conversation->notes['opinionmiguel'] = $anterior;

                // no break
            case 3:

                if ($message->getType()!="Voice" || ($this->conversation->notes['opinionmiguel'] == $message->getVoice()->getFileId())){
                    $this->conversation->notes['state'] = 3;
                    $this->conversation->update();

                    $data['text'] = 'Vaya, qué bien! no se me había ocurrido… Parece que con tus ideas el MOOC va a salir adelante!!!'."\n".
'Ahora la universidad está pidiendo ideas...¿Qué temas te parecen interesantes para organizar un MOOC?(mensaje de voz de 1 minuto máximo)';
                    $result = Request::sendMessage($data);
                    break;
                }
                 $anterior = $message->getVoice()->getFileId();
               $this->conversation->notes['opiniontemas'] = $anterior;
                $text = '';

                // no break
            case 4:
                $this->conversation->update();
                $out_text = 'Muchísimas gracias!Me has ayudado un montón! Creo que ahora tengo las ideas mucho más claras gracias a ti!!!!'."\n".
'En cuanto analicemos las respuestas, recibirás un mensaje con un resumen de las mismas al email proporcionado.';

                unset($this->conversation->notes['state']);

		$out_text .= "\n" . $this->conversation->notes['email'];
/*
                foreach ($this->conversation->notes as $k => $v) {
                    $out_text .= "\n" . ucfirst($k).': ' . $v;
                }
*/
         //       $data['photo'] = $this->conversation->notes['photo_id'];
           //     $data['reply_markup'] = new ReplyKeyBoardHide(['selective' => true]);
             //   $data['caption'] = $out_text;
                $this->conversation->stop();
               // $result = Request::sendPhoto($data);
		$data['text'] = $out_text;
		$result = Request::sendMessage($data);
                break;
        }
        return $result;
    }
}
