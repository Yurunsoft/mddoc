<?php
require_once __DIR__ . '/vendor/autoload.php';

use Yurun\MdDoc\File;
use Yurun\MdDoc\Builder;
use Yurun\Util\HttpRequest;

$config = include __DIR__ . '/config/github.php';

// 密码验证
if(!isset($_GET['password'], $config['password']) || $config['password'] !== $_GET['password'] || '' === $config['password'])
{
    exit('are you ok?');
}

// post数据
$data = json_decode(file_get_contents('php://input'), true);

// 事件验证
if(!isset($_SERVER['HTTP_X_GITHUB_EVENT']) || !in_array($_SERVER['HTTP_X_GITHUB_EVENT'], $config['events']))
{
    exit('are you ok?');
}

// 下载

if(is_dir($config['downloadSavePath']))
{
    File::clearDir($config['downloadSavePath']);
}
else
{
    mkdir($config['downloadSavePath'], 0777, true);
}
$zipFileName = File::path($config['downloadSavePath'], 'download.zip');
$http = new HttpRequest;
$http->download($zipFileName, $config['downloadUrl']);

// 解压

$zip = new ZipArchive;
$return = $zip->open($zipFileName);
if(true !== $return)
{
    exit($zipFileName . ' 文件打开失败');
}
if(!$zip->extractTo($config['downloadSavePath']))
{
    exit($zipFileName . ' 文件解压失败');
}
$zip->close();

// 生成

$builder = new Builder($config['downloadSavePath'], $config['htmlSavePath']);
$builder->build();