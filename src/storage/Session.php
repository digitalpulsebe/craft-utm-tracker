<?php

namespace digitalpulsebe\utmtracker\storage;

use Craft;
use digitalpulsebe\utmtracker\models\Parameters;

class Session extends StorageMethod
{
    /**
     * Key used to store data in the session.
     */
    protected string $sessionKey = 'utm_tracking_parameters';

    public function __construct()
    {
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
        if (Craft::$app->getSession()->has($this->sessionKey)) {
            $stored = Craft::$app->getSession()->get($this->sessionKey);

            if ($stored instanceof Parameters) {
                $this->parameters = $stored;
                Craft::info('UTM Tracker stored parameters loaded from existing session', 'utm_tracker');
                return;
            }
        }

        // No valid session entry found — this is a new visitor.
        $this->isNewUser = true;
        Craft::info('UTM Tracker data loaded from session', 'utm_tracker');
    }

    protected function persist(): void
    {
        Craft::$app->getSession()->set($this->sessionKey, $this->parameters);
        Craft::info('UTM Tracker new session', 'utm_tracker');
    }
}
