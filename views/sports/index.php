<?php

/* @var $this yii\web\View */
/* @var $sports yii\data\ActiveDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<h1>Сезоны</h1>

<p>
    <a href="<?=Url::to(['sports/create'])?>" class="btn btn-outline-primary">Добавить вид спорта</a>
</p>

<?=GridView::widget([
    'dataProvider' => $sports,
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function ($sport) {
                return Html::a($sport->name, ['sports/update', 'id' => $sport->id]);
            },
        ],
    ],
])?>
