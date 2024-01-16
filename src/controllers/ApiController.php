<?php

namespace digitalpulsebe\utmtracker\controllers;

use craft\web\Controller;
use digitalpulsebe\utmtracker\UtmTracker;
use yii\console\ExitCode;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;
    public array|bool|int $allowAnonymous = true;

    public function actionData()
    {
        $params = UtmTracker::$plugin->storage->getParameters();

        return $this->asJson(['data' => $params]);
    }
}
