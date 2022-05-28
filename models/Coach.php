<?php

namespace app\models;

use yii\db\ActiveRecord;

class Coach extends ActiveRecord
{
    const RESPONSE_NONE = 0;
    const RESPONSE_NAME = 1;
    const RESPONSE_AGE = 2;
    const RESPONSE_SEX = 3;
    const RESPONSE_CONTACT = 4;
    const RESPONSE_SPORT = 5;

    const AGES = [
        1 => 'Младше 20',
        '20 – 24',
        '25 – 29',
        '30 – 34',
        '35 – 39',
        '40 – 44',
        '45 – 50',
        'Старше 50',
    ];

    const SEXES = [
        1 => 'Мужчина',
        'Женщина',
    ];

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'age' => 'Возраст',
            'sex' => 'Пол',
            'contact' => 'Контакт',
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
            [['name', 'age', 'sex', 'contact', 'sport_id', 'telegram_id', 'telegram_name', 'telegram_username'], 'safe'],
        ];
    }

    public static function add($user)
    {
        $name = $user['first_name'];
        if (array_key_exists('last_name', $user)) {
            $name .= ' ' . $user['last_name'];
        }

        $coach = new Coach([
            'telegram_id' => $user['id'],
            'telegram_name' => $name,
            'telegram_username' => $user['username'] ?? '',
            'response_state' => self::RESPONSE_NONE,
        ]);
        $coach->save();

        return $coach;
    }

    public static function findByFilter($filter)
    {
        $ids = Coach::find()->select('id')->where($filter)->asArray()->column();
        if (count($ids) === 0) {
            return null;
        }

        $key = array_rand($ids);

        return Coach::findOne($ids[$key]);
    }
}
