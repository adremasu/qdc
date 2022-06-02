<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * User "/treatment" command
 *
 * Add a treatment
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class TreatmentCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'treatment';

    /**
     * @var string
     */
    protected $description = 'add treatment';

    /**
     * @var string
     */
    protected $usage = '/treatment';

    /**
     * @var string
     */
    protected $version = '0.4.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Conversation Object
     *
     * @var Conversation
     */
    protected $conversation;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        // Preparing response
        $data = [
            'chat_id'      => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            // Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        // Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();

        // State machine
        // Every time a step is achieved the state is updated
        switch ($state) {
            case 0:
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Prodotto utilizzato:';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['product'] = $text;
                $text          = '';

            // No break!
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Quantità:';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['quantity'] = $text;
                $text             = '';

            // No break!
            case 2:
                if ($text === '' || !is_numeric($text)) {
                    $notes['state'] = 2;
                    $this->conversation->update();

                    $data['text'] = 'Superficie (in ha):';
                    if ($text !== '') {
                        $data['text'] = 'Age must be a number';
                    }

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['area'] = $text;
                $text         = '';

            // No break!
            case 3:
                $years = [date("Y",strtotime("-1 year")),date("Y"), date("Y",strtotime("+1 year"))];
                if ($text === '' || !in_array($text, $years, true)) {
                    $notes['state'] = 3;
                    $this->conversation->update();

                    $data['reply_markup'] = (new Keyboard($years))
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(true);

                    $data['text'] = 'Selezione l\'anno:';
                    if ($text !== '') {
                        $data['text'] = 'Choose a keyboard option to select the year';
                    }

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['year'] = $text;

            // No break!
            case 4:
              for ($m=1; $m<=3; $m++) {
                $months1[] = date('F', mktime(0,0,0,$m, 1, date('Y')));
              }
              for ($m=4; $m<=8; $m++) {
                $months2[] = date('F', mktime(0,0,0,$m, 1, date('Y')));
              }
              for ($m=9; $m<=12; $m++) {
               $months3[] = date('F', mktime(0,0,0,$m, 1, date('Y')));
               }

              if ($text === '' || !in_array($text, $months, true)) {
                  $notes['state'] = 4;
                  $this->conversation->update();

                  $data['reply_markup'] = (new Keyboard($months1,$months2,$months3))
                      ->setResizeKeyboard(true)
                      ->setOneTimeKeyboard(true)
                      ->setSelective(true);

                  $data['text'] = 'Selezione il mese:';

                  $result = Request::sendMessage($data);
                  break;
              }

              $notes['month'] = $text;

            // No break!
            case 5:
              if ($text === '' || !is_numeric($text)) {
                  $notes['state'] = 5;
                  $this->conversation->update();

                  $data['text'] = 'giorno del mese:';
                  if ($text !== '') {
                      $data['text'] = 'il giorno deve essere un numero';
                  }

                  $result = Request::sendMessage($data);
                  break;
              }

              $notes['day'] = $text;
              $text         = '';

            // No break!
            case 6:
              if ($text === '') {
                  $notes['state'] = 6;
                  $this->conversation->update();

                  $data['text'] = 'Colture:';

                  $result = Request::sendMessage($data);
                  break;
              }

              $notes['cultivar'] = $text;
              $text         = '';

            // No break!
            case 7:
                $this->conversation->update();
                $out_text = '/treatment added:' . PHP_EOL;
                unset($notes['state']);
                foreach ($notes as $k => $v) {
                    $out_text .= PHP_EOL . ucfirst($k) . ': ' . $v;
                }

                $this->conversation->stop();

                break;
        }

        return $result;
    }
}
