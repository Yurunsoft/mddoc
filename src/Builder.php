<?php

namespace Yurun\MdDoc;

use HyperDown\Parser;

class Builder
{
    /**
     * markdown文档路径.
     *
     * @var string
     */
    public $markdownPath;

    /**
     * 要保存html的根路径.
     *
     * @var string
     */
    public $htmlPath;

    /**
     * 默认路径.
     *
     * @var string
     */
    public $templatePath;

    /**
     * 生成数据.
     *
     * @var array
     */
    protected $buildData;

    /**
     * 处理器.
     *
     * @var Parser
     */
    protected $parser;

    /**
     * 配置.
     *
     * @var array
     */
    protected $config;

    /**
     * 渲染数据.
     *
     * @var array
     */
    protected $renderData;

    /**
     * 文章内容.
     *
     * @var array
     */
    protected $articles;

    /**
     * 构造方法.
     *
     * @param string $markdownPath markdown文档路径
     * @param string $htmlPath     要保存html的根路径
     */
    public function __construct($markdownPath, $htmlPath)
    {
        $this->markdownPath = $markdownPath;
        $this->htmlPath = $htmlPath;
        $this->templatePath = static::getDefaultTemplatePath();
        $this->parser = new Parser();
    }

    /**
     * 获取默认模版路径.
     *
     * @return string
     */
    public static function getDefaultTemplatePath()
    {
        return File::path(\dirname(__DIR__), 'template');
    }

    /**
     * 开始生成html.
     *
     * @return void
     */
    public function build()
    {
        $this->loadConfig();
        $this->clearHtmlPath();
        $this->copyStatics();
        $this->prepareBuildData();
        $this->buildDocs();
        $this->buildSearchJS();
        $this->copyFiles();
    }

    /**
     * 加载配置.
     *
     * @return void
     */
    public function loadConfig()
    {
        $file = File::path($this->markdownPath, 'mddoc.json');
        if (is_file($file))
        {
            $this->config = json_decode(file_get_contents($file), true);
        }
        else
        {
            $this->config = [];
        }
    }

    /**
     * 清空html生成目录.
     *
     * @return void
     */
    public function clearHtmlPath()
    {
        File::clearDir($this->htmlPath);
    }

    /**
     * 复制静态文件们.
     *
     * @return void
     */
    public function copyStatics()
    {
        File::copy(File::path($this->templatePath, 'statics'), File::path($this->htmlPath, 'statics'));
    }

    /**
     * 准备生成数据.
     *
     * @return void
     */
    protected function prepareBuildData()
    {
        $this->buildData = [
            'catalog'   => [],
        ];

        $catalogFileName = File::path($this->markdownPath, 'SUMMARY.md');
        $content = file_get_contents($catalogFileName);
        list($catalogList, $catalog) = CategoryParser::parse($content);
        // 列表
        $this->buildData['catalogList'] = $catalogList;
        // children关系
        $this->buildData['catalog'] = $catalog;
    }

    /**
     * 生成文档.
     *
     * @return void
     */
    protected function buildDocs()
    {
        foreach ($this->buildData['catalogList'] as &$item)
        {
            if (!isset($item['url']))
            {
                continue;
            }
            ob_start();
            $fileName = $item['url'];
            $savePath = File::path($this->htmlPath, $fileName);
            $articleContent = $this->markdownToHtml(file_get_contents(File::path($this->markdownPath, $item['mdFileName'])));
            $this->articles[$item['id']] = $articleContent;
            $this->renderTemplate(File::path($this->templatePath, 'html/article.php'), [
                'data'              => $this->buildData,
                'currentCatalog'    => $item,
                'articleContent'    => $articleContent,
            ]);
            $content = ob_get_clean();
            $path = \dirname($savePath);
            if (!is_dir($path))
            {
                mkdir($path, 0777, true);
            }
            file_put_contents($savePath, $content);
        }
    }

    protected function renderTemplate($template, $data = [])
    {
        $this->renderData = $data;
        extract($data);
        include $template;
    }

    public function path($path)
    {
        $level = substr_count($this->renderData['currentCatalog']['url'], '/');

        return str_repeat('../', $level) . $path;
    }

    protected function buildSearchJS()
    {
        $savePath = File::path($this->htmlPath, 'statics/js/mddoc-search.js');
        ob_start();
        $searchDatas = [];
        foreach ($this->buildData['catalogList'] as $item)
        {
            if (isset($item['url']))
            {
                $item['content'] = preg_replace('/<[^>]+>/', '', $this->articles[$item['id']]);
                $searchDatas[] = $item;
            }
        }
        include File::path($this->templatePath, 'html/search.php');
        $content = ob_get_clean();
        file_put_contents($savePath, $content);
    }

    public function markdownToHtml($content)
    {
        $content = $this->parser->makeHtml($content);
        $content = str_replace('<li>[x]', '<li><input type="checkbox" disabled checked/>', $content);
        $content = str_replace('<li>[ ]', '<li><input type="checkbox" disabled/>', $content);

        return $content;
    }

    protected function copyFiles()
    {
        File::copy($this->markdownPath, $this->htmlPath, function ($from, $to) {
            $ext = pathinfo($from, \PATHINFO_EXTENSION);
            if (\in_array($ext, ['md']))
            {
                return false;
            }
        });
    }
}
