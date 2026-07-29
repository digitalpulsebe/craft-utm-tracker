<?php
/**
 * UTM Tracker plugin for Craft CMS 3.x
 *
 * Track landing query parameters in the user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\utmtracker\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\web\Request;
use digitalpulsebe\utmtracker\storage\Cookie;
use digitalpulsebe\utmtracker\storage\Session;
use digitalpulsebe\utmtracker\storage\StorageMethod;
use digitalpulsebe\utmtracker\UtmTracker;
use yii\web\View;
use Exception;

class UtmTrackerService extends Component
{
    /**
     * Instantiate the configured storage method and — when not running in async
     * mode — immediately load and persist parameters from the current request.
     *
     * In async mode the storage instance is returned with an empty Parameters
     * object; the actual load and persist happens later via actionReport() when
     * the browser posts the page URL back through the API.
     * @throws Exception
     */
    public function processRequest(Request $request, bool $async = false): ?StorageMethod
    {
        if (!$request->isSiteRequest) {
            Craft::info('UTM Tracker plugin loaded, but this is not a site request', 'utm_tracker');
            return null;
        }

        $storage = $this->createStorage();

        if (!$async) {
            $storage->setFromRequest($request);
        }

        return $storage;
    }

    /**
     * Inject the async fetch snippet that posts the current page URL to the
     * report API endpoint so UTM parameters can be captured after the cached
     * page has been served.
     */
    public function registerAsyncScript(Request $request): void
    {
        if (!$request->getIsSiteRequest()) {
            return;
        }

        $actionUrl = UrlHelper::actionUrl('utm-tracker/api/report');
        Craft::$app->getView()->registerJs(<<<JS
document.addEventListener("DOMContentLoaded",()=>fetch("$actionUrl",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:new URLSearchParams({url:window.location.href,referrerUrl:document.referrer})}).catch(e=>console.debug(e)));
JS
        , View::POS_END);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * create the storage instance based on the configured method
     * @throws Exception
     */
    private function createStorage(): StorageMethod
    {
        $storageMethod = UtmTracker::$plugin->getSettings()->storageMethod;

        return match ($storageMethod) {
            'session' => new Session(),
            'cookies' => new Cookie(),
            default   => throw new Exception("Storage method $storageMethod unknown for UTM Tracker"),
        };
    }
}
