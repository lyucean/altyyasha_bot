<?php

namespace altyysha_bot\core;

use altyysha_bot\command\Error;
use Exception;
use MysqliDb;

class DB
{
    private MysqliDb $db;

    public function __construct()
    {
        $this->db = new MysqliDb(
          array(
            'host' => $_ENV['DB_HOST'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'db' => $_ENV['DB_NAME'],
            'port' => $_ENV['DB_PORT'],
            'prefix' => '',
            'charset' => $_ENV['DB_CHARSET']
          )
        );

        return $this;
    }

    // SendingDaily ---------------------------------------------------
    public function getSendingDailyNow()
    {
        $this->db->where("date_time", gmdate('Y-m-d H:i:s'), "<=");
        $this->db->where("status_sent", 0);
        return $this->db->get("schedule_daily");
    }

    public function addSendingDailyNow($data)
    {
        return $this->db->insert('schedule_daily', $data);
    }

    public function setScheduleDailyStatusSent($schedule_daily_id)
    {
        $this->db->where('schedule_daily_id', $schedule_daily_id);
        $this->db->update('schedule_daily', ['status_sent' => 1]);
    }

    // Schedule ---------------------------------------------------
    public function getSchedules()
    {
        return $this->db->get("schedule");
    }

    public function getSchedule($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        return $this->db->getOne("schedule");
    }

    public function setSchedule($chat_id, $data)
    {
        if (!empty($data['quantity'])) {
            $change['quantity'] = (int)$data['quantity'];
        }

        if (!empty($data['time_zone_offset'])) {
            $change['time_zone_offset'] = (int)$data['time_zone_offset'];
        }

        if (!empty($data['hour_start'])) {
            $change['hour_start'] = (int)$data['hour_start'];
        }

        if (!empty($data['hour_end'])) {
            $change['hour_end'] = (int)$data['hour_end'];
        }

        if (empty($change)) {
            return;
        }

        $change['chat_id'] = $chat_id;
        $change['date_modified'] = $this->db->now();

        $this->db->replace('schedule', $change);
    }

    public function addSchedule($data)
    {
        $data['date_modified'] = $this->db->now();
        return $this->db->replace('schedule', $data);
    }

    // Message ----------------------------------------------------

    /**
     * Selects which message to send.
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getMessagePrepared($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "asc");
        $message = $this->db->getOne("message");

        if (empty($message)) {
            return [];
        }

        // Add the information that we have already shown this message
        $this->addDateReminderMessage($message['message_id']);

        return $message;
    }

    /**
     * Selects which message to send.
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getMessagesToday($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("DATE(date_added) = DATE(NOW())");
        $message = $this->db->get("message");

        return count($message);
    }

    /**
     * @param $data
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function existCheckMessage($data)
    {
        if (isset($data['text'])) {
            $this->db->where("text", $this->db->escape(trim($data['text'])));
        }
        if (isset($data['message_id'])) {
            $this->db->where("message_id", (int)$data['message_id']);
        }
        $this->db->where("chat_id", $data['chat_id']);
        $this->db->where("display", 1);
        return !empty($this->db->get("message"));
    }

    /**
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getMessages($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "desc");
        return $this->db->get("message");
    }

    /**
     * @param  array  $filter
     * @return array
     * @throws Exception
     */
    public function getMessage(array $filter = [])
    {
        if (isset($filter['message_id'])) {
            $this->db->where("message_id", (int)$filter['message_id']);
        }
        if (isset($filter['text'])) {
            $this->db->where("text", $this->db->escape(trim($filter['text'])));
        }
        return $this->db->getOne("message");
    }

    /**
     * @param  int  $chat_id
     * @return array
     * @throws Exception
     */
    public function getLastMessage(int $chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->orderBy("date_reminder", "DESC");
        $this->db->where("display", 1);

        return $this->db->getOne("message");
    }

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function deleteMessage($data)
    {
        $this->db->where('message_id', $data['message_id']);
        $this->db->where('chat_id', $data['chat_id']);
        return $this->db->delete('message');
    }

    /**
     * @param $chat_id
     * @return bool
     * @throws Exception
     */
    public function clearAllMessage($chat_id)
    {
        $this->db->where('chat_id', $chat_id);
        return $this->db->update(
          'message',
          [
            'display' => 0
          ]
        );
    }

    /**
     * Adds message, returns message_id
     * @param $data
     * @throws Exception
     */
    public function addMessage($data)
    {
        $this->db->insert(
          'message',
          [
            'message_id' => $data['message_id'],
            'chat_id' => $data['chat_id'],
            'text' => $this->db->escape(trim($data['text'])),
            'image' => $data['image'] ?? '',
            'view' => 0,
            'date_added' => $this->db->now(),
            'date_reminder' => $this->db->now(),
            'display' => 1,
          ]
        );
    }

    /**
     * update date reminder and view for Message
     * @param $message_id
     * @throws Exception
     */
    public function addDateReminderMessage($message_id)
    {
        $this->db->where('message_id', $message_id);
        $this->db->update(
          'message',
          [
            'date_reminder' => $this->db->now(),
            'view' => $this->db->inc()
          ]
        );
    }

    /**
     * update Message
     * @param $data
     * @throws Exception
     */
    public function editMessageByMessageId($data)
    {
        $this->db->where('message_id', $data['message_id']);
        $this->db->where('chat_id', $data['chat_id']);

        if (isset($data['text'])) {
            $changes['text'] = $this->db->escape(trim($data['text']));
        }
        if (isset($data['display'])) {
            $changes['display'] = (bool)$data['display'];
        }

        if (isset($changes)) {
            $this->db->update(
              'message',
              $changes
            );
        }
    }

    // ChatHistory ------------------------------------------------
    public function addChatHistory($data)
    {
        $data['date_added'] = $this->db->now();
        $this->db->insert('chat_history', $data);
    }

    /**
     * @param $chat_id
     * @return string
     * @throws Exception
     */
    public function getNameByChatHistory($chat_id): string
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->orderBy("date_added", "desc");

        $result = $this->db->getOne("chat_history");

        return $result['first_name'].' '.$result['last_name'].' @'.$result['user_name'];
    }

