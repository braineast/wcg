<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/1/2014
 * Time: 7:36 PM
 */
if (!$list || !is_array($list)) $list = [];
if (!$summary || !is_array($summary)) $summary = [];
?>
<div class="main_content">
    <div class="in_list">
        <?php if ($list && is_array($list)): ?>
        <?php foreach($list as $deals): ?>
        <?php if ($deals && is_array($deals)): ?>
            <?php foreach($deals as $deal): ?>
                <table class="list_table list_tableNew" cellpadding="0" cellspacing="0" width="100%">
                    <tbody><tr>
                        <td align="" colspan="4"><span class="t_50"><?= $deal['info']['title'] ?></span></td>
                    </tr>
                    <tr>
                        <td width="24%"><?= number_format($deal['invest_amt'], 2) ?>元 </td>
                        <td width="24%"><?= intval($deal['info']['rate']) ?>%</td>
                        <td width="24%"><?= $deal['info']['period'] ?></td>
                        <td width="28%" class="td1" align="right">总收益：<span class="p_color"><?= $deal['interest_amt'] ?></span>元</td>
                    </tr>
                    </tbody>
                </table>
            <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<div class="bottom_area">
    <table cellspacing="0" width="100%" class="bottom_areaInfo">
        <tbody><tr>
            <td width="33%" align="center">理财金额
                <p><?= $summary && isset($summary['investAmt']) ? number_format($summary['investAmt'], 2) : 0.00 ?>元</p></td>
            <td width="34%" align="center">获得收益
                <p><?= $summary && isset($summary['returnedInterestAmt']) ? number_format($summary['returnedInterestAmt'], 2) : 0.00 ?>元</p></td>
            <td width="33%" align="center">可用余额
                <p><?= $summary && isset($summary['interestAmt']) ? number_format($summary['interestAmt'], 2) : 0.00 ?>元</p></td>
        </tr>
        <tr>
            <td align="ceter" colspan="3" class="btnBlue" style=" padding:0"><p><a href="<?= Yii::$app->urlManager->createAbsoluteUrl('/site/products') ?>"><img src="/css/wcg/images/icon_money.png"><span style="color: #ffffff;">去理财</span></a></p></td>
        </tr>
        </tbody></table>
</div>