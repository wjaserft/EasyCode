<?php
/*
 * 框架初始化
 * 实现类文件自动加载以及路由转发
 * */
namespace easycode\core;
new EasyCode();  //实例化框架初始化类
class EasyCode
{
    /*构造函数*/
    public function  __construct()
    {
       /*初始化常量*/
        $this->initConst();   //定义路径常量
        $config1=$this->loadFrameworkConfig();   //加载框架配置文件
        $config2=$this->loadAppConfig();     //加载应用配置文件
        $GLOBALS['config']=array_merge($config1,$config2);   //合并配置文件

        /*自动加载及路由转发*/
        $this->registerAutoload();   //自动加载
        $this->initMCA();  //路由转发初始化
        $config3=$this->loadModuleConfig();    //加载模块配置文件,由于此方法中使用了initMCA()中定义的常量，故需要放在initMCA()方法下面
        $GLOBALS['config']=array_merge($GLOBALS['config'],$config3);  //合并最终的配置文件
        $this->dispatch();  //实现路由转发
    }

    /*初始化路径常量*/
    protected function initConst()
    {
        define('APP_NAME','application');   //定义项目名称
        $now_path = str_replace('\\','/',getcwd());    //产生当前的工作路径
        define('ROOT_PATH',$now_path.'/');    //定义根目录常量
        define('APP_PATH',$now_path.'/'.APP_NAME.'/');     //定义应用目录常量
    }

    /*加载框架配置，配置的优先级最低*/
    protected function loadFrameworkConfig()
    {
        $config_path=ROOT_PATH."easycode/config/config.php";
        return require_once $config_path;
    }

    /*加载应用配置，配置的优先级高于框架配置文件*/
    protected function loadAppConfig()
    {
        $config_path=APP_PATH."common/config/config.php";
        if(file_exists($config_path))
        {
            return require_once $config_path;
        }else
        {
            return array();
        }
    }

    /*加载模块配置文件，配置的优先级最高*/
    protected function loadModuleConfig()
    {
        $config_path=APP_PATH.MODULE."/config/config.php";
        if(file_exists($config_path))
        {
            return require_once $config_path;
        }else
        {
            return array();
        }
    }

    /*注册自动加载*/
    protected function registerAutoload()
    {
        spl_autoload_register(array($this,'autoloaderFunc'));   //注册自动加载函数
    }

    /*自动加载处理函数*/
    protected function autoloaderFunc($class_name)
    {
        if($class_name=='Smarty')
        {
            require_once ROOT_PATH."vendor/smarty/Smarty.class.php";
            return;
        }
        //分拆类名的各个部分
        $arr=explode('\\',$class_name);
        if($arr['0']=='easycode')
        {
            $basic_path=ROOT_PATH;    //由于命名空间中已经存在框架的名称部分，故此处直接使用根目录即可
        }else
        {
            $basic_path = APP_PATH;     //由于应用程序的名称可能会改变，故在实现自动加载时做额外的判断
        }
        $sub_path=str_replace('\\','/',$class_name);   //将反斜杠替换成正斜杠
        $class_path=$basic_path.$sub_path;   //拼接路径
        //判断是否是接口文件
        if(!file_exists($class_path.'.interface.php'))
        {
            if(file_exists($class_path.'.php'))
            {
                require_once $class_path.'.php';
            }
        }else
        {
            require_once $class_path.'.interface.php';
        }
    }

    /*接受get参数，确定MCA路由参数*/
    protected function initMCA()
    {
        //接受用户传入的参数，确定用户要访问的地址
        $m=isset($_GET['m'])?$_GET['m']:$GLOBALS['config']['default_module'];
        $c=isset($_GET['c'])?$_GET['c']:$GLOBALS['config']['default_controller'];
        $a=isset($_GET['a'])?$_GET['a']:$GLOBALS['config']['default_action'];
        $c=ucfirst($c);    //首字母大写
        $a='action'.ucfirst($a);
        //将路由参数保存至常量，用于在本类中实现共享
        define('MODULE',$m);
        define('CONTROLLER',$c);
        define('ACTION',$a);
    }

    /*实例化对象并调用方法，实现路由转发*/
    protected function dispatch()
    {
        $class_path=APP_PATH.MODULE."/controller/".CONTROLLER.".php";
        if(!file_exists($class_path))
        {
            echo "你访问的链接不存在！";
            exit;
        }
        $class=MODULE.'\\controller\\'.CONTROLLER;   //将命名空间与控制器名进行拼接
        $instance=new $class;
        if(!method_exists($instance,ACTION))    //判断需要访问的方法是否存在
        {
            echo '方法不存在！';
            return;
        }
        $action=ACTION;
        $instance->$action();   //调用需要访问的方法
    }
}

