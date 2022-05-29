<?php

use yii\db\Migration;

class m220529_123330_remove_sex extends Migration
{
    public function up()
    {
        $this->dropColumn('coach', 'sex');
        $this->dropColumn('student', 'sex');

        $this->alterColumn('coach', 'contact', $this->text());
        $this->alterColumn('coach', 'about', $this->text());
        $this->alterColumn('student', 'contact', $this->text());
    }

    public function down()
    {
        $this->addColumn('coach', 'sex', $this->integer());
        $this->addColumn('student', 'sex', $this->integer());


        $this->alterColumn('coach', 'contact', $this->string());
        $this->alterColumn('coach', 'about', $this->string());
        $this->alterColumn('student', 'contact', $this->string());
    }
}
