# 介绍

mddoc 是一个开源的 markdown 文档转 html 文档工具，它使用 php 开发，支持 php &gt;= 5.4。它生成的 html 可以直接放在网站上，支持全文搜索、自定义 html 模版。

mddoc 同时支持命令行、git webhook生成，不光可以本地生成后上传，也可以直接做成 webhook 自动部署生成，非常简单。

mddoc 兼容 GitBook Editor 编辑的格式，可以说 mddoc 是不想装 Node 环境又想使用 gitbook 的用户的一个非常好的选择。

其实我开发 mddoc 的本意，其实是因为我用的虚拟主机，不能装 Node 环境。想要实现 webhook 自动生成比较困难，所以就用 php 写了这个……

具体效果你现在看的这个文档就是使用 mddoc 生成哒！（https://doc.yurunsoft.com/mddoc）

Github：https://github.com/Yurunsoft/mddoc.git

Gitee：https://gitee.com/yurunsoft/mddoc

composer：`yurunsoft/mddoc`