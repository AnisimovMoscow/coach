<?php

namespace app\models;

use yii\db\ActiveRecord;

class City extends ActiveRecord
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
        $cities = self::find()->orderBy('name')->all();
        return array_column($cities, 'name', 'id');
    }
}
