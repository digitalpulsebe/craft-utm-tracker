<?php

namespace digitalpulsebe\utmtracker\controllers;

use craft\web\Controller;
use yii\console\ExitCode;

class ReportController extends Controller
{
    public $enableCsrfValidation = false;
    public array|bool|int $allowAnonymous = true;

    public function actionUrl()
    {
        return $this->asJson(['success' => true]);
    }
}
