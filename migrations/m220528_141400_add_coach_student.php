<?php

use yii\db\Migration;

class m220528_141400_add_coach_student extends Migration
{
    public function up()
    {
        $this->createTable('coach_student', [
            'id' => $this->primaryKey(),
            'coach_id' => $this->integer(),
            'student_id' => $this->integer(),
        ]);
    }

    public function down()
    {
        $this->dropTable('coach_student');
    }
}
