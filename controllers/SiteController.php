<?php

namespace app\controllers;

use Exception;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    const COMMAND_START = '/start';

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

    public function actionIndex()
    {
        $this->view->title = 'Панель управления';
        return $this->render('index');
    }

    public function actionHook()
    {
        $update = Yii::$app->request->post();
        Yii::info(print_r($update, true), 'send');

        if (!array_key_exists('message', $update)) {
            return;
        }
        $message = $update['message'];
        $chat = $message['chat'];

        if ($chat['type'] === 'private' && array_key_exists('text', $message) && $message['text'] === self::COMMAND_START) {
            $this->start($chat['id']);
        }
    }

    private function start($id)
    {
        $token = Yii::$app->params['token'];
        $bot = new BotApi($token);

        $keyboard = new InlineKeyboardMarkup([
            [
                [
                    'text' => 'Спортсмен',
                    'callback_data' => 'student',
                ],
                [
                    'text' => 'Тренер',
                    'callback_data' => 'coach',
                ],
            ],
        ]);

        try {
            $bot->sendMessage($id, 'Подберу вам тренера для занятий или найду спорстмена для тренировки. Ты тренер или спортсмен?', null, false, null, $keyboard);
        } catch (Exception $e) {
            Yii::error('Send error. Message: ' . $e->getMessage() . ' Code: ' . $e->getCode(), 'send');
        }
    }
}
