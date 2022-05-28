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
    const RESPONSE_FORMAT = 5;
    const RESPONSE_CITY = 6;
    const RESPONSE_SPORT = 7;

    const FORMAT_ONLINE = 1;
    const FORMAT_OFFLINE = 2;
    const FORMAT_ANY = 3;

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

    const SEXES = [
        1 => 'Мужчина',
        2 => 'Женщина',
        3 => 'Не скажу',
    ];

    const FORMATS = [
        self::FORMAT_ONLINE => 'Онлайн',
        self::FORMAT_OFFLINE => 'Оффлайн',
        self::FORMAT_ANY => 'Любой',
    ];

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'age' => 'Возраст',
            'sex' => 'Пол',
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
            [['name', 'age', 'sex', 'contact', 'format', 'city_id', 'sport_id', 'telegram_id', 'telegram_name', 'telegram_username'], 'safe'],
        ];
    }

    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
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

    public static function findByFilter($student)
    {
        $query = Coach::find()->select('id')->where(['sport_id' => $student->sport_id]);

        switch ($student->format) {
            case Coach::FORMAT_ONLINE:
                $query->andWhere(['format' => [Coach::FORMAT_ONLINE, Coach::FORMAT_ANY]]);
                break;

            case Coach::FORMAT_OFFLINE:
                $query->andWhere([
                    'format' => [Coach::FORMAT_OFFLINE, Coach::FORMAT_ANY],
                    'city_id' => $student->city_id,
                ]);
                break;

            case Coach::FORMAT_ANY:
                $query->andWhere('format = :any OR (format = :offline AND city_id = :city)', [
                    'any' => Coach::FORMAT_ANY,
                    'offline' => Coach::FORMAT_OFFLINE,
                    'city' => $student->city_id,
                ]);
                break;
        }

        $ids = $query->asArray()->column();
        if (count($ids) === 0) {
            return null;
        }

        $key = array_rand($ids);

        return Coach::findOne($ids[$key]);
    }
}
