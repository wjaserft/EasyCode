<?php
namespace home\controller;
use easycode\core\Controller;
use easycode\tools\Captcha;
class Index extends  Controller
{
    /*
     * demo
     * */
    public function actionIndex()
    {
        echo 'Hello World!<br/>';
        echo date('Y-m-d H:i:s');
    }

    /*
     * demo演示验证码类
     * */
    public function actionCaptcha()
    {
        $captcha = new Captcha(200,40);   //实例化验证码工具类
        $captcha->makeImage();   //产生验证码
    }


}