<?php

namespace digitalpulsebe\utmtracker\storage;

use Craft;
use digitalpulsebe\utmtracker\models\Parameters;
use digitalpulsebe\utmtracker\UtmTracker;

class Cookie extends StorageMethod
{
    /**
     * Key used to store data in the cookie jar.
     */
    protected string $cookieName = 'utm_tracking_parameters';

    /**
     * Cookie lifetime in seconds (default: two days).
     */
    protected int $cookieLifetime = 172800;

    public function __construct()
    {
        $settings = UtmTracker::$plugin->getSettings();
        $this->cookieName = $settings->cookieName ?? $this->cookieName;
        $this->cookieLifetime = $settings->cookieLifetime ?? $this->cookieLifetime;

        // Initialise parameters to an empty instance so getParameters() is
        // always safe before load() / initFromRequest() / initFromUrl() is called.
        $this->parameters = new Parameters();
    }

    // =========================================================================
    // StorageMethod implementation
    // =========================================================================

    protected function load(): void
    {
        $this->isLoaded = true;
        if (Craft::$app->request->getCookies()->has($this->cookieName)) {
            try {
                $stored = new Parameters(unserialize(Craft::$app->request->getCookies()->get($this->cookieName)));

                if ($stored instanceof Parameters) {
                    $this->parameters = $stored;
                    Craft::info('UTM Tracker stored parameters loaded from existing cookie', 'utm_tracker');
                    return;
                }
            } catch (\Throwable $exception) {
                Craft::error(
                    'UTM Tracker stored parameters could not be loaded from cookie: ' . $exception->getMessage(),
                    'utm_tracker'
                );
            }
        }

        // No valid cookie found — this is a new visitor.
        $this->isNewUser = true;
        Craft::info('UTM Tracker data loaded from session', 'utm_tracker');
    }

    protected function persist(): void
    {
        $serialized = serialize($this->parameters?->toArray());

        // Skip writing when nothing has changed.
        if ($serialized === Craft::$app->request->getCookies()->get($this->cookieName)?->value) {
            return;
        }

        $cookie = Craft::createObject([
            'class' => 'yii\web\Cookie',
            'name' => $this->cookieName,
            'httpOnly' => true,
            'value' => $serialized,
            'expire' => time() + $this->cookieLifetime,
        ]);

        Craft::$app->getResponse()->getCookies()->add($cookie);
        Craft::info('UTM Tracker new cookie', 'utm_tracker');
    }
}
