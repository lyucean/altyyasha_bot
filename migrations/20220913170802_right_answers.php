<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RightAnswers extends AbstractMigration
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
        $table = $this->table('right_answers', ['id' => 'right_answers_id']);
        $table->addColumn('text', 'string', ['limit' => 4096])
            ->addColumn('date_added', 'datetime')
            ->addColumn('winner', 'string', ['limit' => 4096])
            ->addColumn('status', 'integer', ['default' => 3])
            ->create();
    }
}
