<?php

/* @var $this yii\web\View */
/* @var $city app\models\City */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<?=$this->render('/layouts/errors', ['model' => $city])?>

<?php $form = ActiveForm::begin();?>
    <?php if (!$city->isNewRecord): ?>
        <div class="row mb-3">
            <?=Html::activeLabel($city, 'id', ['class' => 'col-sm-3 col-form-label'])?>
            <div class="col-sm-9">
                <?=Html::activeInput('text', $city, 'id', ['readonly' => true, 'class' => 'form-control-plaintext'])?>
            </div>
        </div>
    <?php endif;?>

    <div class="row mb-3">
        <?=Html::activeLabel($city, 'name', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $city, 'name', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-6 offset-sm-3">
            <?=Html::submitButton(($city->isNewRecord) ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary'])?>
            <a href="<?=Url::to(['index'])?>" class="btn btn-outline-primary">Отмена</a>
        </div>
        <?php if (!$city->isNewRecord): ?>
            <div class="col-sm-3 text-end">
                <a href="<?=Url::to(['cities/delete', 'id' => $city->id])?>" data-confirm="Вы уверены, что хотите удалить город?" data-method="post">Удалить</a>
            </div>
        <?php endif;?>
    </div>
<?php ActiveForm::end();?>
