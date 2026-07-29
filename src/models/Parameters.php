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

class Parameters extends Model
{

    public array $queryParameters = [];
    public string $absoluteLandingUrl = '';
    public string $landingUrl = '';
    public ?string $referrerUrl;

    public function processQueryParametersFromRequest(Request $request, bool $isNewUser): void
    {
        if ($isNewUser) {
            // First visit — capture URL and referrer before processing query params.
            $this->absoluteLandingUrl = StringHelper::escape($request->getAbsoluteUrl());
            $this->landingUrl = StringHelper::escape($request->getHostInfo() . '/' . $request->getPathInfo());
            $this->referrerUrl = $request->getReferrer()
                ? StringHelper::escape($request->getReferrer())
                : null;
        }

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

    public function processParametersFromUrl(string $url, string $referrerUrl = null): void {
        if (empty($this->absoluteLandingUrl)) {
            // this might be the first request we see
            $this->absoluteLandingUrl = $url;
            // url without query params
            $urlParts = parse_url($url);
            unset($urlParts['query']);
            $this->landingUrl = http_build_url($urlParts);
            $this->referrerUrl = $referrerUrl;
        }

        $tagsToTrack = UtmTracker::$plugin->getSettings()->getTrackableTagsArray();

        $parsedUrl = parse_url($url);
        $queryString = $parsedUrl['query'] ?? '';
        $queryParameters = [];
        parse_str($queryString, $queryParameters);

        foreach($tagsToTrack as $tagKey) {
            if (isset($queryParameters[$tagKey])) {
                $value = $queryParameters[$tagKey];
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
