<?php

namespace digitalpulsebe\utmtracker\formie;

use craft\helpers\Html;
use digitalpulsebe\utmtracker\UtmTracker;
use verbb\formie\fields\formfields\Hidden;
use verbb\formie\helpers\SchemaHelper;
use verbb\formie\positions\Hidden as HiddenPosition;

class TrackedField extends Hidden
{
    public function init()
    {
        parent::init();
    }

    public static function displayName(): string
    {
        return 'Tracked Field';
    }

    /**
     * @inheritDoc
     */
    public static function getSvgIconPath(): string
    {
        return 'utm-tracker/_formie/icon.svg';
    }

    /**
     * @inheritDoc
     */
    public function defineGeneralSchema(): array
    {
        return [
            SchemaHelper::labelField([
                'label' => 'Name',
                'help' => 'The name of this field displayed only to you',
            ]),
            SchemaHelper::selectField([
                'label' => 'Field',
                'help' => 'Select the tracked field',
                'name' => 'defaultOption',
                'options' => [
                    [ 'label' => 'Query parameter (tag)', 'value' => 'tag' ],
                    [ 'label' => 'Landing Page URL', 'value' => 'absoluteLandingUrl' ],
                    [ 'label' => 'Landing Page URL (without Query String)', 'value' => 'landingUrl' ],
                    [ 'label' => 'Referrer URL', 'value' => 'referrerUrl' ],
                    [ 'label' => 'All data as json string', 'value' => 'all' ],
                ],
            ]),
            SchemaHelper::toggleContainer('settings.defaultOption=tag', [
                SchemaHelper::selectField([
                    'label' => 'Query Parameter',
                    'help' => 'Select the name (key) of the tracked query parameter. (Add more in the UTM Tracker plugin settings)',
                    'name' => 'queryParameter',
                    'options' => array_map(function ($tag) {
                        return [
                            'label' => $tag,
                            'value' => $tag,
                        ];
                    }, UtmTracker::$plugin->settings->getTrackableTagsArray())
                ]),
            ]),
            SchemaHelper::variableTextField([
                'label' => 'Default Value',
                'help' => 'Entering a default value will place the value in the field when the tracked field value is empty.',
                'name' => 'defaultValue',
            ]),
        ];
    }

    public function getFieldValue($element, $handle = '', $attributePrefix = '')
    {
        $value = null;

        if (!\Craft::$app->getRequest()->getIsConsoleRequest()) {
            $storage = UtmTracker::$plugin->storage;

            if ($storage) {
                $parameters = $storage->getParameters();

                if ($this->defaultOption === 'absoluteLandingUrl') {
                    $value = $parameters->absoluteLandingUrl;
                } elseif ($this->defaultOption === 'landingUrl') {
                    $value = $parameters->landingUrl;
                } elseif ($this->defaultOption === 'referrerUrl') {
                    $value = $parameters->referrerUrl;
                } elseif ($this->defaultOption === 'tag') {
                    $value = $parameters->getQueryParameter($this->queryParameter);
                } elseif ($this->defaultOption === 'all') {
                    $value = json_encode($parameters->toArray());
                }
            }
        }

        if ($value === null) {
            $value = $this->getDefaultValue();
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getPreviewInputHtml(): string
    {
        return Html::tag('input', '', [
            'type' => 'text',
            'disabled' => true,
            'class' => 'fui-field-input',
            'placeholder' => '- user tracked data -',
        ]);
    }

    public static function getFrontEndInputTemplatePath(): string
    {
        return 'fields/hidden';
    }

    /**
     * @inheritDoc
     */
    public function getFieldDefaults(): array
    {
        return [
            'labelPosition' => HiddenPosition::class,
            'defaultOption' => 'landingUrl',
            'includeInEmail' => false,
        ];
    }
}