<?php

namespace app\controllers;

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

    const TYPE_STUDENT = 1;
    const TYPE_COACH = 2;

    const ACTION_TYPE = 1;
    const ACTION_AGE = 2;
    const ACTION_SEX = 3;
    const ACTION_SPORT = 4;

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
                $this->start($chat['id']);
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
                            $this->requestStudentSport($chat);
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
                                $this->requestCoachSport($chat);
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
                        $student = $this->createStudent($user);
                        $this->requestStudentName($user, $student);

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $coach = $this->createCoach($user);
                        $this->requestCoachName($user, $coach);
                    }
                    break;

                case self::ACTION_AGE:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $this->setStudentAge($user, $data['age']);
                        $this->requestStudentSex($user);

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $this->setCoachAge($user, $data['age']);
                        $this->requestCoachSex($user);
                    }
                    break;

                case self::ACTION_SEX:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $this->setStudentSex($user, $data['sex']);
                        $this->requestStudentContact($user);

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $this->setCoachSex($user, $data['sex']);
                        $this->requestCoachContact($user);
                    }
                    break;

                case self::ACTION_SPORT:
                    if ($data['type'] == self::TYPE_STUDENT) {
                        $this->setStudentSport($user, $data['sport']);
                        $this->findCoach($user, $data['sport']);

                    } elseif ($data['type'] == self::TYPE_COACH) {
                        $this->setCoachSport($user, $data['sport']);
                        $this->welcomeCoach($user);
                    }
                    break;
            }
        }
    }

    private function start($id)
    {
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
        $this->send($id, 'Подберу вам тренера для занятий или найду спорстмена для тренировки. Вы спортсмен или тренер?', $keyboard);
    }

    private function createStudent($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student !== null) {
            return;
        }

        return Student::add($user);
    }

    private function requestStudentName($user, $student)
    {
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
            return;
        }
        if ($student->response_state != Student::RESPONSE_AGE) {
            return;
        }

        $student->age = $age;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();
    }

    private function requestStudentSex($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }

        $keyboard = $this->getSexKeyboard(self::TYPE_STUDENT, Student::SEXES);
        $this->send($student->telegram_id, 'Укажите ваш пол', $keyboard);

        $student->response_state = Student::RESPONSE_SEX;
        $student->save();
    }

    private function setStudentSex($user, $sex)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }
        if ($student->response_state != Student::RESPONSE_SEX) {
            return;
        }

        $student->sex = $sex;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();
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
            return;
        }
        if ($student->response_state != Student::RESPONSE_SPORT) {
            return;
        }

        $student->sport_id = $sportId;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();
    }

    private function findCoach($user, $sportId)
    {
        $coach = Coach::findByFilter(['sport_id' => $sportId]);
        if ($coach === null) {
            $this->send($user['id'], 'Мы не смогли найти тренера по вашему запросу');
        } else {
            $sex = Coach::SEXES[$coach->sex] ?? 'Не указан пол';
            $age = Coach::AGES[$coach->age] . ' лет' ?? 'не указан возраст';
            $this->send($user['id'], "Мы нашли вам тренера:\n\n{$coach->name}\n{$sex}, {$age}\nКонтакты:\n{$coach->contact}");
        }
    }

    private function createCoach($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach !== null) {
            return;
        }

        return Coach::add($user);
    }

    private function requestCoachName($user, $coach)
    {
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
            return;
        }
        if ($coach->response_state != Coach::RESPONSE_AGE) {
            return;
        }

        $coach->age = $age;
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();
    }

    private function requestCoachSex($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }

        $keyboard = $this->getSexKeyboard(self::TYPE_COACH, Coach::SEXES);
        $this->send($coach->telegram_id, 'Укажите ваш пол', $keyboard);

        $coach->response_state = Coach::RESPONSE_SEX;
        $coach->save();
    }

    private function setCoachSex($user, $sex)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach === null) {
            return;
        }
        if ($coach->response_state != Coach::RESPONSE_SEX) {
            return;
        }

        $coach->sex = $sex;
        $coach->response_state = Coach::RESPONSE_NONE;
        $coach->save();
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
            return;
        }
        if ($coach->response_state != Student::RESPONSE_SPORT) {
            return;
        }

        $coach->sport_id = $sportId;
        $coach->response_state = Student::RESPONSE_NONE;
        $coach->save();
    }

    private function welcomeCoach($user)
    {
        $this->send($user['id'], 'Мы пришлём вам контакты спортсменов');
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

    private function getSexKeyboard($type, $sexes)
    {
        $row = [];
        foreach ($sexes as $id => $name) {
            $row[] = [
                'text' => $name,
                'callback_data' => http_build_query([
                    'action' => self::ACTION_SEX,
                    'type' => $type,
                    'sex' => $id,
                ]),
            ];
        }

        return new InlineKeyboardMarkup([$row]);
    }

    private function getSportKeyboard($type)
    {
        $sports = Sport::find()->orderBy('name')->all();
        $chunks = array_chunk($sports, 2);

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
