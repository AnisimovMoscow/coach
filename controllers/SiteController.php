<?php

namespace app\controllers;

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

            if ($chat['type'] === 'private' && array_key_exists('text', $message) && $message['text'] === self::COMMAND_START) {
                $this->start($chat['id']);
            }
        } elseif (array_key_exists('callback_query', $update)) {
            if ($update['callback_query']['data'] === self::TYPE_STUDENT) {
                $this->createStudent($update['callback_query']['from']);

            } elseif ($update['callback_query']['data'] === self::TYPE_COACH) {
                $this->createCoach($update['callback_query']['from']);
            }
        }
    }

    private function start($id)
    {
        $keyboard = new InlineKeyboardMarkup([
            [
                [
                    'text' => 'Спортсмен',
                    'callback_data' => self::TYPE_STUDENT,
                ],
                [
                    'text' => 'Тренер',
                    'callback_data' => self::TYPE_COACH,
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

        Student::add($user);

        $this->send($user['id'], 'Отправьте ваше имя. Оно будет отображаться тренеру');
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
