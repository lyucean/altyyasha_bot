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

    public function sendAll($text, $exclude_id = null)
    {
        // отправим всем пользователям бота
        foreach ($this->db->getChatHistoryIds() as $value) {

            if($value['chat_id'] == $exclude_id){
                continue;
            }
            $this->telegram->sendMessage(
              [
                'chat_id' => $value['chat_id'],
                'text' => $text
              ]
            );
        }
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
            return (new Error($this->telegram))->send('🥲 Я не знаю, как работать с этим типом сообщений.');
        }

        // Проверка, что количество запросов за сегодня не больше MAX_NUM_ATTEMPTS_PER_DAY
 /*       if ($_ENV['MAX_NUM_ATTEMPTS_PER_DAY'] < $this->db->getMessagesToday($this->chat_id)) {
            return (new Error($this->telegram))->send('Достигнут лимит попыток угадать на сегодня! '. random_reaction());
        }*/

        // проверим, что игра ещё продолжается
        if (!$this->db->getRightAnswerUnguessed()) {

            return $this->telegram->sendMessage(
              [
                'chat_id' => $this->chat_id,
                'text' => 'Игра окончена 🥳'
              ]
            );
        }

        // сохраним предложенный вариант
        $this->db->addMessage(
          [
            'chat_id' => $this->chat_id,
            'text' => $this->telegram->Text(),
            'message_id' => $this->telegram->MessageID(),
          ]
        );

        // удалим саму команду
        $possible = str_replace(['/send'], '', $this->telegram->Text());

        // очистим от лишнего
        $possible = ltrim(rtrim(mb_strtolower(str_replace(array("\r\n", "\r", "\n"), '', $possible))));

        // Сообщаем, что пользователь предложил вариант
        $who = $this->db->getNameByChatHistory($this->chat_id);

        $message[] = 'Вариант от '.$who.' '.'"'.$possible.'"';

        // Если не отгаданы все слова, то проверка идёт на слова
        if ($this->db->getRightWordsUnguessed()) {
            // Проверим, что если такое слово
            if ($this->db->getRightWordsCheck($possible)) {
                $message[] = '';
                $message[] = 'И это правильное слово!!! '.random_reaction().random_reaction().random_reaction();

                // пометим как отгаданное
                $this->db->updateRightWordsStatus($possible, $who);

                // Проверим, что есть ещё слова, которые необходимо отгадать
                if (!$this->db->getRightWordsUnguessed()) {
                    $arr_words = $this->db->getRightWordsOpen();
                    if ($arr_words) {
                        $message[] = '';
                        $message[] = 'Вы угадали все слова, вот ваши герои:';
                        foreach ($arr_words as $value) {
                            $message[] = $value['who']." - ".'"'.$value['text'].'"';
                        }
                        $message[] = '';
                        $message[] = 'Теперь необходимо составить из них искомую фразу.';
                    }
                }
            } else {
                $message[] = 'И не угадал '.random_reaction();
            }
        } else { // Если нет, то сверяем только всё выражение

            // Проверим, совпадает ли наше выражение
            if ($this->db->getRightAnswerCheck($possible)) {
                $message[] = '';
                $message[] = 'И это Бинго!!!';
                $message[] = 'Встречайте победителя '.random_reaction().random_reaction().random_reaction();

                // пометим как отгаданное
                $this->db->updateRightAnswerStatus($possible, $who);

                // Проверим, что победитель, это Даша
                if ($this->chat_id != 530979463) {
                    $this->telegram->sendMessage(
                      [
                        'chat_id' => 530979463,
                        'text' => '🍬 Приз за помощь... - Даша, прокричит вам спасибо в кружочек 😃' . random_reaction()
                      ]
                    );
                }

            } else {
                $message[] = 'И не угадал '.random_reaction();
            }
        }

        (new Message($this->telegram))->sendAll(implode("\n", $message));
    }
}
