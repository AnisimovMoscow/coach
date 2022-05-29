<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Student extends ActiveRecord
{
    const RESPONSE_NONE = 0;
    const RESPONSE_NAME = 1;
    const RESPONSE_AGE = 2;
    const RESPONSE_CONTACT = 3;
    const RESPONSE_FORMAT = 4;
    const RESPONSE_CITY = 5;
    const RESPONSE_SPORT = 6;

    const AGES = [
        1 => 'Младше 20',
        2 => '20 – 24',
        3 => '25 – 29',
        4 => '30 – 34',
        5 => '35 – 39',
        6 => '40 – 44',
        7 => '45 – 49',
        8 => 'Старше 50',
        9 => 'Не скажу',
    ];

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'age' => 'Возраст',
            'contact' => 'Контакт',
            'format' => 'Формат тренировок',
            'city_id' => 'Город',
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
            [['name', 'age', 'contact', 'format', 'city_id', 'sport_id', 'telegram_id', 'telegram_name', 'telegram_username'], 'safe'],
        ];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        Yii::$app->db->createCommand()->delete('coach_student', ['student_id' => $this->id])->execute();
        return true;
    }

    public function getCoach()
    {
        return Coach::find()->innerJoin('coach_student cs', 'cs.coach_id = coach.id')->where(['cs.student_id' => $this->id])->one();
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
