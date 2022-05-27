<?php

/* @var $this yii\web\View */
/* @var $coaches yii\data\ActiveDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<h1>Тренеры</h1>

<p>
    <a href="<?=Url::to(['coaches/create'])?>" class="btn btn-outline-primary">Добавить тренера</a>
</p>

<?=GridView::widget([
    'dataProvider' => $coaches,
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function ($coach) {
                return Html::a($coach->name, ['coaches/update', 'id' => $coach->id]);
            },
        ],
    ],
])?>
