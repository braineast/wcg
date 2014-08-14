<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/6/2014
 * Time: 5:40 PM
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;

//$details = $details ? ($details['result']==0 && $details['errors']['code'] == 0 ? $details['data'] : null) : null;
$deal = $deal ? $deal : null;
$refunds = isset($refunds) && $refunds ? $refunds : [];
$refundsBalance = 0.00;
$dealOrders = isset($dealOrders) && $dealOrders ? $dealOrders : [];
?>

<style>
    h4 {
        text-align: left;
    }
    .help-block {
        color: red;
    }
</style>
<div class="main_content" style="padding-bottom:88px;">
<div class="financingList">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style=" border-bottom:0">
        <tbody><tr>
            <td colspan="2"><span><?= $deal['title'] ?></span><?php if ($deal['baoxian'] == 2): ?><img class="icon" src="/css/wcg/images/home7_03.png"><?php endif; ?></td>
            <td></td>
        </tr>
        <tr>
            <td width="66%">年利率：<?= preg_replace('/\.00$/', '', $deal['syl']) ?>%</td>
            <td width="34%" align="center"><?= $deal['money'] ?>元</td>
        </tr>
        <tr>
            <td colspan="">期限：<?= $deal['period'] ?></td>
            <td width="34%" rowspan="3" align="center">
                    <?php if ($deal['deal_status'] == 1): ?>
<!--                        <span>标的处于预告期</span>-->
<!--                        <div style=" height:44px; width:240px; border:3px solid #ccc; background:none">-->
<!--                            <span style=" display:block; height:37px; margin:3px; line-height:100px; font-size:12px; color:#777; width:234px; background:#ff6630;padding:0">100%</span>-->
<!--                        </div>-->
                    <?php endif; ?>
                    <?php if ($deal['deal_status'] == 2): ?>
                        <?php $perc = round((($deal['money'] - $deal['balance']) / $deal['money'])*100);if ($perc == 100 && $deal['balance'] > 0) {$perc = 99;} ?>
                        <div id="deal_percent" style=" height:44px; width:240px; border:3px solid #ccc; background:none">
                            <span style=" display:block; height:37px; margin:3px; line-height:100px; font-size:12px; color:#777; width:<?= $perc*234/100 ?>px; background:#ff6630;padding:0"><?= $perc ?>%</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($deal['deal_status'] == 3): ?>
                        <span>已满标</span>
                    <?php endif; ?>
                    <?php if ($deal['deal_status'] == 4): ?>
                        <span>已流标</span>
                    <?php endif; ?>
                    <?php if ($deal['deal_status'] == 5): ?>
                        <span>还款中</span>
                    <?php endif; ?>
                    <?php if ($deal['deal_status'] == 6): ?>
                        <span>已完成</span>
                    <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td colspan="">还款方式：<?= $deal['loan_type'] ?></td>
        </tr>
        <?php if ($deal['deal_status'] == 1): ?>
            <tr><td colspan="" id="left_time_text">尚有：<?= $deal['interval']->d ?>天<?= $deal['interval']->h ?>时<?= $deal['interval']->i ?>分<?= $deal['interval']->s ?>秒开放</td></tr>
        <?php endif; ?>
        <?php if ($deal['deal_status'] == 2): ?>
            <tr><td colspan="" id="left_time_text">剩余时间：<?= $deal['interval']->d ?>天<?= $deal['interval']->h ?>时<?= $deal['interval']->i ?>分<?= $deal['interval']->s ?>秒</td></tr>
        <?php endif; ?>
        </tbody></table>
    <?php if ($deal['deal_status'] < 3): ?>
        <?php $form = ActiveForm::begin(['id'=>'tender_form','enableClientValidation'=>true, 'enableAjaxValidation'=>true]); ?>
        <?= $form->field($model, 'dealId', ['template'=>'{input}'])->hiddenInput(['value'=>$deal['deal_id']]) ?>
    <table class="list_table" cellpadding="0" cellspacing="0" width="100%" style="padding-bottom:0; padding-top:0;border-bottom:0<?php if ($deal['deal_status'] == 1) echo(';display: block;'); ?>">
        <tbody>
        <tr>
            <td height="60" align="" colspan="3"><p id="deal_balance" class="t_30"><?php if ($deal['balance'] > 0): ?>剩余<?= $deal['balance'] ?>元可投<?php endif; ?></p></td>
        </tr>
        <tr>
            <td align="left">
                <?= $form->field($model, 'amount', ['template'=>'{input}{error}'])->textInput(['class'=>'inputMoney', 'style'=>'height:60px; width:100%; border:0;', 'placeholder'=>'请输入投资金额']); ?>
            </td>
            <td align="right">元</td>
        </tr>
        <tr>
            <td align="right" colspan="3" style=" border-top: 1px solid #cccccc">
                <span style="padding-bottom:0;padding-top:2px; float:right;color:#777; font-size:24px;">
                    我的余额：<?= $user['avl_balance'] ?>元 |
                    <?php if (!$user['cnpnr_account']): ?><?= Html::a('去开户', '/site/cnpnr', ['style'=>'color: red;']); ?>
                    <?php else: ?><?= Html::a('去充值', '/account/deposit', ['style'=>'color: red;']); ?>
                    <?php endif; ?>
                </span>
            </td>
        </tr>
        </tbody>
    </table>

    <table class="recept_role" width="100%" style=" border-bottom:0; padding-top:16px;">
        <tbody><tr>
            <td colspan="2"><?= Html::submitButton('立即投资', ['class'=>'btn_adapt_100']) ?></td>
        </tr>
        </tbody></table>
    <?php ActiveForm::end(); ?>
    <?php endif; ?>
