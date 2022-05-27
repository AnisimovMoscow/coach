<?php

use yii\db\Migration;

class m220527_164733_add_sport extends Migration
{
    public function up()
    {
        $this->createTable('sport', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
        ]);
    }

    public function down()
    {
        $this->dropTable('sport');
    }
}
