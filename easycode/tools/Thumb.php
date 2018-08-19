<?php
/**
 * Class Thumb
 * 图片压缩
 * Author：Hao
 */
namespace easycode\tools;
use \Finfo;
class Thumb
{
    private $dst_img_w;   //压缩后的图片宽度
    private $dst_img_h;   //压缩后的图片高度

    //建立图片MIME类型和对应函数的映射，用于创建不同类型的图片资源
    private $create_func=array(
        'image/jpeg'=>'imagecreatefromjpeg',
        'image/gif'=>'imagecreatefromgif',
        'image/png'=>'imagecreatefrompng'
     );

    //建立图片MIME类型和对应函数的映射，用于输出不同类型的图片
    private $output_func=array(
        'image/jpeg'=>'imagejpeg',
        'image/gif'=>'imagegif',
        'image/png'=>'imagepng'
    );

    /*构造函数，执行初始化操作*/
    public function __construct($dst_img_w,$dst_img_h)
    {
        $this->dst_img_w=$dst_img_w;
        $this->dst_img_h=$dst_img_h;
    }

    /** 制作缩略图
     *
     * */
    public function makeThumb($input_file,$output_file)
    {
        if(!file_exists($input_file))
        {
            die('文件不存在！');
        }
        //获取输入文件的mime类型
        $finfo=new Finfo(FILEINFO_MIME_TYPE);
        $input_file_mime=$finfo->file($input_file);
        //创建图片资源
        $src_img=$this->create_func[$input_file_mime]($input_file);
        //获取图片的宽高
        $src_img_w=imagesx($src_img);
        $src_img_h=imagesy($src_img);
        //计算图片最优缩放的倍数（商较大的一个值通常为最优倍数）
        if(($src_img_h/$this->dst_img_h)>($src_img_w/$this->dst_img_w))
        {
            $scale=$src_img_h/$this->dst_img_h;
        }else
        {
            $scale=$src_img_w/$this->dst_img_w;
        }
        //依据压缩的倍数，计算画布的宽高,即等比压缩后的图片大小
        $dst_img_w=(int)$src_img_w/$scale;
        $dst_img_h=(int)$src_img_h/$scale;
        //创建画布资源
        $dst_img=imagecreatetruecolor($dst_img_w,$dst_img_h);
        //设置画布的背景透明
        $color=imagecolorallocate($dst_img,255,255,255);
        $color=imagecolortransparent($dst_img,$color);
        imagefill($dst_img,0,0,$color);

        //调用函数执行图像压缩操作
        imagecopyresampled($dst_img,$src_img,0,0,0,0,$dst_img_w,$dst_img_h,$src_img_w,$src_img_h);

        //输出图像
        $b=$this->output_func[$input_file_mime]($dst_img,$output_file);
        //销毁图像资源
        imagedestroy($src_img);
        imagedestroy($dst_img);
        //返回值
       if($b)
       {
           return true;
       }else
       {
           return false;
       }
    }

}