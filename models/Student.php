<?php

namespace app\models;

use yii\db\ActiveRecord;

class Student extends ActiveRecord
{
    const RESPONSE_NONE = 0;
    const RESPONSE_NAME = 1;
    const RESPONSE_SPORT = 2;

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'sport_id' => 'Вид спорта',
            'telegram_id' => 'ID в Telegram',
            'telegram_name' => 'Имя в Telegram',
            'telegram_username' => 'Ник в Telegram',
            'response_state' => 'Состояние ответа',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'sport_id', 'telegram_id', 'telegram_name', 'telegram_username'], 'safe'],
        ];
    }

    public static function add($user)
    {
        $name = $user['first_name'];
        if (array_key_exists('last_name', $user)) {
            $name .= ' ' . $user['last_name'];
        }

        $student = new Student([
            'telegram_id' => $user['id'],
            'telegram_name' => $name,
            'telegram_username' => $user['username'] ?? '',
            'response_state' => self::RESPONSE_NONE,
        ]);
        $student->save();

        return $student;
    }
}
