# 命令行生成

## 使用步骤

1. 首先通过 git 将项目拉取下来
2. 执行`composer update`安装
3. 执行`php build.php -markdownPath "md文档路径" -htmlPath "html生成路径"`

> 注意！html生成目录在生成前会被清空所有文件，请慎重选择目录！

默认生成使用的模版是`mddoc/template`下的，如果要指定自定义的模版路径，加上参数`-templatePath "路径"`