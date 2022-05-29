<?php

namespace app\controllers;

use app\models\City;
use app\models\Coach;
use app\models\Sport;
use app\models\Student;
use Exception;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

class SiteController extends Controller
{
    const COMMAND_START = '/start';
    const COMMAND_COACH = '/coach';

    const TYPE_STUDENT = 1;
    const TYPE_COACH = 2;

    const ACTION_TYPE = 1;
    const ACTION_AGE = 2;
    const ACTION_FORMAT = 3;
    const ACTION_CITY = 4;
    const ACTION_SPORT = 5;
    const ACTION_ABOUT = 6;
    const ACTION_REQUEST_FORMAT = 7;
    const ACTION_CHANGE_FORMAT = 8;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'hook' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == 'hook') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $this->view->title = 'Панель управления';
        return $this->render('index');
    }

    public function actionHook()
    {
        $update = Yii::$app->request->post();
        Yii::info(print_r($update, true), 'send');

        if (array_key_exists('message', $update)) {
            $message = $update['message'];
            $chat = $message['chat'];

            if ($chat['type'] != 'private') {
                return;
            }
            if (!array_key_exists('text', $message)) {
                return;
            }
            if ($message['text'] == self::COMMAND_START) {
                $this->start($chat);
            } elseif ($message['text'] == self::COMMAND_COACH) {
                $this->coach($chat);
            } else {
                $student = Student::findOne(['telegram_id' => $chat['id']]);
                if ($student !== null) {
                    switch ($student->response_state) {
                        case Student::RESPONSE_NAME:
                            $this->setStudentName($student, $message['text']);
                            $this->requestStudentAge($chat);
                            break;

                        case Student::RESPONSE_CONTACT:
                            $this->setStudentContact($student, $message['text']);
                            $this->requestStudentFormat($chat, self::ACTION_FORMAT);
                            break;
                    }
                } else {
                    $coach = Coach::findOne(['telegram_id' => $chat['id']]);
                    if ($coach !== null) {
                        switch ($coach->response_state) {
                            case Coach::RESPONSE_NAME:
                                $this->setCoachName($coach, $message['text']);
                                $this->requestCoachAge($chat);
                                break;

                            case Coach::RESPONSE_CONTACT:
                                $this->setCoachContact($coach, $message['text']);
                                $this->requestCoachFormat($chat);
                                break;

                            case Coach::RESPONSE_ABOUT:
                                $this->setCoachAbout($coach, $message['text']);
                                $this->welcomeCoach($chat);
                                break;
                        }
                    }
                }
            }
        } elseif (array_key_exists('callback_query', $update)) {
            parse_str($update['callback_query']['data'], $data);
            $user = $update['callback_query']['from'];

            switch ($data['action']) {
                case self::ACTION_TYPE:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $ok = $this->createStudent($user);
                        if ($ok) {
                            $this->requestStudentName($user);
                        }

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $ok = $this->createCoach($user);
                        if ($ok) {
                            $this->requestCoachName($user);
                        }
                    }
                    break;

                case self::ACTION_AGE:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $ok = $this->setStudentAge($user, $data['age']);
                        if ($ok) {
                            $this->requestStudentContact($user);
                        }

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $ok = $this->setCoachAge($user, $data['age']);
                        if ($ok) {
                            $this->requestCoachContact($user);
                        }
                    }
                    break;

                case self::ACTION_FORMAT:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $ok = $this->setStudentFormat($user, $data['format']);
                        if ($ok) {
                            $this->requestStudentCity($user);
                        }

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $ok = $this->setCoachFormat($user, $data['format']);
                        if ($ok) {
                            $this->requestCoachCity($user);
                        }
                    }
                    break;

                case self::ACTION_CITY:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $ok = $this->setStudentCity($user, $data['city']);
                        if ($ok) {
                            $this->requestStudentSport($user);
                        }

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $ok = $this->setCoachCity($user, $data['city']);
                        if ($ok) {
                            $this->requestCoachSport($user);
                        }
                    }
                    break;

                case self::ACTION_SPORT:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $ok = $this->setStudentSport($user, $data['sport']);
                        if ($ok) {
                            $this->findCoach($user);
                        }

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $ok = $this->setCoachSport($user, $data['sport']);
                        if ($ok) {
                            $this->requestCoachAbout($user);
                        }
                    }
                    break;

                case self::ACTION_REQUEST_FORMAT:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $this->requestStudentFormat($user, self::ACTION_CHANGE_FORMAT);
                    }
                    break;

                case self::ACTION_CHANGE_FORMAT:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $ok = $this->setStudentFormat($user, $data['format']);
                        if ($ok) {
                            $this->findCoach($user);
                        }
                    }
                    break;
            }
        }
    }

    private function start($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student !== null) {
            $coach = $student->getCoach();
            if ($coach !== null) {
                $info = $this->getCoachInfo($coach);
                $this->send($user['id'], "Ваш тренер:\n\n{$info}");
            } else {
                $this->send($user['id'], 'Не смогли подобрать тренера для вас');
            }
            return;
        }

        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach !== null) {
            $this->send($user['id'], 'Мы пришлём вам контакты спортсменов');
            return;
        }

        $keyboard = new InlineKeyboardMarkup([
            [
                [
                    'text' => 'Спортсмен',
                    'callback_data' => http_build_query([
                        'action' => self::ACTION_TYPE,
                        'type' => self::TYPE_STUDENT,
                    ]),
                ],
                [
                    'text' => 'Тренер',
                    'callback_data' => http_build_query([
                        'action' => self::ACTION_TYPE,
                        'type' => self::TYPE_COACH,
                    ]),
                ],
            ],
        ]);
        $this->send($user['id'], 'Подберу вам тренера для занятий или найду спорстмена для тренировки. Вы спортсмен или тренер?', $keyboard);
    }

    private function coach($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            $this->send($user['id'], 'Это работает только для спортсменов');
            return;
        }

        $coach = $student->getCoach();
        if ($coach === null) {
            $this->send($user['id'], 'Не смогли подобрать тренера для вас');
            return;
        }

        $info = $this->getCoachInfo($coach);
        $this->send($user['id'], "Ваш тренер:\n\n{$info}");
    }

    private function createStudent($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student !== null) {
            $this->send($user['id'], 'Если вы хотите посмотреть своего тренера, нажмите /coach');
            return false;
        }

        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach !== null) {
            $this->send($user['id'], 'Вы уже зарегистрированы как тренер');
            return false;
        }

        Student::add($user);

        return true;
    }

    private function requestStudentName($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $this->send($user['id'], 'Отправьте ваше имя. Оно будет отображаться тренеру');

        $student->response_state = Student::RESPONSE_NAME;
        $student->save();
    }

    private function setStudentName($student, $text)
    {
        $student->name = trim($text);
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();
    }

    private function requestStudentAge($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $keyboard = $this->getAgeKeyboard(self::TYPE_STUDENT, Student::AGES);
        $this->send($student->telegram_id, 'Укажите ваш возраст', $keyboard);

        $student->response_state = Student::RESPONSE_AGE;
        $student->save();
    }

    private function setStudentAge($user, $age)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return false;
        }
        if ($student->response_state != Student::RESPONSE_AGE) {
            return false;
        }

        $student->age = $age;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();

        return true;
    }

    private function requestStudentContact($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $this->send($user['id'], 'Отправьте контакты для связи (телефон или почта). Они будут отображаться вашему тренеру');

        $student->response_state = Student::RESPONSE_CONTACT;
        $student->save();
    }

    private function setStudentContact($student, $text)
    {
        $student->contact = trim($text);
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();
    }

    private function requestStudentFormat($user, $action)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $keyboard = $this->getFormatKeyboard(self::TYPE_STUDENT, $action);
        $this->send($student->telegram_id, 'Выберите желаемый формат тренировок', $keyboard);

        $student->response_state = Student::RESPONSE_FORMAT;
        $student->save();
    }

    private function setStudentFormat($user, $format)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return false;
        }
        if ($student->response_state != Student::RESPONSE_FORMAT) {
            return false;
        }

        $student->format = $format;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();

        return true;
    }

    private function requestStudentCity($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $keyboard = $this->getCityKeyboard(self::TYPE_STUDENT, $student->format);
        $this->send($student->telegram_id, 'Выберите ваш город', $keyboard);

        $student->response_state = Student::RESPONSE_CITY;
        $student->save();
    }

    private function setStudentCity($user, $cityId)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return false;
        }
        if ($student->response_state != Student::RESPONSE_CITY) {
            return false;
        }

        $student->city_id = $cityId;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();

        return true;
    }

    private function requestStudentSport($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $keyboard = $this->getSportKeyboard(self::TYPE_STUDENT);
        $this->send($student->telegram_id, 'Выберите вид спорта для тренировок', $keyboard);

        $student->response_state = Student::RESPONSE_SPORT;
        $student->save();
    }

    private function setStudentSport($user, $sportId)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return false;
        }
        if ($student->response_state != Student::RESPONSE_SPORT) {
            return false;
        }

        $student->sport_id = $sportId;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();

        return true;
    }

    private function findCoach($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $coach = Coach::findByFilter($student);
        if ($coach === null) {
            $keyboard = new InlineKeyboardMarkup([
                [
                    [
                        'text' => 'Поменять формат',
                        'callback_data' => http_build_query([
                            'action' => self::ACTION_REQUEST_FORMAT,
                            'type' => self::TYPE_STUDENT,
                        ]),
                    ],
                ],
            ]);
            $this->send($user['id'], 'Мы не смогли найти тренера по вашему запросу, но вы можете поменять желаемый формат тренировок и возможно получится подобрать тренера', $keyboard);
        } else {
            $info = $this->getCoachInfo($coach);
            $this->send($user['id'], "Мы нашли вам тренера:\n\n{$info}");
        }
    }

    private function createCoach($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach !== null) {
            $this->send($user['id'], 'Мы пришлём вам контакты спортсменов');
            return false;
        }

        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student !== null) {
            $this->send($user['id'], 'Вы уже зарегистрированы как спортсмен');
            return false;
        }

        Coach::add($user);

        return true;
    }

    private function requestCoachName($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $this->send($user['id'], 'Отправьте ваше имя. Оно будет отображаться спортсменам');

        $coach->response_state = Coach::RESPONSE_NAME;
        $coach->save();
    }

    private function setCoachName($coach, $text)
    {
        $coach->name = trim($text);
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();
    }

    private function requestCoachAge($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $keyboard = $this->getAgeKeyboard(self::TYPE_COACH, Coach::AGES);
        $this->send($coach->telegram_id, 'Укажите ваш возраст', $keyboard);

        $coach->response_state = Coach::RESPONSE_AGE;
        $coach->save();
    }

    private function setCoachAge($user, $age)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return false;
        }
        if ($coach->response_state != Coach::RESPONSE_AGE) {
            return false;
        }

        $coach->age = $age;
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();

        return true;
    }

    private function requestCoachContact($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $this->send($user['id'], 'Отправьте контакты для связи (телефон или почта). Они будут отображаться спортсменам');

        $coach->response_state = Coach::RESPONSE_CONTACT;
        $coach->save();
    }

    private function setCoachContact($coach, $text)
    {
        $coach->contact = trim($text);
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();
    }

    private function requestCoachFormat($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $keyboard = $this->getFormatKeyboard(self::TYPE_COACH, self::ACTION_FORMAT);
        $this->send($coach->telegram_id, 'Выберите желаемый формат тренировок', $keyboard);

        $coach->response_state = Coach::RESPONSE_FORMAT;
        $coach->save();
    }

    private function setCoachFormat($user, $format)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return false;
        }
        if ($coach->response_state != Coach::RESPONSE_FORMAT) {
            return false;
        }

        $coach->format = $format;
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();

        return true;
    }

    private function requestCoachCity($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $keyboard = $this->getCityKeyboard(self::TYPE_COACH, $coach->format);
        $this->send($coach->telegram_id, 'Укажите ваш город', $keyboard);

        $coach->response_state = Coach::RESPONSE_CITY;
        $coach->save();
    }

    private function setCoachCity($user, $cityId)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return false;
        }
        if ($coach->response_state != Coach::RESPONSE_CITY) {
            return false;
        }

        $coach->city_id = $cityId;
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();

        return true;
    }

    private function requestCoachSport($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $keyboard = $this->getSportKeyboard(self::TYPE_COACH);
        $this->send($coach->telegram_id, 'Выберите вид спорта, которым вы занимаетесь', $keyboard);

        $coach->response_state = Coach::RESPONSE_SPORT;
        $coach->save();
    }

    private function setCoachSport($user, $sportId)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return false;
        }
        if ($coach->response_state != Student::RESPONSE_SPORT) {
            return false;
        }

        $coach->sport_id = $sportId;
        $coach->response_state = Student::RESPONSE_NONE;
        $coach->save();

        return true;
    }

    private function requestCoachAbout($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $this->send($user['id'], 'Расскажите немного о себе. Ваша квалификация, опыт');

        $coach->response_state = Coach::RESPONSE_ABOUT;
        $coach->save();
    }

    private function setCoachAbout($coach, $text)
    {
        $coach->about = trim($text);
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();
    }

    private function welcomeCoach($user)
    {
        $this->send($user['id'], 'Мы пришлём вам контакты спортсменов');
    }

    private function getCoachInfo($coach)
    {
        $info = "{$coach->name}\n";

        $age = array_key_exists($coach->age, Coach::AGES) ? Coach::AGES[$coach->age] . ' лет' : 'Не указан возраст';
        $info .= "{$age}\n\n";

        $format = array_key_exists($coach->format, Coach::FORMATS) ? 'Форматы тренировок: ' . Coach::FORMATS[$coach->format] : 'Не указаны форматы тренировок';
        $info .= "{$format}\n";

        $city = ($coach->city !== null) ? 'Город: ' . $coach->city->name : 'Не указан город';
        $info .= "{$city}\n\n";

        $info .= "Контакты:\n{$coach->contact}\n\n";
        if (!empty($coach->telegram_username)) {
            $info .= "Телеграм: https://t.me/{$coach->telegram_username}\n\n";
        }

        $info .= "О себе:\n{$coach->about}";

        return $info;
    }

    private function getAgeKeyboard($type, $ages)
    {
        $chunks = array_chunk($ages, 3, true);

        $buttons = [];
        foreach ($chunks as $ages) {
            $row = [];
            foreach ($ages as $id => $name) {
                $row[] = [
                    'text' => $name,
                    'callback_data' => http_build_query([
                        'action' => self::ACTION_AGE,
                        'type' => $type,
                        'age' => $id,
                    ]),
                ];
            }
            $buttons[] = $row;
        }

        return new InlineKeyboardMarkup($buttons);
    }

    private function getFormatKeyboard($type, $action)
    {
        $row = [];
        foreach (Coach::FORMATS as $id => $name) {
            $row[] = [
                'text' => $name,
                'callback_data' => http_build_query([
                    'action' => $action,
                    'type' => $type,
                    'format' => $id,
                ]),
            ];
        }

        return new InlineKeyboardMarkup([$row]);
    }

    private function getCityKeyboard($type, $format)
    {
        $cities = City::find()->orderBy('name')->all();
        if ($format != Coach::FORMAT_OFFLINE) {
            $cities[] = new City([
                'id' => 0,
                'name' => 'другой',
            ]);
        }
        $chunks = array_chunk($cities, 3);

        $buttons = [];
        foreach ($chunks as $cities) {
            $row = [];
            foreach ($cities as $city) {
                $row[] = [
                    'text' => $city->name,
                    'callback_data' => http_build_query([
                        'action' => self::ACTION_CITY,
                        'type' => $type,
                        'city' => $city->id,
                    ]),
                ];
            }
            $buttons[] = $row;
        }

        return new InlineKeyboardMarkup($buttons);
    }

    private function getSportKeyboard($type)
    {
        $sports = Sport::find()->orderBy('name')->all();
        $chunks = array_chunk($sports, 3);

        $buttons = [];
        foreach ($chunks as $sports) {
            $row = [];
            foreach ($sports as $sport) {
                $row[] = [
                    'text' => $sport->name,
                    'callback_data' => http_build_query([
                        'action' => self::ACTION_SPORT,
                        'type' => $type,
                        'sport' => $sport->id,
                    ]),
                ];
            }
            $buttons[] = $row;
        }

        return new InlineKeyboardMarkup($buttons);
    }

    private function send($chatId, $text, $keyboard = null)
    {
        $token = Yii::$app->params['token'];
        $bot = new BotApi($token);

        try {
            $bot->sendMessage($chatId, $text, null, false, null, $keyboard);
        } catch (Exception $e) {
            Yii::error('Send error. Message: ' . $e->getMessage() . ' Code: ' . $e->getCode(), 'send');
        }
    }
}
