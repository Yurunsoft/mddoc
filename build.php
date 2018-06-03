<?php
// 禁止非 cli 方式运行
if('cli' !== PHP_SAPI)
{
    exit('are you ok?');
}
require_once __DIR__ . '/vendor/autoload.php';

use Yurun\MdDoc\Args;
use Yurun\MdDoc\Builder;

Args::init();

$markdownPath = Args::get('markdownPath');
if(null === $markdownPath)
{
    exit('-markdownPath can not be null');
}

$htmlPath = Args::get('htmlPath');
if(null === $htmlPath)
{
    exit('-htmlPath can not be null');
}

$builder = new Builder($markdownPath, $htmlPath);
$builder->build();