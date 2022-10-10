<?php
/**
 * UTM Tracker plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\utmtracker\models;

use craft\base\Model;
use craft\helpers\StringHelper;
use craft\web\Request;
use digitalpulsebe\utmtracker\UtmTracker;
use voku\helper\AntiXSS;

class Parameters extends Model
{

    public array $queryParameters = [];
    public string $absoluteLandingUrl;
    public string $landingUrl;
    public ?string $referrerUrl;

    static function createFromRequest(Request $request): self {
        $instance = new self();

        $instance->absoluteLandingUrl = StringHelper::escape($request->getAbsoluteUrl());
        $instance->landingUrl = StringHelper::escape($request->getHostInfo() . '/' . $request->getPathInfo());
        $instance->referrerUrl = $request->getReferrer() ? StringHelper::escape($request->getReferrer()) : null;
        $instance->storeQueryParameters($request);

        return $instance;
    }

    public function storeQueryParameters(Request $request)
    {
        $tagsToTrack = UtmTracker::$plugin->getSettings()->getTrackableTagsArray();

        foreach($tagsToTrack as $tagKey) {
            if ($request->get($tagKey)) {
                $value = $request->getQueryParam($tagKey);
                if ($value) {
                    $clean_value = StringHelper::stripHtml($value);
                    $clean_value = StringHelper::escape($clean_value);
                    $this->queryParameters[$tagKey] = $clean_value;
                }
            }
        }
    }

    public function getQueryParameter(string $key, string $default = null): ?string
    {
        if (!isset($this->queryParameters[$key])) {
            return $default;
        }

        return $this->queryParameters[$key];
    }

}
