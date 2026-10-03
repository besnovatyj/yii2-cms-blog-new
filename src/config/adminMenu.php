<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Contracts\adminMenu\AdminMenuLocation;
use Besnovatyj\Contracts\adminMenu\AdminMenuPlacement;

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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Blog New',
                    groupIcon: 'bi bi-book',
                    groupPriority: 100,
                    priority: 100,
                ),
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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Blog New',
                    groupIcon: 'bi bi-book',
                    groupPriority: 100,
                    priority: 100,
                ),
            ],
        ],
    ],
];
