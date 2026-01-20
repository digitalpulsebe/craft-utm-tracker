<?php
/**
 * UTM Tracker plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\utmtracker;

use craft\base\Model;
use digitalpulsebe\utmtracker\services\UtmTrackerService as UtmTrackerServiceService;
use digitalpulsebe\utmtracker\storage\StorageMethod;
use digitalpulsebe\utmtracker\variables\UtmTrackerVariable;
use digitalpulsebe\utmtracker\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use digitalpulsebe\utmtracker\formie\TrackedField;
use verbb\formie\events\RegisterFieldsEvent;
use verbb\formie\services\Fields;
use yii\base\Event;

/**
 *
 * @author    Digital Pulse
 * @package   UtmTracker
 * @since     1.0.0
 *
 * @property  UtmTrackerServiceService $utmTrackerService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class UtmTracker extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * UtmTracker::$plugin
     *
     * @var UtmTracker
     */
    public static $plugin;

    public ?StorageMethod $storage = null;

    // Public Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('utmTracker', UtmTrackerVariable::class);
            }
        );

        // hook on site requests
        $this->storage = $this->utmTrackerService->processRequest(Craft::$app->request);

        // register custom field for Formie
        if (class_exists(Fields::class)) {
            Event::on(Fields::class, Fields::EVENT_REGISTER_FIELDS, function(RegisterFieldsEvent $event) {
                $event->fields[] = TrackedField::class;
            });
        }

    }


    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string|null The rendered settings HTML
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'utm-tracker/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
