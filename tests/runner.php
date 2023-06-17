#!/usr/bin/env php
<?php

$testsDir = __DIR__;
$projectDir = dirname($testsDir);

require "{$projectDir}/vendor/autoload.php";

$reporter = new ztest\ConsoleReporter();
$reporter->enable_color();

$suite = new ztest\TestSuite('Curl unit tests');
$suite->require_all("{$testsDir}/src/UnitTestCase");
$suite->auto_fill();
$suite->run($reporter);
