<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\ErrorHandler\ErrorHandler;

ErrorHandler::register(null, false);
