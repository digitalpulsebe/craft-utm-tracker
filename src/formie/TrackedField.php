<?php

namespace digitalpulsebe\utmtracker\formie;

use craft\helpers\Template;
use digitalpulsebe\utmtracker\UtmTracker;
use verbb\formie\elements\Submission;
use verbb\formie\fields\Hidden;
use verbb\formie\helpers\SchemaHelper;
use verbb\formie\models\Notification;

class TrackedField extends Hidden
{
    public const TRACKED_SOURCES = ['absoluteLandingUrl', 'landingUrl', 'referrerUrl', 'tag', 'all'];

    public function init(): void
    {
        parent::init();

        // Formie 4: a 'disabled' visibility makes the field "unavailable" (ConditionVisibility::disabled()),
        // so ClearHiddenValuesTask nulls its value right after it was resolved server-side.
        // Fields saved under Formie 3 still carry visibility 'disabled' in their settings, so reset it here.
        if ($this->visibility === 'disabled') {
            $this->visibility = null;
        }
    }

    /**
     * The value is always resolved server-side from the UTM storage, so the (disabled / not rendered)
     * input never has to be posted back by the browser.
     * Formie 4 checks this in Hidden::shouldResolveSource() when a submission is populated.
     */
    public function isAuthoritativeSource(): bool
    {
        return in_array($this->valueSource, self::TRACKED_SOURCES, true) || parent::isAuthoritativeSource();
    }

    /**
     * Formie 4 resolves hidden field values through getDefaultValue() (used for rendering as well as submission)
     */
    public function getDefaultValue(): mixed
    {
        if (in_array($this->valueSource, self::TRACKED_SOURCES, true)) {
            $value = $this->getTrackedValue();

            if ($value !== null && $value !== '') {
                return $value;
            }

            // fall back to the configured default value when nothing was tracked
            return parent::getDefaultValue();
        }

        return parent::getDefaultValue();
    }

    protected function getTrackedValue(): ?string
    {
        if (\Craft::$app->getRequest()->getIsConsoleRequest()) {
            return null;
        }

        $storage = UtmTracker::$plugin->storage;

        if (!$storage) {
            return null;
        }

        $parameters = $storage->getParameters();

        return match ($this->valueSource) {
            'absoluteLandingUrl' => $parameters->absoluteLandingUrl,
            'landingUrl' => $parameters->landingUrl,
            'referrerUrl' => $parameters->referrerUrl,
            'tag' => $this->queryParameter ? $parameters->getQueryParameter($this->queryParameter) : null,
            'all' => json_encode($parameters->toArray()),
            default => null,
        };
    }

    /**
     * Allow the custom value sources in the parent's `valueSource` range validation
     */
    protected function defineRules(): array
    {
        return [];

        $rules = array_filter(parent::defineRules(), function ($rule) {
            return !(is_array($rule) && ($rule[0] ?? null) === ['valueSource'] && ($rule[1] ?? null) === 'in');
        });

        $rules[] = [['valueSource'], 'in', 'range' => self::TRACKED_SOURCES];

        return array_values($rules);
    }

    public function getEmailHtml(Submission $submission, Notification $notification, mixed $value, array $renderOptions = []): string|null|bool
    {
        return Template::raw($value ?? '');
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
    public function defineFormBuilderGeneralSchema(): array
    {
        return [
            SchemaHelper::labelField([
                'label' => 'Name',
                'help' => 'The name of this field displayed only to you',
            ]),
            SchemaHelper::selectField([
                'label' => 'Field',
                'help' => 'Select the tracked field',
                'name' => 'valueSource',
                'options' => [
                    [ 'label' => 'Landing Page URL', 'value' => 'absoluteLandingUrl' ],
                    [ 'label' => 'Landing Page URL (without Query String)', 'value' => 'landingUrl' ],
                    [ 'label' => 'Referrer URL', 'value' => 'referrerUrl' ],
                    [ 'label' => 'Query parameter (tag)', 'value' => 'tag' ],
                    [ 'label' => 'All data as json string', 'value' => 'all' ],
                ],
            ]),
            SchemaHelper::selectField([
                'label' => 'Query Parameter',
                'help' => 'Select the name (key) of the tracked query parameter. (Add more in the UTM Tracker plugin settings)',
                'name' => 'queryParameter',
                'options' => [
                    ['label' => 'Select a query parameter', 'value' => ''],
                    ...array_map(function ($tag) {
                        return [
                            'label' => $tag,
                            'value' => $tag,
                        ];
                    }, UtmTracker::$plugin->settings->getTrackableTagsArray())
                ],
                'if' => 'valueSource == "tag"'
            ]),
            SchemaHelper::variableTextField([
                'label' => 'Default Value',
                'help' => 'Entering a default value will place the value in the field when the tracked field value is empty.',
                'name' => 'defaultValue',
            ]),
        ];
    }

    public function defineFormBuilderPreviewSchema(): array
    {
        return [
            SchemaHelper::previewInput([
                'type' => 'hidden',
                'wrapperClassName' => 'formie-field-preview-control formie-field-preview-control--hidden',
                'value' => '- user tracked data -',
            ]),
        ];
    }

    public static function getFrontEndInputTemplatePath(): string
    {
        return 'fields/hidden';
    }
}