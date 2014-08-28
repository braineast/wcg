<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/1/2014
 * Time: 11:00 AM
 */
use \yii\helpers\Html;
?>
<div class="main_content" style="">
    <div class="financing" style=" background:#f8f8f8">
        <table class="financingTitle" cellpadding="0" cellspacing="0" width="100%">
            <tbody><tr>
                <th align="left" width="33%">理财列表</th>
            </tr>
            <tr align="center">
                <td width="33%"><span>10%-13%</span></td>
            </tr>
            </tbody></table>
    </div>
    <div class="line"></div>
    <?php foreach($list as $deal): ?>
    <div class="financingList" style="padding-bottom:8px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody><tr align="center">
                <td align="left" colspan="2"><span><?= Html::a($deal['title'], Yii::$app->urlManager->createAbsoluteUrl('/site/product?id='.$deal['deal_id'])) ?></span><?php if ($deal['baoxian'] == 2): ?><img class="icon" src="/css/wcg/images/home7_03.png"><?php endif; ?></td>
                <td width="17%" rowspan="3" class="deal_status_text">
                    <?php if ($deal['deal_status'] == 1): ?>
                        <div class="span" style="color: red; font-weight: bold;">即将开始</div>
                        <div class="fk001" seconds="<?= $deal['start_date'] - time() ?>" deal_id="<?= $deal['deal_id'] ?>"><?= $deal['interval']->d.':'.$deal['interval']->h.':'.$deal['interval']->i.':'.$deal['interval']->s ?></div>
                    <?php elseif($deal['deal_status'] == 2):?>
                        <div class="progress-radial progress-<?= (1 - $deal['balance'] / $deal['money'])*100 ?>"><?= Html::a('立即投资', Yii::$app->urlManager->createAbsoluteUrl('/site/product?id='.$deal['deal_id'])) ?></div>
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
<script type="text/javascript">
    jQuery(document).ready(
        function()
        {
            jQuery('.fk001').each(
                function()
                {
                    var timerId = null;
                    var period = $(this).attr('seconds');
                    if (period)
                    {
                        var contentDiv = $(this);
                        timerId = window.setInterval(function(){
                            period--;
                            showTime(period,contentDiv);
                            if (timerId && period == 0) {
                                clearInterval(timerId);
                                contentDiv.parent().html('<div><a href="<?= Yii::$app->request->hostInfo ?>/site/product?id='+contentDiv.attr('deal_id')+'">立即投资</a></div>');
                            }
                        }, 1000);
                    }
                }
            );
        }
    );

    function showTime(intervalSeconds, contentContainer)
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
        return contentContainer.text((days ? days+'天': '')+hours+'时'+minutes+'分'+seconds+'秒后开放');
    }
</script>