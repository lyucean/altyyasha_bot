<?php

namespace altyysha_bot\command;

use altyysha_bot\core\DB;
use Telegram;

class Send
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

        // получим нашу фразу
        $phrases = $this->telegram->Text();

        // удалим саму команду
        $phrases = str_replace(['/send'], '', $phrases);

        $chats = $this->db->getChatHistoryIds();

        // получим всех пользователей бота
        foreach ($chats as $value) {
            $this->telegram->sendMessage(
              [
                'chat_id' => $value['chat_id'],
                'text' => '🧛' . $phrases
              ]
            );
        }
    }
}
