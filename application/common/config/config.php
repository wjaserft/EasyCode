<?php
/*
 * 应用配置文件
 * */
return array(
    //数据库配置信息
    'host'  =>  '',    //数据库主机地址
    'user_name'  =>  '',   //数据库用户名
    'password'  =>  '',    //密码
    'db_name'   =>  '',    //数据库名
    'table_prefix'  =>  '',     //数据表前缀

    //配置smarty
    'left_delimiter'    =>  '<{',  //smarty左边界符
    'right_delimiter'   =>  '}>',   //smarty右边界符
    'smarty_caching'    =>  true,   //是否开启smarty缓存
    'smarty_cache_lifetime' =>  60,   //缓存的时间

    //默认框架配置信息,不能在应用配置处修改
    'default_module'  =>  'home',     //默认分组名称
    'default_controller'    =>  'index',   //默认控制器名称
    'default_action'    =>  'index',     //默认操作方法名称

    //时区,具体参数可参考php手册的时区部分
    'timezone' => 'PRC'
);