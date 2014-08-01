<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/1/2014
 * Time: 11:00 AM
 */
?>
<div class="main_content" style="">
    <div class="financing" style=" background:#f8f8f8">
        <table class="financingTitle" cellpadding="0" cellspacing="0" width="100%">
            <tbody><tr>
                <th align="left" width="33%">理财列表</th>
            </tr>
            <tr align="center">
                <td width="33%"><span>12%-16%</span></td>
            </tr>
            </tbody></table>
    </div>
    <div class="line"></div>
    <?php foreach($list as $deal): ?>
    <div class="financingList" style="padding-bottom:8px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody><tr align="center">
                <td align="left" colspan="2"><span><?= $deal['title'] ?></span><?php if ($deal['baoxian'] == 2): ?><img class="icon" src="/css/wcg/images/home7_03.png"><?php endif; ?></td>
                <td width="17%" rowspan="3">
                    <?php if ($deal['deal_status'] == 2): ?>
                        <div class="progress-radial progress-<?= (1 - $deal['balance'] / $deal['money'])*100 ?>"><b></b></div>
                        <?php elseif($deal['deal_status'] == 5):?>
                        <div class="zt zt_1"><span class="span">还款中</span></div>
                    <?php elseif($deal['deal_status'] == 6):?>
                        <div class="zt"><span class="span">已完成</span></div>
                    <?php endif;?>
                </td>
            </tr>
            <tr>
                <td width="41%">年利率：<?= intval($deal['syl']) ?>%</td>
                <td width="42%"><?= $deal['money'] ?>元</td>
            </tr>
            <tr>
                <td colspan="2">期限：<?= $deal['qixian'] ?></td>
            </tr>
            </tbody></table>
    </div>
    <?php endforeach; ?>

    <!--table cellspacing="0" width="100%">
        <tbody><tr>
            <td align="center" class="" style=" padding:26px;font-size:30px; color:#000;">点击加载更多</td>
        </tr>
        </tbody></table-->
</div>