<?php

namespace digitalpulsebe\utmtracker\storage;

use craft\web\Request;
use digitalpulsebe\utmtracker\models\Parameters;

interface StorageMethod
{
    public function __construct(Request $request);

    public function isNewUser();

    public function getParameters(): Parameters;
}