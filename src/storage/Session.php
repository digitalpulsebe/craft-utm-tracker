<?php

namespace digitalpulsebe\utmtracker\storage;

use Craft;
use craft\web\Request;
use digitalpulsebe\utmtracker\models\Parameters;

class Session implements StorageMethod
{
    /**
     * key to store data in the session
     * @var string
     */
    protected string $sessionKey = 'utm_tracking_parameters';

    /**
     * first request without parameters in session implies a new user
     * @var bool
     */
    protected bool $isNewUser = false;

    protected Parameters $parameters;

    public function __construct(Request $request)
    {
        if (Craft::$app->getSession()->has($this->sessionKey)) {
            $this->parameters = Craft::$app->getSession()->get($this->sessionKey);
            $this->parameters->storeQueryParameters($request);

            Craft::info('UTM Tracker stored parameters loaded from existing session', 'utm_tracker');
        } else {
            $this->parameters = Parameters::createFromRequest($request);
            $this->isNewUser = true;

            Craft::info('UTM Tracker new session', 'utm_tracker');
        }

        Craft::$app->getSession()->set($this->sessionKey, $this->parameters);
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