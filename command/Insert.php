<?php

namespace altyysha_bot\command;

use altyysha_bot\core\DB;
use Telegram;

class Insert
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
        $phrases = str_replace(['/insert', ',', '.'], '', $phrases);
        // преобразуем регистр и уберём пробелы
        $phrases = trim(mb_strtolower($phrases));

        if (empty($phrases)) {
            return (new Error($this->telegram))->send('Фраза не может быть пустой!');
        }

        // Добавим слова
        $arr_words = preg_split("/[\s,.]+/", $phrases, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($arr_words) or count($arr_words) < 2) {
            return (new Error($this->telegram))->send('Слов меньше 2ух!');
        }

        if (!empty($this->db->getRightWordsStatus())) {
            return (new Error($this->telegram))->send('Слова уже есть!');
        }

        $this->db->addRightWords($arr_words);

        // Добавим буквы
        $arr_letters = preg_split('//u', str_replace(" ","",$phrases), null, PREG_SPLIT_NO_EMPTY);

        shuffle($arr_letters);

        if (empty($arr_letters)) {
            return (new Error($this->telegram))->send('Нет букв!');
        }

        $this->db->addRightLetters($arr_letters);

        // Добавим фразу в правильный ответ
        $this->db->insertRightAnswer($phrases);

        // сообщим
        $message[] = '';
        $message[] = 'Добавил слова:';
        $message[] =  implode("\n", $arr_words);

        $message[] = '';
        $message[] = 'Правильный ответ:';
        $message[] =  $phrases;

        $message[] = '';
        $this->telegram->sendMessage(
          [
            'chat_id' => $this->chat_id,
            'text' => implode("\n", $message)
          ]
        );
    }
}
