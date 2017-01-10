# 微信小程序-极客社区

## 使用
1、服务器端使用开源项目wordpress,需要PHP+MYSQL环境,把server目录下文件拷贝至虚拟主机,修改wp-config.php的数据库配置信息,
导入server/sql目录下的sql文件

2、小程序服务器端需要使用HTTPS,本地开发可以使用ngrok:[参考配置](http://www.ittun.com/)

3、克隆本项目 -> 在微信开发工具中添加项目 -> 选择项目中的app目录

4、修改app.js中的serverUrl为你的域名,再根据小程序开发文档配置其余相关内容,即可看到完整的程序

## 资源

* [微信小程序官方文档](https://mp.weixin.qq.com/debug/wxadoc/dev/?t=201715)
* [服务器端WordPress基础教程](https://codex.wordpress.org/zh-cn:Main_Page)
* [JSON API 文档](https://wordpress.org/plugins/json-api/)
* [wxParse文档](https://github.com/icindy/wxParse)
* [小程序版WeUI文档](https://github.com/weui/weui-wxss)

## 广告

需要小程序开发请联系企鹅号:570841034