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

    const TYPE_COACH = 'coach';
    const TYPE_STUDENT = 'student';

    const ACTION_TYPE = 'type';
    const ACTION_SPORT = 'sport';

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

            if ($chat['type'] !== 'private') {
                return;
            }
            if (!array_key_exists('text', $message)) {
                return;
            }
            if ($message['text'] === self::COMMAND_START) {
                $this->start($chat['id']);
            } else {
                $student = Student::findOne(['telegram_id' => $chat['id']]);
                if ($student !== null && $student->response_state === Student::RESPONSE_NAME) {
                    $this->setStudentName($student, $message['text']);
                }
                $coach = Coach::findOne(['telegram_id' => $chat['id']]);
                if ($coach !== null && $coach->response_state === Student::RESPONSE_NAME) {
                    $this->setCoachName($coach, $message['text']);
                }
            }
        } elseif (array_key_exists('callback_query', $update)) {
            parse_str($update['callback_query']['data'], $data);

            switch ($data['action']) {
                case self::ACTION_TYPE:
                    if ($data['type'] === self::TYPE_STUDENT) {
                        $this->createStudent($update['callback_query']['from']);
                    } elseif ($data['type'] === self::TYPE_COACH) {
                        $this->createCoach($update['callback_query']['from']);
                    }
                    break;

                case self::ACTION_SPORT:
                    if ($data['type'] === self::TYPE_STUDENT) {
                        $this->setStudentSport($update['callback_query']['from'], $data['sport']);
                    } elseif ($data['type'] === self::TYPE_COACH) {
                        $this->setCoachSport($update['callback_query']['from'], $data['sport']);
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
        $this->send($id, 'Подберу вам тренера для занятий или найду спорстмена для тренировки. Ты тренер или спортсмен?', $keyboard);
    }

    private function createStudent($user)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student !== null) {
            return;
        }

        $student = Student::add($user);

        $this->send($user['id'], 'Отправьте ваше имя. Оно будет отображаться тренеру');

        $student->response_state = Student::RESPONSE_NAME;
        $student->save();
    }

    private function setStudentName($student, $text)
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
                        'type' => self::TYPE_STUDENT,
                        'sport' => $sport->id,
                    ]),
                ];
            }
            $buttons[] = $row;
        }
        $keyboard = new InlineKeyboardMarkup([$buttons]);
        $this->send($student->telegram_id, 'Выберите вид спорта для тренировок', $keyboard);

        $student->name = trim($text);
        $student->response_state = Student::RESPONSE_SPORT;
        $student->save();
    }

    private function setStudentSport($user, $sportId)
    {
        $student = Student::findOne(['telegram_id' => $user['id']]);
        if ($student === null) {
            return;
        }
        if ($student->response_state !== Student::RESPONSE_SPORT) {
            return;
        }

        $coach = Coach::findByFilter(['sport_id' => $sportId]);
        if ($coach === null) {
            $this->send($user['id'], 'Мы не смогли найти тренера по вашему запросу');
        } else {
            $this->send($user['id'], 'Мы нашли вам тренера – ' . $coach->name);
        }

        $student->sport_id = $sportId;
        $student->response_state = Student::RESPONSE_NONE;
        $student->save();
    }

    private function createCoach($user)
    {
        $coach = Coach::findOne(['telegram_id' => $user['id']]);
        if ($coach !== null) {
            return;
        }

        Coach::add($user);

        $this->send($user['id'], 'Отправьте ваше имя. Оно будет отображаться спортсменам');
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
