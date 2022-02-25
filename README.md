# 介绍

mddoc 是一个开源的 markdown 文档转 html 文档工具，它使用 php 开发，支持 php &gt;= 5.4。它生成的 html 可以直接放在网站上，支持全文搜索、自定义 html 模版。

mddoc 同时支持命令行、git webhook生成，不光可以本地生成后上传，也可以直接做成 webhook 自动部署生成，非常简单。

mddoc 兼容 GitBook Editor 编辑的格式，可以说 mddoc 是不想装 Node 环境又想使用 gitbook 的用户的一个非常好的选择。

其实我开发 mddoc 的本意，其实是因为我用的虚拟主机，不能装 Node 环境。想要实现 webhook 自动生成比较困难，所以就用 php 写了这个……

具体效果你现在看的这个文档就是使用 mddoc 生成哒！（ https://doc.yurunsoft.com/mddoc ）

Github：https://github.com/Yurunsoft/mddoc

Gitee：https://gitee.com/yurunsoft/mddoc

composer：`yurunsoft/mddoc`

## 命令行生成

1. 首先通过 git 将项目拉取下来
2. 执行`composer update`安装
3. 执行`php build.php -markdownPath "md文档路径" -htmlPath "html生成路径"`

> 注意！html生成目录在生成前会被清空所有文件，请慎重选择目录！

默认生成使用的模版是`mddoc/template`下的，如果要指定自定义的模版路径，加上参数`-templatePath "路径"`
