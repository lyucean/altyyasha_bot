<?php

namespace altyyasha_bot\command;

use altyyasha_bot\core\DB;
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
     * @param array $data
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
                'text' => '😈 Отправленный вариант изменить уже нельзя!'
            ]
        );
    }

    public function addImage()
    {
        // take the highest resolution
//        $array = $this->telegram->Photo();
//        $file = $this->telegram->getFile(array_pop($array)['file_id']);
//
//        if (!array_key_exists('ok', $file) || !array_key_exists('result', $file)) {
//            (new Error($this->telegram))->send('Я не смог скачать картинку, сервер недоступен.');
//        }
//
//        $file_path = $file['result']['file_path'];
//        $file_name = $file['result']['file_unique_id'] . '.jpg';
//
//        $url_on_server = 'https://api.telegram.org/file/bot' . $_ENV['TELEGRAM_TOKEN'] . '/' . $file_path;
//
//        $folder = rand(10, 999) . '/';
//
//        if (!is_dir($_ENV['DIR_FILE'] . $folder)) {
//            mkdir($_ENV['DIR_FILE'] . $folder);
//        }
//
//        file_put_contents(
//            $_ENV['DIR_FILE'] . $folder . $file_name,
//            file_get_contents($url_on_server)
//        );
//
//        $this->message_id = $this->telegram->MessageID();
//
//        $this->db->addMessage(
//            [
//                'chat_id' => $this->chat_id,
//                'text' => $this->telegram->Caption(),
//                'image' => $folder . $file_name,
//                'message_id' => $this->telegram->MessageID(),
//            ]
//        );
//
//        $option = [
//            [
//                $this->telegram->buildInlineKeyBoardButton(
//                    'Отменить',
//                    $url = '',
//                    '/message/cancel?message_id=' . $this->message_id
//                ),
//            ],
//        ];

        $this->send(
            [
//                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Картинка? Ты серьёзно? 🤣🤣🤣'
            ]
        );
    }

    public function add()
    {
        if (!in_array($this->telegram->getUpdateType(), ['message', 'reply_to_message'])) {
            (new Error($this->telegram))->send('🥲 Я не знаю, как работать с этим типом сообщений.');
            return;
        }

        // Проверка на дубли, можно писать. Что кто-то уже пробовал этот вариант
//        if ($this->db->existCheckMessage(
//            [
//                'chat_id' => $this->chat_id,
//                'text' => $this->telegram->Text(),
//            ]
//        )) {
//            $message = $this->db->getMessage(['text' => $this->telegram->Text()]);
//
//            (new Error($this->telegram))->send(
//                'Этот вариант уже был))'
//            );
//            return;
//        }
        
        // Проверка, что количество запросов за сегодня не больше 5
        
        
        $this->message_id = $this->telegram->MessageID();

        // сохраним сообщение для других подарков)
        $this->db->addMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
                'message_id' => $this->telegram->MessageID(),
            ]
        );
        

        
        // Проверка на правду
        
        $this->send(
            [
                'text' => '😆 Не угадала !!!'
            ]
        );
    }
}
