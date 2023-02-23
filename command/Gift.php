<?php

namespace altyysha_bot\command;

use altyysha_bot\core\DB;
use Telegram;

class Gift
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
        if (empty($this->chat_id == $_ENV['TELEGRAM_ADMIN_CHAT_ID'])) {
            return (new Error($this->telegram))->send('Вы не админ!');
        }


        // Откроем новую букву за присоединение.
        $letter = $this->db->openRightLetter('gift', $this->chat_id);

        if(!empty($letter)){

            $message[] = 'Эй, ловите новую букву в подарок: "' . $letter . '"';

            (new Message($this->telegram))->sendAll('🧛 ' . implode("\n", $message));
        }

        $this->telegram->sendMessage(
          [
            'chat_id' => $this->chat_id,
            'text' => 'Не открытых букв: '  . $this->db->getRightLettersnNotOpenCount()
          ]
        );
    }
}
