<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

return [
    // Posts
    [
        'label' => 'Posts',
        'iconClass' => 'bi bi-list-columns me-1',
        'url' => ['/BlogNew/backend/post/index'],
        'active' => static function () {
            return str_contains(Yii::$app->request->url, '/BlogNew/backend/post');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Blog New',
                    'groupIcon' => 'bi bi-book',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Categories
    [
        'label' => 'Categories',
        'iconClass' => 'bi bi-diagram-3 me-1',
        'url' => ['/BlogNew/backend/category/index'],
        'active' => static function () {
            return (bool)preg_match('#/BlogNew/backend/category/(index|create|update|view)#', Yii::$app->request->url);
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Blog New',
                    'groupIcon' => 'bi bi-book',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],
];
