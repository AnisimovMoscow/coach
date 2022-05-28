<?php

/* @var $this yii\web\View */
/* @var $sport app\models\Sport */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<?=$this->render('/layouts/errors', ['model' => $sport])?>

<?php $form = ActiveForm::begin();?>
    <?php if (!$sport->isNewRecord): ?>
        <div class="row mb-3">
            <?=Html::activeLabel($sport, 'id', ['class' => 'col-sm-3 col-form-label'])?>
            <div class="col-sm-9">
                <?=Html::activeInput('text', $sport, 'id', ['readonly' => true, 'class' => 'form-control-plaintext'])?>
            </div>
        </div>
    <?php endif;?>

    <div class="row mb-3">
        <?=Html::activeLabel($sport, 'name', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $sport, 'name', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-6 offset-sm-3">
            <?=Html::submitButton(($sport->isNewRecord) ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary'])?>
            <a href="<?=Url::to(['index'])?>" class="btn btn-outline-primary">Отмена</a>
        </div>
        <?php if (!$sport->isNewRecord): ?>
            <div class="col-sm-3 text-end">
                <a href="<?=Url::to(['sports/delete', 'id' => $sport->id])?>" data-confirm="Вы уверены, что хотите удалить вид спорта?" data-method="post">Удалить</a>
            </div>
        <?php endif;?>
    </div>
<?php ActiveForm::end();?>
