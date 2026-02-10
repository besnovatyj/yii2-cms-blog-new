<?php

namespace Besnovatyj\BlogNew;

use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * Бутстрапинг отдельным классом, для того чтобы не создавать экземпляр модуля?
 */
class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
            // остальной код
        });
    }
}
