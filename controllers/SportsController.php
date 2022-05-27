<?php

namespace app\controllers;

use app\models\Sport;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SportsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $sports = new ActiveDataProvider([
            'query' => Sport::find()->orderBy('name DESC'),
        ]);

        $this->view->title = 'Виды спорта';
        return $this->render('index', ['sports' => $sports]);
    }

    public function actionCreate()
    {
        $sport = new Sport();
        if ($sport->load(Yii::$app->request->post()) && $sport->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Добавить вид спорта';
        return $this->render('create', ['sport' => $sport]);
    }

    public function actionUpdate($id)
    {
        $sport = $this->getSport($id);
        if ($sport->load(Yii::$app->request->post()) && $sport->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Редактировать вид спорта';
        return $this->render('update', ['sport' => $sport]);
    }

    public function actionDelete($id)
    {
        $sport = $this->getSport($id);
        $sport->delete();

        return $this->redirect(['index']);
    }

    private function getSport($id)
    {
        $sport = Sport::findOne($id);
        if ($sport === null) {
            throw new NotFoundHttpException('Вид спорта не найден');
        }

        return $sport;
    }
}
