<?php

namespace app\models;

use yii\db\ActiveRecord;

class Sport extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'safe'],
        ];
    }

    public static function getAll()
    {
        $seasons = self::find()->orderBy('started_at DESC')->all();
        return array_column($seasons, 'name', 'id');
    }
}
