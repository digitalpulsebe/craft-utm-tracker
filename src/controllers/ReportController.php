<?php

namespace digitalpulsebe\utmtracker\controllers;

use craft\web\Controller;
use yii\console\ExitCode;

/**
 * @deprecated since 3.1.0
 */
class ReportController extends Controller
{
    public $enableCsrfValidation = false;
    public array|bool|int $allowAnonymous = true;

    public function actionUrl()
    {
        return $this->asJson(['success' => true]);
    }
}
