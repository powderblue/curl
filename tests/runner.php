#!/usr/bin/env php
<?php

$projectDir = dirname(__DIR__);

require "{$projectDir}/vendor/autoload.php";
require "{$projectDir}/curl.php";

$reporter = new ztest\ConsoleReporter();
$reporter->enable_color();

$suite = new ztest\TestSuite('`Curl` and `CurlResponse` unit tests');
$suite->require_all(__DIR__ . '/unit');
$suite->auto_fill();
$suite->run($reporter);
