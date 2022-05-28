<?php

/* @var $this yii\web\View */
/* @var $cities yii\data\ActiveDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<h1>Города</h1>

<p>
    <a href="<?=Url::to(['cities/create'])?>" class="btn btn-outline-primary">Добавить город</a>
</p>

<?=GridView::widget([
    'dataProvider' => $cities,
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function ($city) {
                return Html::a($city->name, ['cities/update', 'id' => $city->id]);
            },
        ],
    ],
])?>
