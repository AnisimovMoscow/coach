<?php

use yii\db\Migration;

class m220527_203102_add_student extends Migration
{
    public function up()
    {
        $this->createTable('student', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'sport_id' => $this->integer(),
            'telegram_id' => $this->string(),
            'telegram_name' => $this->string(),
            'telegram_username' => $this->string(),
            'response_state' => $this->integer(),
        ]);
        $this->createIndex('telegram', 'student', 'telegram_id');
    }

    public function down()
    {
        $this->dropTable('student');
    }
}