    // Right Answer -----------------------------------------------

    /**
     * get Right Answer
     * @return array|string|null
     * @throws Exception
     */

    public function getRightAnswer()
    {
        $this->db->orderBy("status", "desc");
        return $this->db->getOne("right_answers");
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function endRightAnswer($winner): bool
    {
        return $this->db->update(
          'right_answers',
          [
            'status' => 0,
            'winner' => $this->db->escape(trim($winner))
          ]
        );
    }
    // PhrasesMessages ----------------------------------------------------

    /**
     * @return array|string
     * @throws Exception
     */
    public function getPhrasesMessagesPrepared()
    {
        $this->db->orderBy("date_reminder", "asc");
        $phrases_messages = $this->db->getOne("phrases_messages");

        // Add the information that we have already shown this message
        $this->addDateReminderPhrasesMessages($phrases_messages['phrases_messages_id']);

        return $phrases_messages['text'];
    }

    /**
     * @param $phrases_messages_id
     * @return void
     * @throws Exception
     */
    public function addDateReminderPhrasesMessages($phrases_messages_id)
    {
        $this->db->where('phrases_messages_id', $phrases_messages_id);
        $this->db->update(
          'phrases_messages',
          [
            'date_reminder' => $this->db->now(),
            'view' => $this->db->inc()
          ]
        );
    }

    // RightWords ----------------------------------------------------
    public function getRightWordsStatus()
    {
        return $this->db->getOne("right_words");
    }

    public function addRightWords(array $words): array
    {
        foreach ($words as $word) {
            $this->db->insert(
              'right_words',
              [
                'text' => $word,
                'status' => 0
              ]
            );
        }
    }
    public function getOpenRightWords(): array
    {
        $this->db->where("status", 1);

        $arr = [];

        foreach ($this->db->get("right_words") as $value){
            $arr[] = $value['text'];
        }

        return $arr;
    }

    // RightLetters ----------------------------------------------------
    public function getOpenRightLetters()
    {
        $this->db->where("status", 1);

        $letters = [];

        foreach ($this->db->get("right_letters") as $letter){
            $letters[] = $letter['text'];
        }

        return $letters;
    }

    public function addRightLetters(array $letters)
    {
        foreach ($letters as $letter) {

            // пропускаем пустые
            if (empty($letter)) {
                continue;
            }

            $data = [
              'text' => $letter,
              'status' => 0
            ];

            // 30% буквы, мы отроем на старте
            if (rand(0, 10) < 5) {
                $data['status'] = 1;
                $data['reason'] = 'Start';
            }

            $this->db->insert('right_letters', $data);
        }
    }
}
