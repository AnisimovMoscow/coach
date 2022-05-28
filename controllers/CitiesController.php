<?php

namespace app\controllers;

use app\models\City;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CitiesController extends Controller
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
        $cities = new ActiveDataProvider([
            'query' => City::find()->orderBy('name DESC'),
        ]);

        $this->view->title = 'Города';
        return $this->render('index', ['cities' => $cities]);
    }

    public function actionCreate()
    {
        $city = new City();
        if ($city->load(Yii::$app->request->post()) && $city->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Добавить город';
        return $this->render('create', ['city' => $city]);
    }

    public function actionUpdate($id)
    {
        $city = $this->getCity($id);
        if ($city->load(Yii::$app->request->post()) && $city->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Редактировать город';
        return $this->render('update', ['city' => $city]);
    }

    public function actionDelete($id)
    {
        $city = $this->getCity($id);
        $city->delete();

        return $this->redirect(['index']);
    }

    private function getCity($id)
    {
        $city = City::findOne($id);
        if ($city === null) {
            throw new NotFoundHttpException('Город не найден');
        }

        return $city;
    }
}
