<?php
return [
    // 密码，为空时不会启用，填写后在webhook url中填写为http://xxx.com/github-webhook.php?password={password}
    'password'  =>  '',
    // 更新文档的事件
    'events'    =>  [
        'push',
        'pull_request',
        'ping',
    ],
    // 要下载的文件地址，一般可以用github提供的
    'downloadUrl'   =>  'https://github.com/{username}/{projectName}/archive/master.zip',
    // 下载文件保存路径（下载前会清空该目录，请谨慎选择目录）
    'downloadSavePath'  =>  dirname(__DIR__) . '/tmp',
    // 生成的html保存路径（生成前会清空该目录，请谨慎选择目录）
    'htmlSavePath'  =>  dirname(__DIR__) . '/html',
    // 模版目录
    // 'templatePath'  =>  dirname(__DIR__) . '/template',
];