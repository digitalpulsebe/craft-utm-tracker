<?php

namespace digitalpulsebe\utmtracker\migrations;

use Craft;
use craft\db\Migration;

/**
 * m260724_125652_set_default_setting_async_false migration.
 */
class m260724_125652_set_default_setting_async_false extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // set project config setting 'async' to false for existing installations
        Craft::$app->getProjectConfig()->set('plugins.utm-tracker.settings.async', false);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        return true;
    }
}
