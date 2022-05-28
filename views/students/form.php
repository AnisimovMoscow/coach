<?php

/* @var $this yii\web\View */
/* @var $student app\models\Student */

use app\models\City;
use app\models\Coach;
use app\models\Sport;
use app\models\Student;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<?=$this->render('/layouts/errors', ['model' => $student])?>

<?php $form = ActiveForm::begin();?>
    <?php if (!$student->isNewRecord): ?>
        <div class="row mb-3">
            <?=Html::activeLabel($student, 'id', ['class' => 'col-sm-3 col-form-label'])?>
            <div class="col-sm-9">
                <?=Html::activeInput('text', $student, 'id', ['readonly' => true, 'class' => 'form-control-plaintext'])?>
            </div>
        </div>
    <?php endif;?>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'name', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $student, 'name', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'age', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($student, 'age', Student::AGES, ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'sex', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($student, 'sex', Student::SEXES, ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'contact', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeTextarea($student, 'contact', ['class' => 'form-control', 'rows' => 3])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'format', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($student, 'format', Coach::FORMATS, ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'city_id', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($student, 'city_id', City::getAll(), ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'sport_id', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeDropDownList($student, 'sport_id', Sport::getAll(), ['class' => 'form-select', 'prompt' => '—'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'telegram_id', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $student, 'telegram_id', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'telegram_name', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $student, 'telegram_name', ['class' => 'form-control'])?>
        </div>
    </div>

    <div class="row mb-3">
        <?=Html::activeLabel($student, 'telegram_username', ['class' => 'col-sm-3 col-form-label'])?>
        <div class="col-sm-9">
            <?=Html::activeInput('text', $student, 'telegram_username', ['class' => 'form-control'])?>
        </div>
    </div>

    <?php if (!$student->isNewRecord): ?>
        <div class="row mb-3">
            <?=Html::activeLabel($student, 'response_state', ['class' => 'col-sm-3 col-form-label'])?>
            <div class="col-sm-9">
                <?=Html::activeInput('text', $student, 'response_state', ['readonly' => true, 'class' => 'form-control-plaintext'])?>
            </div>
        </div>
    <?php endif;?>

    <div class="row mb-3">
        <div class="col-sm-6 offset-sm-3">
            <?=Html::submitButton(($student->isNewRecord) ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary'])?>
            <a href="<?=Url::to(['index'])?>" class="btn btn-outline-primary">Отмена</a>
        </div>
        <?php if (!$student->isNewRecord): ?>
            <div class="col-sm-3 text-end">
                <a href="<?=Url::to(['students/delete', 'id' => $student->id])?>" data-confirm="Вы уверены, что хотите удалить спорстмена?" data-method="post">Удалить</a>
            </div>
        <?php endif;?>
    </div>
<?php ActiveForm::end();?>
