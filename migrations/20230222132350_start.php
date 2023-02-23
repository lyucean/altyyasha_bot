<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Start extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('chat_history', ['id' => 'chat_history_id']);
        $table->addColumn('chat_id', 'biginteger', ['null' => false])
            ->addColumn('first_name', 'string', ['limit' => 100])
            ->addColumn('last_name', 'string', ['limit' => 100])
            ->addColumn('user_name', 'string', ['limit' => 100])
            ->addColumn('text', 'string', ['limit' => 4096])
            ->addColumn('date_added', 'datetime')
            ->create();

        $table = $this->table('message', ['id' => false, 'primary_key' => 'message_id']);
        $table->addColumn('message_id', 'integer', ['null' => false])
            ->addColumn('chat_id', 'biginteger', ['null' => false])
            ->addColumn('text', 'string', ['limit' => 4096])
            ->addColumn('image', 'string', ['limit' => 100])
            ->addColumn('view', 'integer', ['default' => 0])
            ->addColumn('display', 'integer', ['default' => 1])
            ->addColumn('date_reminder', 'datetime')
            ->addColumn('date_added', 'datetime')
            ->create();

        $table = $this->table('schedule', ['id' => false, 'primary_key' => 'chat_id']);
        $table->addColumn('chat_id', 'biginteger', ['null' => false])
            ->addColumn('hour_start', 'integer', ['default' => 9])
            ->addColumn('hour_end', 'integer', ['default' => 14])
            ->addColumn('time_zone_offset', 'integer', ['default' => 3])
            ->addColumn('quantity', 'integer', ['default' => 1])
            ->addColumn('date_modified', 'datetime')
            ->create();

        $table = $this->table('schedule_daily', ['id' => 'schedule_daily_id']);
        $table->addColumn('chat_id', 'biginteger', ['null' => false])
            ->addColumn('date_time', 'datetime')
            ->addColumn('status_sent', 'integer', ['default' => 3])
            ->create();


        $table = $this->table('right_answers', ['id' => 'right_answers_id']);
        $table->addColumn('text', 'string', ['limit' => 4096])
          ->addColumn('date_added', 'datetime', ['default' => '2000-01-01 00:00:00'])
          ->addColumn('winner', 'string', ['limit' => 4096,'null' => true])
          ->addColumn('status', 'integer', ['default' => 3])
          ->create();

        $table = $this->table('phrases_messages', ['id' => 'phrases_messages_id']);
        $table->addColumn('text', 'string', ['limit' => 4096])
          ->addColumn('view', 'integer', ['default' => 0])
          ->addColumn('date_reminder', 'datetime')
          ->create();

        $table = $this->table('right_words', ['id' => 'right_words_id']);
        $table->addColumn('text', 'string', ['limit' => 4096])
          ->addColumn('date_opening', 'datetime', ['default' => '2000-01-01 00:00:00'])
          ->addColumn('who', 'string', ['limit' => 4096,'null' => true])
          ->addColumn('status', 'integer', ['default' => 3])
          ->create();

        $table = $this->table('right_letters', ['id' => 'letters_id']);
        $table->addColumn('text', 'string', ['limit' => 4096])
          ->addColumn('reason', 'string', ['limit' => 4096])
          ->addColumn('who', 'string', ['limit' => 4096,'null' => true])
          ->addColumn('status', 'integer', ['default' => 3])
          ->addColumn('date_send', 'datetime', ['default' => '2000-01-01 00:00:00'])
          ->create();
    }
}
