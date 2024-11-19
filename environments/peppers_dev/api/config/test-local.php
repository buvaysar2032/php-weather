<?php

return [
    'components' => [
        'request' => [
            'csrfCookie' => ['secure' => false]
        ],
        'user' => [
            'identityCookie' => ['secure' => false]
        ],
        'session' => [
            'cookieParams' => ['secure' => false]
        ]
    ]
];
