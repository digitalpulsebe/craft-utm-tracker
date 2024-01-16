<?php
/**
 * UTM Tracker plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\utmtracker\variables;

use Craft;
use craft\helpers\App;
use craft\helpers\Template;
use craft\web\View;
use digitalpulsebe\utmtracker\models\Parameters;
use digitalpulsebe\utmtracker\UtmTracker;
use Twig\Markup;

class UtmTrackerVariable
{
    public function __get($name)
    {
        return $this->tag($name);
    }

    public function tag(string $key)
    {
        return $this->parameters()->getQueryParameter($key, '');
    }

    public function tags(): array
    {
        return $this->parameters()->queryParameters;
    }

    public function landingUrl(): string
    {
        return $this->parameters()->landingUrl;
    }

    public function referrerUrl(): ?string
    {
        return $this->parameters()->referrerUrl;
    }

    public function parameters(): ?Parameters
    {
        return UtmTracker::$plugin->storage->getParameters();
    }

    public function reportScript(): Markup
    {
        $templatePath = 'utm-tracker/_script/report';

        return Template::raw(Craft::$app->getView()->renderTemplate($templatePath, [], 'cp'));
    }

}
