# EasyCode
简易的PHP MVC框架。正在完善中，目前不建议生产环境使用

## 目录说明
application 应用目录

application/home  模块目录，名称可自定义

application/common/config/config.php 应用配置文件

application/home/config/config.php 模块配置文件

easycode 框架目录

easycode/config/config.php 框架配置文件

easycode/code 框架核心文件目录

easycode/dao 数据库访问对象工具目录

easycode/tools 框架工具目录，提供验证码工具类、文件上传下载类、图片压缩类、图片添加水印类等。支持自动加载。

vendor 外部扩展目录

## 使用说明
在根目录建立index.php入口文件，引入框架初始化文件即可

`require_once "./easycode/core/EasyCode.php";`

此时访问index.php文件，框架会自动访问home模块-Index控制器-index方法。如需修改默认模块、控制器、方法，可在配置文件进行修改。

在项目开发中可通过use引入需要的类库。












