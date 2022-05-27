<?php

namespace app\controllers;

use app\models\Coach;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CoachesController extends Controller
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
        $coaches = new ActiveDataProvider([
            'query' => Coach::find()->orderBy('id DESC'),
        ]);

        $this->view->title = 'Тренеры';
        return $this->render('index', ['coaches' => $coaches]);
    }

    public function actionCreate()
    {
        $coach = new Coach();
        if ($coach->load(Yii::$app->request->post()) && $coach->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Добавить тренера';
        return $this->render('create', ['coach' => $coach]);
    }

    public function actionUpdate($id)
    {
        $coach = $this->getCoach($id);
        if ($coach->load(Yii::$app->request->post()) && $coach->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Редактировать тренера';
        return $this->render('update', ['coach' => $coach]);
    }

    public function actionDelete($id)
    {
        $coach = $this->getCoach($id);
        $coach->delete();

        return $this->redirect(['index']);
    }

    private function getCoach($id)
    {
        $coach = Coach::findOne($id);
        if ($coach === null) {
            throw new NotFoundHttpException('Тренер не найден');
        }

        return $coach;
    }
}
