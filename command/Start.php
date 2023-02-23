<?php

namespace altyysha_bot\command;

use altyysha_bot\core\DB;
use Telegram;

class Start
{
    private Telegram $telegram;
    private int $chat_id;
    private DB $db;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function index()
    {
        // Добавим расписание, когда присылать буквы
/*        $this->db->addSchedule(
            [
                'chat_id' => $this->chat_id,
                'hour_start' => 10,
                'hour_end' => 10,
                'time_zone_offset' => 3,
                'quantity' => 1,
            ]
        );*/

        $message[] = 'Буэно диас) Начнём 🧛';
        $message[] = '';

        // Отправим все открытые буквы

        // Количество загаданных слов
        $count_words = $this->db->getRightWordsCount();
        $message[] = 'Загадано ' . $count_words . ' ' . rus_ending($count_words, 'слово', 'слова', 'слов');

        $arr_letters = $this->db->getRightLettersOpen();
        if($arr_letters){
            $message[] = '';
            $message[] = 'Открытые буквы:';
            $message[] = implode(", ", $arr_letters);

        }

        $arr_words = $this->db->getRightWordsOpen();
        if($arr_words){
            $message[] = '';
            $message[] = 'Отгаданные слова:';
            foreach ($arr_words as $value){
                $message[] = $value['who'] . ": " .  $value['text'];
            }
        }

        // Отправим все угаданные слова
        $message[] = '';
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        // Откроем новую букву за присоединение.
        $letter = $this->db->openRightLetter('new', $this->chat_id);

        if(!empty($letter)){

            // Кто это
            $who = $this->db->getNameByChatHistory($this->chat_id);

            $message = ['Встречайте ещё одного игрока: ' . $who];

            $message[] = '';
            $message[] = 'И ловите новую букву в подарок: "' . $letter . '"';

            (new Message($this->telegram))->sendAll('🧛 ' . implode("\n", $message));
        }


    }
}
