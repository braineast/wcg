<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/30/2014
 * Time: 2:27 AM
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>
<style>
    .help-block {
        margin: 10px;
        color: red;
        font-size: 2em;
    }
</style>
<div class="main_content" style=" min-height:1048px;">
    <div class="register">
        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation'=>true, 'enableAjaxValidation'=>true]); ?>
        <table cellpadding="0" cellspacing="0" class="borderBt" style=" margin-top:0">
            <tbody><tr>
                <td width="80" class="icon_user"></td>
                <td><?= $form->field($model, 'username', ['template'=>'{input}'])->textInput(['class'=>'field_adapt_90', 'placeholder'=>'用户名']) ?></td>
            </tr>
            </tbody></table>
        <?= $form->field($model, 'password', ['template'=>'<table cellpadding="0" cellspacing="0" class="borderBt"><tbody><tr><td width="80" class="user_lock"></td><td>{input}</td></tr></tbody></table>{error}'])->passwordInput(['class'=>'field_adapt_90', 'placeholder'=>'密码']) ?>
<!--        <table cellpadding="0" cellspacing="0" class="borderBt">-->
<!--            <tbody><tr>-->
<!--                <td width="80" class="user_lock"></td>-->
<!--                <td></td>-->
<!--            </tr>-->
<!--            </tbody></table>-->
        <!--table cellpadding="0" cellspacing="0">
            <tbody><tr>
                <td width="80" class="user_pwd bt"></td>
                <td class="yzm_text"><input type="password" placeholder="验证码" class="field_adapt_90"></td>
                <td align="right" width="230"><button class="btn_fixed_186px">WWLF</button></td>
            </tr>
            </tbody></table-->
        <table class="recept_role">
            <tbody><tr>
                <td style=" padding-bottom:20px;"><?= Html::submitButton('绑定', ['class' => 'btn_adapt_100 btn btn-primary', 'name' => 'login-button']) ?></td>
            </tr>
            <!--
            <tr>
                <td><button class="btn_adapt_100_grey" onclick="location.href=''">忘记密码</button></td>
            </tr> -->
            </tbody></table>
        <?php ActiveForm::end(); ?>
        <?php if (Yii::$app->user->isGuest): ?>
        <div style="color: red; font-size: 2em; padding: 10px; float: right"><a href="<?= Yii::$app->urlManager->createAbsoluteUrl('/site/signup?openid='.$openid); ?>">没有旺财谷账户？</a> </div>
        <?php endif; ?>
    </div>
</div>