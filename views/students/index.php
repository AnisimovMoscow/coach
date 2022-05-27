<?php

/* @var $this yii\web\View */
/* @var $students yii\data\ActiveDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<h1>Спортсмены</h1>

<p>
    <a href="<?=Url::to(['students/create'])?>" class="btn btn-outline-primary">Добавить спортсмена</a>
</p>

<?=GridView::widget([
    'dataProvider' => $students,
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function ($student) {
                return Html::a($student->name, ['students/update', 'id' => $student->id]);
            },
        ],
        [
            'attribute' => 'telegram_name',
            'format' => 'raw',
            'value' => function ($student) {
                return Html::a($student->telegram_name, ['students/update', 'id' => $student->id]);
            },
        ],
    ],
])?>