</div>
<div class="list">
<table width="100%" border="0" class="tableOut" cellpadding="0" cellspacing="0">
    <tbody><tr onclick="hide1();" id="topBtn1" style="display:none;">
        <th width="50%" align="left">项目信息</th>
        <th width="50%" align="right"><img src="/css/wcg/images/close.png"></th></tr>
    <tr onclick="show1();" id="bottomBtn1">
        <th width="50%" align="left">项目信息</th>
        <th align="right"><img src="/css/wcg/images/open.png"></th>
    </tr>
    <tr>
        <td height="" colspan="2" style=" padding:0 30px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableIn" id="greenTable1" style="display:">
                <tbody><tr>
                    <td colspan="4">借款用途：<?= $deal['zjyt'] ?></td>
                </tr>
                <tr>
                    <td colspan="4">详情介绍：<?= $deal['projdes'] ?></td>
                </tr>
                <tr>
                    <td colspan="4"><span style="font-weight: bold">还款来源：</span><br><?= $deal['hkly'] ?></td>
                </tr>
                </tbody></table></td>
    </tr>
    </tbody></table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableOut">
    <tbody><tr onclick="hide2();" id="topBtn2" style="display:none;">
        <th width="50%" align="left">企业信息</th>
        <th width="50%" align="right"><img src="/css/wcg/images/close.png"></th></tr>
    <tr onclick="show2();" id="bottomBtn2">
        <th width="50%" align="left">企业信息</th>
        <th align="right"><img src="/css/wcg/images/open.png"></th>
    </tr>
    <tr><td height="" colspan="2" style=" padding:0 30px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableIn" id="greenTable2" style="display:">
                <tbody><tr>
                    <th colspan="4" style="font-size: 20px"><?= $deal['company'] ?></th>
                </tr>
                </tbody></table></td>
    </tr>
    </tbody></table>
