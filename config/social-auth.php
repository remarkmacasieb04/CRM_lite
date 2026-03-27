<?php

return [
    'providers' => [
        'google' => [
            'label' => 'Google',
            'icon' => 'google',
            'scopes' => ['openid', 'profile', 'email'],
        ],
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'scopes' => ['email'],
        ],
        'github' => [
            'label' => 'GitHub',
            'icon' => 'github',
            'scopes' => ['user:email'],
        ],
    ],
];
