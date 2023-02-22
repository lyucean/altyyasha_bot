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

        $message[] = '🤠 Буэно диас!';

        // Отправим все открытые буквы
        $message[] = 'Посмотрим, что у вас уже есть';
        $message[] = '';

        $message[] = 'Открытые буквы:';
        $message[] = 'ы,ф,б,у,а,ы';

        $message[] = '';
        $message[] = 'Отгаданные слова:';
        $message[] = '1. Тест1';
        $message[] = '2. Тест2';

        // Отправим все угаданные слова

        $message[] = '';
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );
    }
}
