<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\bootstrap5\Nav;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?php $this->beginPage()?>
<!doctype html>
<html lang="<?=Yii::$app->language?>">
<head>
    <meta charset="<?=Yii::$app->charset?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title><?=Html::encode($this->title)?></title>

    <?php $this->head()?>
</head>
<body>
<?php $this->beginBody()?>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?=Url::to(['/site/index'])?>">Панель управления</a>
        </div>
    </nav>

    <div class="container row">
        <div class="col-3">
            <?=Nav::widget([
                'items' => [
                    ['label' => 'Тренеры', 'url' => ['coaches/index']],
                    ['label' => 'Спортсмены', 'url' => ['students/index']],
                    ['label' => 'Города', 'url' => ['cities/index']],
                    ['label' => 'Виды спорта', 'url' => ['sports/index']],
                ],
                'options' => ['class' => 'flex-column'],
            ])?>
        </div>
        <div class="col-9">
            <?=$content?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<?php $this->endBody()?>
</body>
</html>
<?php $this->endPage()?>
