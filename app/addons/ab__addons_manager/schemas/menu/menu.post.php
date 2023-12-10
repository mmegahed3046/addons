<?php


if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
$schema['central']['ab__addons']['position'] = 10000;
$schema['central']['ab__addons']['items']['ab__addons_manager'] = [
    'attrs' => ['class' => 'is-addon'],
    'href' => 'ab__am.addons',
    'position' => 1,
    'subitems' => [
        'ab__am.addons' => [
            'attrs' => [
                'class' => 'is-addon',
                'href' => [
                    'class' => 'ab__am',
                ],
            ],
            'href' => 'ab__am.addons',
            'position' => 10,
        ],
        'ab__am.our_store' => [
            'attrs' => [
                'class' => 'is-addon',
                'href' => [
                    'target' => '_blank',
                    'rel' => 'nofollow noopener',
                ],
            ],
            'href' => 'https://cs-cart.alexbranding.com/?utm_medium=ab__am&utm_source=menu&utm_campaign=menu',
            'position' => 20,
        ],
    ],
];
return $schema;
