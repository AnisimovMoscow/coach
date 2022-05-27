<?php

use yii\db\Migration;

class m220527_171749_add_coach extends Migration
{
    public function up()
    {
        $this->createTable('coach', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'sport_id' => $this->integer(),
        ]);
        $this->createIndex('sport', 'coach', 'sport_id');
    }

    public function down()
    {
        $this->dropTable('coach');
    }
}
