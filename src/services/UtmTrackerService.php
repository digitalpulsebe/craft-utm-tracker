<?php
/**
 * UTM Tracker plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\utmtracker\services;

use Craft;
use craft\base\Component;
use craft\web\Request;
use digitalpulsebe\utmtracker\storage\Cookie;
use digitalpulsebe\utmtracker\storage\Session;
use digitalpulsebe\utmtracker\storage\StorageMethod;
use digitalpulsebe\utmtracker\UtmTracker;

class UtmTrackerService extends Component
{
    public function processRequest($request): ?StorageMethod
    {
        if ($request instanceof Request && $request->isSiteRequest) {

            $storageMethod = UtmTracker::$plugin->getSettings()->storageMethod;

            if ($storageMethod == 'session') {
                $storage = new Session($request);
            } elseif ($storageMethod == 'cookies') {
                $storage = new Cookie($request);
            } else {
                throw new \Exception("Storage method $storageMethod unknown for UTM Tracker");
            }

            Craft::info($storage->getParameters()->toArray(), 'utm_tracker');
            Craft::info('UTM Tracker plugin loaded', 'utm_tracker');

            return $storage;
        }

        Craft::info('UTM Tracker plugin loaded, but this is not a site request', 'utm_tracker');
        return null;
    }
}
