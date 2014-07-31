<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \frontend\models\SignupForm $model
 */
$this->title = Yii::t('yii', 'Signup');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup','enableClientValidation'=>true, 'enableAjaxValidation'=>true]); ?>
                <?= $form->field($model, 'username') ?>
                <?= $form->field($model, 'email') ?>
                <?= $form->field($model, 'mobile') ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'repeatpassword')->passwordInput() ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('yii', 'Signup'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
