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
use craft\web\Request;
use digitalpulsebe\utmtracker\UtmTracker;

class Parameters extends Model
{

    public array $queryParameters = [];
    public string $landingUrl;
    public ?string $referrerUrl;

    static function createFromRequest(Request $request): self {
        $instance = new self();

        $instance->landingUrl = $request->getAbsoluteUrl();
        $instance->referrerUrl = $request->getReferrer();
        $instance->storeQueryParameters($request);

        return $instance;
    }

    public function storeQueryParameters(Request $request)
    {
        $tagsToTrack = UtmTracker::$plugin->getSettings()->getTrackableTagsArray();

        foreach($tagsToTrack as $tagKey) {
            if ($request->get($tagKey)) {
                $this->queryParameters[$tagKey] = $request->get($tagKey);
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
