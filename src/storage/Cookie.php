<?php

namespace digitalpulsebe\utmtracker\storage;

use Craft;
use craft\web\Request;
use digitalpulsebe\utmtracker\models\Parameters;
use digitalpulsebe\utmtracker\UtmTracker;

class Cookie implements StorageMethod
{
    /**
     * key to store data in the cookies
     * @var string
     */
    protected string $cookieName = 'utm_tracking_parameters';

    /**
     * lifetime in seconds
     * @var int
     */
    protected int $cookieLifetime = 172800; // two days in seconds: 60 * 60 * 24 * 2

    /**
     * first request without parameters in session implies a new user
     * @var bool
     */
    protected bool $isNewUser = false;

    protected Parameters $parameters;

    public function __construct(Request $request)
    {
        $this->cookieName = UtmTracker::$plugin->getSettings()->cookieName ?? $this->cookieName;
        $this->cookieLifetime = UtmTracker::$plugin->getSettings()->cookieLifetime ?? $this->cookieLifetime;

        if (Craft::$app->request->getCookies()->has($this->cookieName)) {
            $this->parameters = unserialize(Craft::$app->request->getCookies()->get($this->cookieName));
            $this->parameters->storeQueryParameters($request);

            Craft::info('UTM Tracker stored parameters loaded from existing cookie', 'utm_tracker');
        } else {
            $this->parameters = Parameters::createFromRequest($request);
            $this->isNewUser = true;

            Craft::info('UTM Tracker new cookie', 'utm_tracker');
        }

        if (serialize($this->parameters) != Craft::$app->request->getCookies()->get($this->cookieName)) {
            // only if changed
            $cookie = Craft::createObject([
                'class' => 'yii\web\Cookie',
                'name' => $this->cookieName,
                'httpOnly' => true,
                'value' => serialize($this->parameters),
                'expire' => time() + $this->cookieLifetime,
            ]);

            Craft::$app->getResponse()->getCookies()->add($cookie);
        }
    }

    public function isNewUser()
    {
        return $this->isNewUser();
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }
}