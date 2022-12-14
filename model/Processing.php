<?php


namespace altyyasha_bot\model;

use altyyasha_bot\command\Message;
use altyyasha_bot\command\Now;
use altyyasha_bot\core\Action;
use altyyasha_bot\core\Model;

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
            if (isset($_ENV['OC_ENV_DEV'])) {
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

            // If it's an independent command, it has the highest priority.
            // Necessarily, the very first
            if (mb_substr($text, 0, 1, 'UTF-8') == '/') {
                
                // Let's look for our command
                $action = new Action($text);
                $action->execute($this->telegram);

                ya_metric($chat_id, $text);
                continue;
            }

            // If this is editing, just edit the message
            if ($this->telegram->getUpdateType() == 'edited_message') {
                (new Message($this->telegram))->edit();
                continue;
            }

            // If this message, then check if the command is waiting
//            $waiting = $this->db->getWaitingCommand($chat_id);
//            if (!empty($waiting['command'])) {
//                // Clear command_waiting
//                $this->db->cleanWaitingCommand($chat_id);
//
//                // Let's look for our command_waiting
//                $action = new Action($waiting['command']);
//                $action->execute($this->telegram);
//
//                ya_metric($chat_id, $waiting['command']);
//                continue;
//            }

            // If this is image
            if ($this->telegram->getUpdateType() == 'photo') {
                (new Message($this->telegram))->addImage();
                continue;
            }

            // All that remains is sent to the controller by default
            (new Message($this->telegram))->add();
            continue;
        }
    }
}
