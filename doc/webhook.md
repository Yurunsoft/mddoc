# Webhook 生成

有两个方法可选

## 方法一

1. 首先通过 git 将项目拉取下来
2. 执行`composer update`安装
3. 修改`config/github.php`配置文件，详见该文件注释
4. 在`github`或`gitee`中配置`webhook`，url为`http://xxx.com/github-webhook.php?password={password}`，`{password}`为你配置文件中设置的

> 注意！html生成目录在生成前会被清空所有文件，请慎重选择目录！

## 方法二

1. 通过`composer`将`mddoc`作为包引入你的项目
2. 新建一个`webhook`入口文件
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Yurun\MdDoc\GithubWebhook;

// $config 随便你用什么方法加载或者设定，只要传入给下面构造方法里即可
// $config = include __DIR__ . '/config/config.php';
// $config = [];

$webhook = new GithubWebhook($config);
$webhook->handle();
```
3. 在`github`或`gitee`中配置`webhook`，url为`http://xxx.com/github-webhook.php?password={password}`，`{password}`为你配置文件中设置的

> 注意！html生成目录在生成前会被清空所有文件，请慎重选择目录！