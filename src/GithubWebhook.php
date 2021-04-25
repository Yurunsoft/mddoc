<?php

namespace Yurun\MdDoc;

use Yurun\Util\HttpRequest;

class GithubWebhook
{
    /**
     * 配置.
     *
     * @var array
     */
    protected $config;

    public function __construct($config)
    {
        ignore_user_abort(true);
        $this->config = $config;
    }

    public function handle()
    {
        // 密码验证
        if (!isset($_GET['password'], $this->config['password']) || $this->config['password'] !== $_GET['password'] || '' === $this->config['password'])
        {
            exit('are you ok 1?');
        }

        // post数据
        $data = json_decode(file_get_contents('php://input'), true);

        // 事件验证
        if (!isset($_SERVER['HTTP_X_GITHUB_EVENT']) || !\in_array($_SERVER['HTTP_X_GITHUB_EVENT'], $this->config['events']))
        {
            exit('are you ok 2?');
        }

        // 下载

        if (is_dir($this->config['downloadSavePath']))
        {
            File::clearDir($this->config['downloadSavePath']);
        }
        else
        {
            mkdir($this->config['downloadSavePath'], 0777, true);
        }
        $zipFileName = File::path($this->config['downloadSavePath'], 'download.zip');
        $http = new HttpRequest();
        $http->download($zipFileName, $this->config['downloadUrl']);

        // 解压

        $zip = new \ZipArchive();
        $return = $zip->open($zipFileName);
        if (true !== $return)
        {
            exit($zipFileName . ' 文件打开失败');
        }
        if (!$zip->extractTo($this->config['downloadSavePath']))
        {
            exit($zipFileName . ' 文件解压失败');
        }
        $firstFileName = $zip->getNameIndex(0);
        $zip->close();

        // 生成

        $builder = new Builder(File::path($this->config['downloadSavePath'], $firstFileName), $this->config['htmlSavePath']);
        if (isset($this->config['templatePath']))
        {
            $builder->templatePath = $this->config['templatePath'];
        }
        $builder->build();
    }
}
