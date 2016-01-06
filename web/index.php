<?php

require_once __DIR__ . '/../vendor/autoload.php';

$ffFactory = \WMDE\Fundraising\Frontend\FFFactory::newFromConfig();

/**
 * @var \Silex\Application $app
 */
$app = require __DIR__ . '/../app/bootstrap.php';

$app->run();