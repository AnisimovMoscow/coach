<?php

namespace app\controllers;

use app\models\Student;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class StudentsController extends Controller
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
        $students = new ActiveDataProvider([
            'query' => Student::find()->orderBy('id DESC'),
        ]);

        $this->view->title = 'Спортсмены';
        return $this->render('index', ['students' => $students]);
    }

    public function actionCreate()
    {
        $student = new Student();
        if ($student->load(Yii::$app->request->post()) && $student->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Добавить спортсмена';
        return $this->render('create', ['student' => $student]);
    }

    public function actionUpdate($id)
    {
        $student = $this->getStudent($id);
        if ($student->load(Yii::$app->request->post()) && $student->save()) {
            return $this->redirect(['index']);
        }

        $this->view->title = 'Редактировать спортсмена';
        return $this->render('update', ['student' => $student]);
    }

    public function actionDelete($id)
    {
        $student = $this->getStudent($id);
        $student->delete();

        return $this->redirect(['index']);
    }

    private function getStudent($id)
    {
        $student = Student::findOne($id);
        if ($student === null) {
            throw new NotFoundHttpException('Спортсмен не найден');
        }

        return $student;
    }
}
