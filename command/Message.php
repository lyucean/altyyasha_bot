<?php

namespace altyysha_bot\command;

use altyysha_bot\core\DB;
use Telegram;

class Message
{
    private Telegram $telegram;
    private int $chat_id;
    private int $message_id = 0;
    private DB $db;
    const EMOJI_ICON = '🙃  ';

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function __debugInfo()
    {
        return [
          'message_id' => $this->message_id,
        ];
    }

    /**
     * Отправляет сообщение в чат
     * @param  array  $data
     */
    public function send(array $data)
    {
        if (isset($data['chat_id'])) {
            $answer['chat_id'] = $data['chat_id'];
        }

        if (empty($answer['chat_id'])) {
            $answer['chat_id'] = $this->chat_id;
        }

        if (isset($data['reply_markup'])) {
            $answer['reply_markup'] = $data['reply_markup'];
        }

        if (isset($data['text'])) {
            $answer['text'] = fix_breaks($data['text']);
        }

        $this->telegram->sendMessage($answer);
    }

    public function edit()
    {
        $this->send(
          [
            'text' => '😈 Отправленный вариант изменять уже нельзя!'
          ]
        );
    }

    public function addImage()
    {
        $this->send(
          [
            'text' => 'Я не умею работать с картинкой) 🤣'
          ]
        );
    }

    public function add()
    {
        if (!in_array($this->telegram->getUpdateType(), ['message', 'reply_to_message'])) {
            (new Error($this->telegram))->send('🥲 Я не знаю, как работать с этим типом сообщений.');
            return;
        }


        $this->send(
          [
            'text' => random_reaction()
              .PHP_EOL.PHP_EOL.' Заглушка сегодня '
          ]
        );
        return;

        $count_answer = $this->db->getMessagesToday($this->chat_id);

        // Проверка, что количество запросов за сегодня не больше MAX_NUM_ATTEMPTS_PER_DAY
        if ($_ENV['MAX_NUM_ATTEMPTS_PER_DAY'] < $count_answer) {
            $this->send(
              [
                'text' => 'Достигнут лимит попыток угадать на сегодня!'.random_reaction()
                  .PHP_EOL.'Возвращайся завтра) 😇'
              ]
            );
            return;
        }

        // сохраним сообщение для других подарков
        $this->db->addMessage(
          [
            'chat_id' => $this->chat_id,
            'text' => $this->telegram->Text(),
            'message_id' => $this->telegram->MessageID(),
          ]
        );

        // Проверка, на правильный ответ
        $answer = $this->db->getRightAnswer();


        // Отправим всем сообщения, кто у нас победитель!
        if ($answer['status'] == 0) {
            $this->send(
              [
                'text' => '🥳У нас есть победитель! 🥳'.random_reaction()
                  .PHP_EOL.'Это: '.$answer['winner']
              ]
            );
            return;
        }

        // Проверка, на правильный ответ
        if ($answer['text'] == ltrim(rtrim(mb_strtolower($this->telegram->Text())))) {
            $this->send(
              [
                'text' => '🥳️❤🥳️Угадала !!!🥳️❤️🥳'
              ]
            );

            $this->db->endRightAnswer($this->db->getNameByChatHistory($this->chat_id));
        } else {
            $phrases_messages = $this->db->getPhrasesMessagesPrepared();

            $num_attempts = $_ENV['MAX_NUM_ATTEMPTS_PER_DAY'] - $count_answer - 1;
            $string_attempts = rus_ending($num_attempts, 'попытка', 'попытки', 'попыток');

            $this->send(
              [
                'text' => $phrases_messages.' '.random_reaction()
                  .PHP_EOL.PHP_EOL.' Осталось '.$num_attempts.' '.$string_attempts.' на сегодня '.random_reaction()
              ]
            );
        }
    }
}
