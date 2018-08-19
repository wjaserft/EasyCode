<?php
/*
 * 基础控制器类
 * */
namespace easycode\core;
class Controller
{
    protected $smarty;

    public function __construct()
    {
        $this->initTimezone();  //初始化时区
        $this->initSmarty();  //初始化smarty
        $this->initConstPath();   //初始化模板常量路径
    }

    /* 初始化模板常量路径
     * 用户可以在index.php定义本常量
     * 如果没有定义本常量，则在此处定义常量并将常量分配至模板中
     * 如果已经定义了常量，则直接将常量分配至模板中
     */
    protected function initConstPath()
    {
        if(defined('PUBLIC_PATH'))
        {
            $this->smarty->assign('PUBLIC_PATH',PUBLIC_PATH);
        }else
        {
            define('PUBLIC_PATH','/');
            $this->smarty->assign('PUBLIC_PATH',PUBLIC_PATH);
        }
    }

    /*
     * 初始化时区
     * */
    protected  function initTimezone()
    {
        date_default_timezone_set($GLOBALS['config']['timezone']);
    }

    /*初始化smarty*/
    protected function initSmarty()
    {
        $this->smarty=new \Smarty();
        $this->smarty->left_delimiter='<{';
        $this->smarty->right_delimiter='}>';
        $this->smarty->setCompileDir(APP_PATH.MODULE.'/runtime/compile');
        $this->smarty->setTemplateDir(APP_PATH.MODULE.'/view');
        $this->smarty->caching=true;   //开启缓存
        $this->smarty->cache_lifetime=60;  //缓存时间
        $this->smarty->setCacheDir(APP_PATH.MODULE.'/runtime/cache');
    }

    /*页面提示信息并跳转*/
    protected function jump($url,$message,$delay=3)
    {
        header("refresh:$delay;$url");  //页面跳转
        echo '<p style="font-size:18px;margin-top:20px;margin-left:10px;">'.$message.'</p>';
        exit;
    }




}