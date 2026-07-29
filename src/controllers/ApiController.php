<?php

namespace digitalpulsebe\utmtracker\controllers;

use Craft;
use craft\web\Controller;
use digitalpulsebe\utmtracker\UtmTracker;
use yii\web\NotFoundHttpException;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;
    public array|bool|int $allowAnonymous = true;

    /**
     * Return the currently stored UTM parameters as JSON.
     * Used by external consumers that need to read tracked data.
     */
    public function actionData(): \yii\web\Response
    {
        $params = UtmTracker::$plugin->storage->getParameters();

        return $this->asJson(['data' => $params]);
    }

    /**
     * Async tracking endpoint.
     *
     * The browser posts the current page URL here after a cached page has been
     * served. We load stored data, merge UTM parameters parsed from the URL,
     * and persist — mirroring what initFromRequest() does on synchronous requests.
     */
    public function actionReport(): \yii\web\Response
    {
        $this->requirePostRequest();

        $url = $this->request->getBodyParam('url', '');
        $referrerUrl = $this->request->getBodyParam('referrerUrl', null);

        $storage = UtmTracker::$plugin->storage;

        $storage->setFromUrl($url, $referrerUrl);

        return $this->asJson([
            'success' => true
        ]);
    }
}
