<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/28/2014
 * Time: 11:03 AM
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>
<div class="main_content">
    <div class="register">
        <?php $form = ActiveForm::begin(['id' => 'form-signup','enableClientValidation'=>true, 'enableAjaxValidation'=>true, 'fieldConfig'=>['template'=>'']]); ?>
        <?= $form->field($model, 'username', ['template'=>'<table cellpadding="0" cellspacing="0" class="borderBt" style=" margin-top:0"><tbody><tr><td width="80" class="icon_user"></td><td>{input}</td><td>{error}</td></tr></tbody></table>'])->textInput(['class'=>'field_adapt_90', 'placeholder'=>'用户名6-16位数字/字母']) ?>
        <?= $form->field($model, 'email', ['template'=>'<table cellpadding="0" cellspacing="0" class="borderBt"><tbody><tr><td width="80" class="user_mail"></td><td>{input}</td><td>{error}</td></tr></tbody></table>'])->textInput(['class'=>'field_adapt_90', 'placeholder'=>'请输入常用邮箱，可用于登录']) ?>
        <?= $form->field($model, 'mobile', ['template'=>'<table cellpadding="0" cellspacing="0" class="borderBt"><tbody><tr><td width="80" class="user_num"></td><td>{input}</td><td>{error}</td></tr></tbody></table>'])->textInput(['class'=>'field_adapt_90', 'placeholder'=>'手机号用于登录和密码重置']) ?>
        <?= $form->field($model, 'password', ['template'=>'<table cellpadding="0" cellspacing="0" class="borderBt"><tbody><tr><td width="80" class="user_lock"></td><td>{input}</td><td>{error}</td></tr></tbody></table>'])->passwordInput(['class'=>'field_adapt_90', 'placeholder'=>'密码请输入5-15位数字/字母/符号']) ?>
        <?= $form->field($model, 'repeatpassword', ['template'=>'<table cellpadding="0" cellspacing="0" class="borderBt"><tbody><tr><td width="80" class="user_lock"></td><td>{input}</td><td>{error}</td></tr></tbody></table>'])->passwordInput(['class'=>'field_adapt_90', 'placeholder'=>'请再输入一遍密码']) ?>
        <!--暂时关闭手机验证码
        <table cellpadding="0" cellspacing="0">
            <tbody><tr>
                <td width="80" class="user_pwd bt"></td>
                <td class="yzm_text"><input type="password" placeholder="请输入短信验证码" class="field_adapt_90"></td>
                <td align="right" width="230"><button class="btn_fixed_186px_red">免费获取</button></td>
            </tr>
            </tbody></table> -->
        <table class="recept_role">
            <tbody><!--暂关闭协议版块<tr>
                <td class="checkbox_deep"><span class="check_box checked_deep"></span></td>
                <td width="180" class="read_role"><span style=" color:#444444; font-size:28px;">已阅读并同意</span></td>
                <td><a href="#"><span class="p_red" style="font-size:28px;">《易代发网站使用协议》</span></a></td>
            </tr>
            <tr>
                <td></td>
                <td width="180"></td>
                <td><a href="#"><span class="p_red" style="font-size:28px;">《易代发用户服务协议》</span></a></td>
            </tr>
            <tr>
                <td colspan="3" height="30"></td></tr> -->
            <tr>
                <td colspan="3"><?= Html::submitButton('注册并绑定', ['class' => 'btn btn-primary btn_adapt_100', 'name' => 'signup-button']) ?></td>
            </tr>
            </tbody></table>
        <?php ActiveForm::end(); ?>
        <div style="color: red; font-size: 2em; padding: 10px; float: right"><a href="<?= Yii::$app->urlManager->createAbsoluteUrl('/site/bind?openid=91899'); ?>">已有旺财谷账户？</a> </div>
    </div>
</div>
