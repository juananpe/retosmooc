<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

/**
 * Start command
 */
class StartCommand extends SystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $text = '¡Hola!. Te damos la bienvenida.'."\n".
'Pulsa sobre /help (o escribe /help) para ver la lista de comandos disponibles. Teclea /Reto1 para empezar con tu actividad en esta comunidad de aprendizaje.'."\n".'Los retos se van realizando a través de preguntas que se responden con tu voz en un tiempo máximo de 1 minuto.'."\n".
'(Todos los comandos de un bot empiezan con el carácter barra /)';

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}
