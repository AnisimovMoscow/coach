<?php

/* @var $this yii\web\View */
/* @var $coach app\models\Coach */

use app\models\City;
use app\models\Coach;
use app\models\Sport;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<?=$this->render('/layouts/errors', ['model' => $coach])?>

<?php $form = ActiveForm::begin();?>
    <?php if (!$coach->isNewRecord): ?>
        <div class="row mb-3">
            <?=Html::activeLabel($coach, 'id', ['class' => 'col-sm-3 col-form-label'])?>
            <div class="col-sm-9">
                <?=Html::activeInput('text', $coach, 'id', ['readonly' => true, 'class' => 'form-control-plaintext'])?>
            </div>
        </div>
    <?php endif;?>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'name', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $coach, 'name', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'age', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($coach, 'age', Coach::AGES, ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'contact', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeTextarea($coach, 'contact', ['class' => 'form-control', 'rows' => 3])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'format', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($coach, 'format', Coach::FORMATS, ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'city_id', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($coach, 'city_id', City::getAll(), ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'sport_id', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($coach, 'sport_id', Sport::getAll(), ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'about', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeTextarea($coach, 'about', ['class' => 'form-control', 'rows' => 3])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'telegram_id', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $coach, 'telegram_id', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'telegram_name', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $coach, 'telegram_name', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($coach, 'telegram_username', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $coach, 'telegram_username', ['class' => 'form-control'])?>
        </div>
    </div>

    <?php if (!$coach->isNewRecord): ?>
        <div class="row mb-3">
            <?=Html::activeLabel($coach, 'response_state', ['class' => 'col-sm-3 col-form-label'])?>
            <div class="col-sm-9">
                <?=Html::activeInput('text', $coach, 'response_state', ['readonly' => true, 'class' => 'form-control-plaintext'])?>
            </div>
        </div>
    <?php endif;?>

    <div class="row mb-3">
        <div class="col-sm-6 offset-sm-3">
            <?=Html::submitButton(($coach->isNewRecord) ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary'])?>
            <a href="<?=Url::to(['index'])?>" class="btn btn-outline-primary">Отмена</a>
        </div>
        <?php if (!$coach->isNewRecord): ?>
            <div class="col-sm-3 text-end">
                <a href="<?=Url::to(['coaches/delete', 'id' => $coach->id])?>" data-confirm="Вы уверены, что хотите удалить тренера?" data-method="post">Удалить</a>
            </div>
        <?php endif;?>
    </div>
<?php ActiveForm::end();?>
