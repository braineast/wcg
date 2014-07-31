<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/1/2014
 * Time: 12:51 AM
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>
<div class="main_content">
    <div class="in_list">
        <table class="list_table" cellpadding="0" cellspacing="0" width="100%" style=" padding-bottom:30px; padding-top:30px;">
            <tbody><tr>
                <td width="13%" height="60" style=""><span class="t_30">充值提示：</span></td>
            </tr>
            <tr>
                <td width="13%" class="p_text"><p>1、所有充值金额将由第三方平台托管（存放）</p>
                    <p>2、推广期内充值手续费均由易代发平台垫付</p>
                    <p>3、请注意您的银行卡充值限额，以免造成不便</p>
                    <p>4、如果充值金额没有及时到账，请和客服联系</p></td>
            </tr>
            </tbody></table>
        <?php $form = ActiveForm::begin(['id'=>'account_deposit_form', 'enableClientValidation'=>true, 'enableAjaxValidation'=>true]); ?>
        <?= $form->field($model, 'amount', ['template'=>'<table class="list_table" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td height="60" align="" colspan="3"><span class="t_30">{label}：</span></td></tr><tr><td align="left">{input}</td><td align="right">元</td></tr></tbody></table><div style="text-align:center;color: red;margin-top: 20px;font-size: 2em">{error}</div>'])->textInput(['class'=>'inputMoney', 'style'=>'height:60px; width:100%; border:0;', 'title'=>'请输入充值金额']); ?>
        <!--table class="list_table" cellpadding="0" cellspacing="0" width="100%">
            <tbody><tr>
                <td height="60" align="" colspan="3"><span class="t_30">充值金额：</span></td>
            </tr>
            <tr>
                <td align="left"><input class="inputMoney" value="请输入充值金额" type="text" style=" height:60px; width:100%; border:0;"></td>
                <td align="right">元</td>
            </tr>
            </tbody></table>
        <div style="text-align:center;color: red;margin-top: 20px;font-size: 2em">{error}</div-->
        <table class="recept_role" width="100%" style=" margin-top:48px">
            <tbody><tr>
                <td colspan="2"><?= Html::submitButton('立即充值', ['class' => 'btn btn-primary btn_adapt_100', 'name' => 'deposit-button']) ?></td>
            </tr>
            </tbody></table>
        <?php ActiveForm::end(); ?>
    </div>
</div>