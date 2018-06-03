<?php
namespace Yurun\MdDoc;

class Builder
{
    /**
     * markdown文档路径
     * @var string
     */
    public $markdownPath;

    /**
     * 要保存html的根路径
     * @var string
     */
    public $htmlPath;

    /**
     * 默认路径
     * @var string
     */
    public $templatePath;

    /**
     * 构造方法
     * @param string $markdownPath markdown文档路径
     * @param string $htmlPath 要保存html的根路径
     */
    public function __construct($markdownPath, $htmlPath)
    {
        $this->markdownPath = $markdownPath;
        $this->htmlPath = $htmlPath;
        $this->templatePath = static::getDefaultTemplatePath();
    }

    /**
     * 获取默认模版路径
     * @return string
     */
    public static function getDefaultTemplatePath()
    {
        return File::path(dirname(__DIR__), 'template');
    }

    /**
     * 开始生成html
     * @return void
     */
    public function build()
    {
        $this->clearHtmlPath();
        $this->copyStatics();
    }

    /**
     * 清空html生成目录
     * @return void
     */
    public function clearHtmlPath()
    {
		File::clearDir($this->htmlPath);
    }

    /**
     * 复制静态文件们
     * @return void
     */
    public function copyStatics()
    {
        File::copy(File::path($this->templatePath, 'js'), File::path($this->htmlPath, 'js'));
        File::copy(File::path($this->templatePath, 'css'), File::path($this->htmlPath, 'css'));
        File::copy(File::path($this->templatePath, 'images'), File::path($this->htmlPath, 'images'));
    }
}