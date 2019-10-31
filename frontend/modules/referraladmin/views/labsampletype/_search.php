<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\referraladmin\LabSampletypeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="lab-sampletype-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'labsampletype_id') ?>

    <?= $form->field($model, 'lab_id') ?>

    <?= $form->field($model, 'sampletype_id') ?>

    <?= $form->field($model, 'date_added') ?>

    <?= $form->field($model, 'added_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
