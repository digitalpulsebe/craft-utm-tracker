<?php
/**
 * UTM Tracker plugin for Craft CMS 3.x
 *
 * Track landing query parameters in user session
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

/**
 * UTM Tracker config.php
 *
 * This file exists only as a template for the UTM Tracker settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'utm-tracker.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    /*
     * storageMethod supported methods:
     * session|cookies
     */
    'storageMethod' => 'session',
    'trackableTags' => [
        ['key' => 'utm_source'],
        ['key' => 'utm_medium'],
        ['key' => 'utm_campaign'],
        ['key' => 'utm_term'],
        ['key' => 'utm_content'],
        ['key' => 'custom_utm_query_parameter'],
    ],
    'cookieName' => 'custom_utm_tracking_parameters',
    'cookieLifetime' => 172800 // two days in seconds
];
