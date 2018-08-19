<?php
/*验证码类
 * Author:Hao
 * */
namespace easycode\tools;
class Captcha
{
    private $captcha_w;    //验证码图形的宽
    private $captcha_h;     //验证码图像的高度


    /*构造函数，用于执行初始化操作*/
    public function __construct($captcha_w,$captcha_h)
    {
        $this->captcha_w=$captcha_w;
        $this->captcha_h=$captcha_h;
    }

    /*
     * 生成验证码图形
     * @param string $font_file 验证码字体文件
     * @param int $font_size  验证码字体大小
     * @param int $char_len   验证码文字数量
     * */
    public function makeImage($font_file=ROOT_PATH.'easycode/tools/arial.ttf',$font_size=14,$char_len=4)
    {
        if($char_len>10 || $char_len<1)
        {
            die('验证码字符个数不符合规范！');
        }
        $code=$this->makeCode($char_len);
        if(!isset($_SESSION))
        {
            session_start();
        }
        $_SESSION['captcha_code']=$code;   //将验证码字符存入session
        $img=imagecreatetruecolor($this->captcha_w,$this->captcha_h);  //创建验证码画布
        $bg_color=imagecolorallocate($img,186,186,186);
        imagefill($img,0,0,$bg_color);  //填充背景颜色
        //产生黑或者白
        mt_rand(0,1) ?$char_color=imagecolorallocate($img,0,0,0):$char_color=imagecolorallocate($img,255,255,255);
        //绘制文字
        for($i=0;$i<strlen($code);$i++)
        {
            $char_x=($this->captcha_w-10)/$char_len*$i+7;
            $char_y=20;
            imagettftext($img,$font_size,mt_rand(-30,30),$char_x,$char_y,$char_color,$font_file,$code[$i]);
        }
        //绘制干扰像素点
        for($i=0;$i<200;$i++)
        {
            $color1=imagecolorallocate($img,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
            imagesetpixel($img,mt_rand(0,$this->captcha_w),mt_rand(0,$this->captcha_h),$color1);
        }
        header("content-type:image/png");
        imagepng($img);
        imagedestroy($img);
    }

    /*生成验证码随机字符*/
    private function makeCode($char_len)
    {
        $code='';  //用于存储最终产生的验证码字符
        $code_arr=array_merge(range('a','z'),range('A','Z'),range(3,9));  //在一定区间产生随机字符数组
        $rand_keys=array_rand($code_arr,$char_len);  //随机获取字符数组的键名
        if($char_len==1)
        {
            $rand_keys=array($rand_keys);  //由于当个数为1时，array_rand返回的结果不是一个数组，所以此处对字符个数为1时进行处理，让rand_keys始终保持为一个数组
        }
        shuffle($rand_keys);   //将键名打乱
        //获取字符并进行拼接
        foreach($rand_keys as $value)
        {
            $code.=$code_arr[$value];
        }
        return $code;  //返回验证码字符串
    }
}