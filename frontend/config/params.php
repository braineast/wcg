<?php
return [
    'adminEmail' => 'admin@example.com',
    'wechat'=>[
        'token'=>'f1582cb3ffa64bd4bdfca73d8795',
        'appid'=>'wxd0360635820df2bf',
        'appsecret'=>'f9ef7b02618bd259035e42cc9be6f9b6',
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
            'dev'=>[
                'host'=>'http://test.chinapnr.com/muser/publicRequests',
                'merid'=>'530091',
                'mercustid'=>'6000060000141804',
                'sign'=>[
                    'host'=>'115.28.152.140',
                    'port'=>'8866'
                ],
            ],
        ],
        'wcg' => [
            'baseUrl' => 'http://api.yidaifa.com/weixin',
        ],
    ],
];
