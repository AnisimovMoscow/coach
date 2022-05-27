<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception yii\web\HttpException */

use yii\helpers\Html;

$this->title = 'Ошибка: ' . $name;

?>
<h1>Ошибка: <?=$name?></h1>

<p>
    <?=nl2br(Html::encode($message))?>
</p>
