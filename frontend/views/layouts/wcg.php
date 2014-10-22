<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/28/2014
 * Time: 10:41 AM
 */
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\WcgAsset;
use frontend\widgets\Alert;

/**
 * @var \yii\web\View $this
 * @var string $content
 */
WcgAsset::register($this);

$company = isset(Yii::$app->params['company']) ? Yii::$app->params['company'] : 'My Company';
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width,maximum-scale=0.5,target-densitydpi=300, user-scalable=no;"  />
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <?= $content ?>


    <?php $this->endBody() ?>
    <script type="text/javascript">
        var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
        document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F1cdd37fcb0912f2278a8a11381ac7321' type='text/javascript'%3E%3C/script%3E"));
    </script>
    </body>
    </html>
<?php $this->endPage() ?>