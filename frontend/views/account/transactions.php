<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/30/2014
 * Time: 8:12 AM
 */
?>
<div class="main_content" style=" padding-bottom:250px;">
    <table class="listInforTitle" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr align="center">
            <td width="33%">时间 </td>
            <td width="34%">类型明细</td>
            <td width="33%" style="border-right:0;">交易金额</td>
        </tr>
        </tbody></table>
    <?php foreach($transactions as $yearMonth=>$logs): ?>
        <table class="listInfoOne" cellpadding="0" cellspacing="0" width="100%">
            <tbody><tr onclick="hide1();" id="topBtn1" style="display:none;">
                <td align="left"><?= $yearMonth ?></td>
                <td width="40"><img src="/css/wcg/images/close.png"></td>
            </tr>
            <tr onclick="show1();" id="bottomBtn1">
                <td align="left"><?= $yearMonth ?></td>
                <td width="40"><img src="/css/wcg/images/open.png"></td>
            </tr>
            </tbody></table>
        <div class="in_list" style=" padding-top:0; border-bottom:0;" id="greenTable1">
            <table class="list_table list_tableOne" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr height="60">
                        <td class="td" width="13%"><?= date('d', $log['create_time']) ?>日 </td>
                        <td class="td" width="13%" align="center"><?= $log['type'] ?></td>
                        <td class="td" width="13%" align="right"><?= $log['fund'] ?>元</td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table>
        </div>
    <?php endforeach; ?>
</div>
<div class="bottom_area">
    <table cellspacing="0" width="100%" class="bottom_areaInfoOne">
        <tbody>
        <tr>
            <td width="33%" align="center">账户金额:￥<?= number_format($summary['balance'], 2); ?>元</td>
            <td width="34%" align="center" style=" border-right:0">理财收益:￥<?= number_format($summary['returned_interest_balance'],2);?>元</td>
        </tr>
        <tr>
            <td width="33%" align="center">可用金额:￥<?= number_format($summary['avl_balance'], 2) ?>元</td>
            <td width="34%" align="center" style=" border-right:0">冻结金额:￥<?= number_format($summary['freeze_balance'], 2) ?>元</td>
        </tr>
        <tr>
            <td align="ceter" colspan="3" class="btnBlue"><p><a href="<?= Yii::$app->urlManager->createAbsoluteUrl('/site/products?openid='.$openid) ?>"><img src="/css/wcg/images/icon_money.png"><span style="color: #ffffff;">去理财</span></a></p></td>
        </tr>
        </tbody></table>
</div>