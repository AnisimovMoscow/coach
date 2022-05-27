<?php

namespace app\models;

use yii\db\ActiveRecord;

class Coach extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'sport_id' => 'Вид спорта',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'sport_id'], 'safe'],
        ];
    }
}
