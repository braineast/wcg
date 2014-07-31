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
    </body>
    </html>
<?php $this->endPage() ?>