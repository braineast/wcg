<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/1/2014
 * Time: 1:09 PM
 */
?>
<div class="jumbotron">
    <h1>恭喜您！</h1>

    <p class="lead"><?= $message ?></p>

    <p><a class="btn btn-success" href="<?= Yii::$app->urlManager->createAbsoluteUrl('/account/deposit') ?>" style="margin-left: 15px">为账户充值</a><a style="margin-left: 15px" class="btn btn-success" href="<?= Yii::$app->urlManager->createAbsoluteUrl('/site/products') ?>">我要理财</a><a style="margin-left: 15px" class="btn btn-success" href="<?= Yii::$app->urlManager->createAbsoluteUrl('/account/transactions') ?>">交易记录</a></p>
</div>