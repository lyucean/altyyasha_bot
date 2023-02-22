<?php


namespace altyysha_bot\model;

use altyysha_bot\command\Message;
use altyysha_bot\command\Now;
use altyysha_bot\core\Action;
use altyysha_bot\core\Model;

/**
 * Responsible for the processing of all incoming messages from the user
 * Class Processing
 * @package msb\Action
 */
class Processing extends Model
{
    const MESSAGE_LIMIT_PER_REQUEST = 10;

    public function check()
    {
        // Get all the new updates and set the new correct update_id before each call
        $updates = $this->telegram->getUpdates(0, self::MESSAGE_LIMIT_PER_REQUEST);

        if (!array_key_exists('result', $updates) || empty($updates['result'])) {
            return;
        }

        for ($i = 0; $i < (int)$this->telegram->UpdateCount(); $i++) {
            // You NEED to call serveUpdate before accessing the values of message in Telegram Class
            $this->telegram->serveUpdate($i);

            $text = $this->telegram->Text();
            $chat_id = $this->telegram->ChatID();

            // для дев окружения всегда выкидываем ответ в консоль
            if ($_ENV['OC_ENV'] == 'dev') {
                echo ddf($text, false);
            }

            // Tracking activity
            $this->db->addChatHistory(
                [
                    'chat_id' => $this->telegram->ChatID(),
                    'first_name' => $this->telegram->FirstName(),
                    'last_name' => $this->telegram->LastName() ?? '',
                    'user_name' => $this->telegram->Username() ?? '',
                    'text' => $text
                ]
            );

            // Если сообщение было отредактировано
            if ($this->telegram->getUpdateType() == 'edited_message') {
                (new Message($this->telegram))->edit();
                continue;
            }

            // Если это изображение
            if ($this->telegram->getUpdateType() == 'photo') {
                (new Message($this->telegram))->addImage();
                continue;
            }

            // проверка на команды
            if (mb_substr($text, 0, 1, 'UTF-8') == '/') {
                $action = new Action($text);
                $action->execute($this->telegram);
                continue;
            }

            // Все, что осталось, по умолчанию отправляется в контроллер
            if(!empty($this->telegram->ChatID())){
                (new Message($this->telegram))->add();
            }
        }
    }
}
