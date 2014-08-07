<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/7/2014
 * Time: 2:01 AM
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
?>

<?php $form = ActiveForm::begin(['id'=>'tender_form', 'enableClientValidation'=>true, 'enableAjaxValidation'=>true]); ?>
<?= $form->field($model, 'dealId') ?>
<?= $form->field($model, 'amount') ?>
<?= Html::submitButton('立即投资') ?>
<?php ActiveForm::end(); ?>