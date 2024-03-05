<?php

namespace digitalpulsebe\utmtracker\formie;

use craft\helpers\Html;
use digitalpulsebe\utmtracker\UtmTracker;
use verbb\formie\fields\formfields\Hidden;
use verbb\formie\helpers\SchemaHelper;

class TrackedField extends Hidden
{

    public function init(): void
    {
        parent::init();
        $this->visibility = 'disabled';

        if (!\Craft::$app->getRequest()->getIsConsoleRequest()) {
            $storage = UtmTracker::$plugin->storage;

            if ($storage) {
                $parameters = $storage->getParameters();

                if ($this->defaultOption === 'absoluteLandingUrl') {
                    $this->defaultValue = $parameters->absoluteLandingUrl;
                } elseif ($this->defaultOption === 'landingUrl') {
                    $this->defaultValue = $parameters->landingUrl;
                } elseif ($this->defaultOption === 'referrerUrl') {
                    $this->defaultValue = $parameters->referrerUrl;
                } elseif ($this->defaultOption === 'tag') {
                    $this->defaultValue = $parameters->getQueryParameter($this->queryParameter);
                } elseif ($this->defaultOption === 'all') {
                    $this->defaultValue = json_encode($parameters->toArray());
                }
            }
        }
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
            SchemaHelper::selectField([
                'label' => 'Query Parameter',
                'help' => 'Select the name (key) of the tracked query parameter. (Add more in the UTM Tracker plugin settings)',
                'name' => 'queryParameter',
                'options' => array_map(function ($tag) {
                    return [
                        'label' => $tag,
                        'value' => $tag,
                    ];
                }, UtmTracker::$plugin->settings->getTrackableTagsArray()),
                'if' => '$get(defaultOption).value == tag'
            ]),
            SchemaHelper::variableTextField([
                'label' => 'Default Value',
                'help' => 'Entering a default value will place the value in the field when the tracked field value is empty.',
                'name' => 'defaultValue',
            ]),
        ];
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
}