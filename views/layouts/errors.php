<?php

/* @var $this \yii\web\View */
/* @var $model \yii\db\ActiveRecord */

use yii\helpers\Html;

?>
<?php if ($model->hasErrors()): ?>
    <div class="alert alert-danger">
        <strong>Ошибка:</strong>
        <?php $errors = $model->getErrors();?>
        <ul class="list-unstyled">
        <?php foreach ($errors as $field => $fieldErrors): ?>
            <?php foreach ($fieldErrors as $error): ?>
                <li>
                    <?=Html::encode($error)?>
                </li>
            <?php endforeach;?>
        <?php endforeach;?>
        </ul>
    </div>
<?php endif;?>
