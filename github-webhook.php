<?php
require_once __DIR__ . '/vendor/autoload.php';

use Yurun\MdDoc\GithubWebhook;

$config = include __DIR__ . '/config/github.php';

$webhook = new GithubWebhook($config);
$webhook->handle();