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
        if (!is_dir($markdownPath))
        {
            mkdir($markdownPath, 0777, true);
        }
        $this->markdownPath = realpath($markdownPath);
        if (!is_dir($htmlPath))
        {
            mkdir($htmlPath, 0777, true);
        }
        $this->htmlPath = realpath($htmlPath);
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
        if (!\in_array($this->htmlPath, [
            '',
            './',
            '.',
            '/',
        ]))
        {
            File::clearDir($this->htmlPath);
        }
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
        if (is_file($catalogFileName))
        {
            $content = file_get_contents($catalogFileName);
            list($catalogList, $catalog, $fileNameRelation) = CategoryParser::parse($content, $this->markdownPath);
            // 列表
            $this->buildData['catalogList'] = $catalogList;
            // children关系
            $this->buildData['catalog'] = $catalog;
            // children关系
            $this->buildData['fileNameRelation'] = $fileNameRelation;
        }
    }

    /**
     * 生成文档.
     *
     * @return void
     */
    protected function buildDocs()
    {
        $markdownPathLen = \strlen($this->markdownPath) + 1;
        foreach (File::enumFile($this->markdownPath) as $file)
        {
            $mdFileFullName = $file[0];
            $mdFileName = substr($mdFileFullName, $markdownPathLen);
            $baseName = basename($mdFileFullName, '.' . pathinfo($mdFileFullName, \PATHINFO_EXTENSION));
            if ('SUMMARY' === $baseName)
            {
                $baseName = 'index';
            }
            $url = trim(str_replace('\\', '/', File::path(\dirname($mdFileName), $baseName . '.html')), './');
            $savePath = File::path($this->htmlPath, $url);
            $markdownContent = file_get_contents($mdFileFullName);
            preg_match('/#\s*([^\r\n]+)/', $markdownContent . \PHP_EOL, $matches);
            $title = $matches[1] ?? '';
            $articleContent = $this->markdownToHtml($markdownContent);

            if (isset($this->buildData['fileNameRelation'][$mdFileName]))
            {
                $item = $this->buildData['fileNameRelation'][$mdFileName];
            }
            else
            {
                $item = [
                    'id'        => '',
                    'title'     => $title,
                    'pageTitle' => $title,
                    'url'       => $url,
                ];
            }

            $this->articles[$item['id']] = $articleContent;
            ob_start();
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
        $content = preg_replace_callback('/(<h\d)(>)([^<]+)(<\/h\d>)/', function ($matches) {
            $anchorPoint = htmlspecialchars($matches[3]);

            return $matches[1] . ' id="' . $anchorPoint . '"' . $matches[2] . '<a href="#' . $anchorPoint . '">' . $matches[3] . '</a>' . $matches[4];
        }, $content);

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
