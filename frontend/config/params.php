<?php
return [
    'adminEmail' => 'admin@example.com',
    'wechat'=>[
        'token'=>'f1582cb3ffa64bd4bdfca73d8795',
        'appid'=>'wx806677715e0a9965',
        'appsecret'=>'223c114936d52b4c29dddb59084b4092',
    ],
    'api' => [
        'cnpnr'=>[
            'host'=>'https://lab.chinapnr.com/muser/publicRequests',
            'merid'=>'830068',
            'mercustid'=>'6000060001283917',
            'sign'=>[
                'host'=>'115.28.152.140',
                'port'=>'8888'
            ],
            'noticeUrl'=>'http://www.wangcaigu.com',
            'dev'=>[
                'host'=>'http://test.chinapnr.com/muser/publicRequests',
                'noticeUrl'=>'http://888.yidaifa.com',
                'merid'=>'530091',
                'mercustid'=>'6000060000141804',
                'sign'=>[
                    'host'=>'115.28.152.140',
                    'port'=>'8866'
                ],
            ],
        ],
        'wcg' => [
            'baseUrl' => 'http://api.wangcaigu.com/weixin',
//            'baseUrl' => 'http://api.yidaifa.com/weixin',
        ],
    ],
];
