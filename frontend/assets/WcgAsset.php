<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/28/2014
 * Time: 10:45 AM
 */

namespace frontend\assets;
use yii\web\AssetBundle;

class WcgAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/wcg/common.css',
        'css/wcg/layout.css',
        'css/wcg/process.css',
    ];
    public $js = [
//        'javascript/open.js',
//        'javascript/account/deposit.js',
    ];
    public $depends = [
//        'yii\web\YiiAsset',
//        'yii\bootstrap\BootstrapAsset',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];
}