<table width="100%" border="0" class="tableOut" cellpadding="0" cellspacing="0">
    <tbody><tr onclick="hide3();" id="topBtn3" style="display:none;">
        <th width="50%" align="left">安全保障</th>
        <th width="50%" align="right"><img src="/css/wcg/images/close.png"></th></tr>
    <tr onclick="show3();" id="bottomBtn3">
        <th width="50%" align="left">安全保障</th>
        <th align="right"><img src="/css/wcg/images/open.png"></th>
    </tr>
    <tr>
        <td height="" colspan="2" style=" padding:0 30px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableIn" id="greenTable3" style="display:">
                <tbody>
                <?php if (isset($deal['security']) && $deal['security']): ?>
                <tr>
                    <td colspan="4">安全投资，多重保障</td>
                </tr>
                <?php foreach($deal['security'] as $securityText): ?>
                    <?php if ($securityText): ?>
                        <tr>
                            <td colspan="4"><?= $securityText ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="4">无安全保障方面的进一步说明。</td></tr>
                <?php endif; ?>
                </tbody></table></td>
    </tr>
    </tbody></table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableOut">
    <tbody><tr onclick="hide4();" id="topBtn4" style="display:none;">
        <th width="50%" align="left">还款计划</th>
        <th width="50%" align="right"><img src="/css/wcg/images/close.png"></th></tr>
    <tr onclick="show4();" id="bottomBtn4">
        <th width="50%" align="left">还款计划</th>
        <th align="right"><img src="/css/wcg/images/open.png"></th>
    </tr>
    <tr>
        <td height="" colspan="2" style=" padding:0 30px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableIn" id="greenTable4" style="display:">
                <tbody>
                <?php if ($refunds): ?>
                    <tr>
                        <th align="left">预期还款时间</th>
                        <th align="">类型</th>
                        <th align="right">还款金额</th>
                    </tr>
                    <?php foreach($refunds as $plan): ?>
                        <?php $refundsBalance += $plan['benxi']; ?>
                        <tr>
                            <td align="left"><?= date('Y.m.d', $plan['refund_time']) ?></td>
                            <td align="center"><?php if($plan['benjin'] && $plan['lixi']) {echo('本息');} elseif($plan['lixi']) {echo('利息');}elseif($plan['benjin']) {echo('本金');} ?></td>
                            <td align="right"><?= number_format($plan['benxi'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td align="right" colspan="3" class="td5" style="padding-top:6px; padding-bottom:9px;">总计：￥<?= number_format($refundsBalance, 2) ?>元</td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="3">尚无相关记录。</td></tr>
                <?php endif; ?>
                </tbody></table></td>
    </tr>
    </tbody></table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableOut">
    <tbody><tr onclick="hide5();" id="topBtn5" style="display:none;">
        <th width="50%" align="left">投标记录</th>
        <th width="50%" align="right"><img src="/css/wcg/images/close.png"></th></tr>
    <tr onclick="show5();" id="bottomBtn5">
        <th width="50%" align="left">投标记录</th>
        <th align="right"><img src="/css/wcg/images/open.png"></th>
    </tr>
    <tr>
        <td height="" colspan="2" style=" padding:0 30px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableIn" id="greenTable5" style="display:">
                <tbody>
                <?php if ($dealOrders): ?>
                    <tr>
                        <th width="50%" align="left">投标人</th>
                        <th width="50%" align="right">投标金额</th>
                    </tr>
                    <?php foreach($dealOrders as $order): ?>
                        <tr>
                            <td align="left"><?= mb_substr($order['username'], 0, 1, 'UTF-8').'***'.mb_substr($order['username'], -1, 1, 'UTF-8') ?></td>
                            <td align="right"><?= number_format($order['order_money'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="2">尚无相关记录</td></tr>
                <?php endif; ?>
                </tbody></table></td>
    </tr>
    </tbody></table>
</div>
</div>
<!--<div class="bottom_area">-->
<!--    <table cellspacing="0" width="100%" class="bottom_areaInfoOne">-->
<!--        <tbody><tr>-->
<!--            <td align="center" class="btnBlue" s=""><p>立即投资</p></td>-->
<!--        </tr>-->
<!--        </tbody></table>-->
<!--</div>-->
<script type="text/javascript">
    jQuery(document).ready(
        function()
        {
            getDealBrief();
            var timerElement = $('#left_time_text');
            if (timerElement)
            {
                var timerId = null;
                var period = 0;
                period = <?= $deal['deal_status'] == 2 ? $deal['end_date']-$deal['start_date'] : ($deal['deal_status'] == 1 ? $deal['start_date'] - time() : 0); ?>;
                if (period)
                {
                    timerId = window.setInterval(function(){
                        period--;
                        showTime(period);
                        if (timerId && period == 0) clearInterval(timerId);
                    }, 1000);
                }
            }
        }
    );

    function showTime(intervalSeconds)
    {
        var minuteLength = 60;
        var hourLength = minuteLength * 60;
        var dayLength = hourLength * 24;
        var days = 0;
        var hours = 0;
        var minutes = 0;
        var seconds = 0;
        if (intervalSeconds > 0)
        {
            days = parseInt(intervalSeconds / dayLength);
            hours = parseInt((intervalSeconds - dayLength * days) / hourLength);
            minutes = parseInt((intervalSeconds - dayLength * days - hourLength * hours) / minuteLength);
            seconds = intervalSeconds - dayLength * days - hourLength * hours - minuteLength * minutes;
            hours = hours < 10 ? '0'+hours : hours;
            minutes = minutes < 10 ? '0'+minutes : minutes;
            seconds = seconds < 10 ? '0'+seconds : seconds;
        }
        var message = days+'天'+hours+'时'+minutes+'分'+seconds+'秒';
        <?php if ($deal['deal_status'] == 1): ?>
        message = '尚有：'+message+'开放！';
        <?php endif; ?>
        <?php if ($deal['deal_status'] == 2): ?>
        message = '剩余时间：'+message;
        <?php endif; ?>
        return $('#left_time_text').text(message);
    }

    function getDealBrief()
    {
        var timerId = window.setInterval(function(){
            $.ajax(
                {
                    url: '<?= Yii::$app->request->hostInfo ?>/site/getdealbrief?dealId=20',
                    success: function(data) {
                        if (data.deal_status > 2 || data.balance == 0) clearInterval(timerId);
                        $('#deal_balance').text('剩余'+data.balance+'元可投');
                        var percent = 0;
                        percent = Math.round((data.money - data.balance) / data.money * 100);
                        if (percent == 100 && data.balance > 0) percent = 100;
                        if (data.balance == 0) percent = 100;
                        $('#deal_percent').html('<span style=" display:block; height:37px; margin:3px; line-height:100px; font-size:12px; color:#777; width:'+ parseInt(percent * 234 / 100) +'px; background:#ff6630;padding:0">'+percent+'%</span>');
                    }
                }
            );
        }, 500);
    }
</script>