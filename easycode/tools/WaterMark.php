<?php
/*
 * 给图片添加水印，支持批量添加
 * Author:Hao
 */
namespace easycode\tools;
class WaterMark
{
    private $file_type=array(
        'image/jpeg'=>'.jpg',
        'image/png'=>'.png',
        'image/gif'=>'.gif'
    );   //建立mime类型和文件后缀的映射
    public $font_file='./msyh.ttf';   //字体文件
    public $font_size=20;
    public $word_color=array(255,255,255);//水印文字的颜色（RGB值）
    public $word='版权所有';   //水印的文字内容

    /*添加水印
     * @param string   $input_image_path   待处理的图片路径，可以是文件夹。
     * @param string   $output_image_path   输出的图片路径，可以是文件夹，需要和input_image_path类型一致。
     * @param int     $wm_location_right    水印文字距图片右侧的距离，不能超过该图片的宽度。如，0 表示水印文字紧贴图片左侧
     * @param int     $wm_location_bottom    水印文字距图片底部的距离，不能超过该图片的高度。如，0 表示水印文字紧贴图片顶部
     * @param is_recursion boolean 是否递归遍历，默认为false，不进行递归遍历
     * return boolean 成功返回true，失败返回false
    */
    public function generate($input_image_path,$output_image_path,$wm_location_right,$wm_location_bottom,$is_recursion=false)
    {
        $input_image_path=iconv('utf-8','gbk',$input_image_path);
        $output_image_path=iconv('utf-8','gbk',$output_image_path);
        if(is_dir($input_image_path))    //如果传递的是一个目录
        {
            $image_addr_arr=array();  //初始化存储图片路径的数组
            $this->traversalImgFile($input_image_path,$image_addr_arr,$is_recursion);  //遍历目录
            for($i=0;$i<count($image_addr_arr);$i++)
            {
                $this->doWaterMark($output_image_path,$wm_location_right,$wm_location_bottom,$image_addr_arr[$i]);
            }
        }else  //如果是一个文件
        {
            $this->doWaterMark($output_image_path,$wm_location_right,$wm_location_bottom,$input_image_path);
        }
        return true;
    }

    /*
     * 将水印文字写入图片
     * @param string $img_addr
     * return boolean
     * */
    private function doWaterMark($output_image_path,$wm_location_right,$wm_location_bottom,$image_addr)
    {
        list($img_w,$img_h)=getimagesize($image_addr);   //获取图片的宽高信息
        $im=imagecreatefromstring(file_get_contents($image_addr));   //从字符串中的图像流创建一副图像
        $color=imagecolorallocate($im,$this->word_color[0],$this->word_color[1],$this->word_color['2']);   //为一副图像分配颜色
        imagettftext($im,$this->font_size,0,$img_w-$wm_location_right,$img_h-$wm_location_bottom,$color,$this->font_file,$this->word);  //写入文字
        //$file_name=substr($image_addr,strrpos($image_addr,'/')+1);    //截取文件名
        $file_name=basename($image_addr);  //从路径中获取文件名
        //如果输出文件名是目录，则拼接输出路径
        if(is_dir($output_image_path))
        {
            $output_file_name=$output_image_path.'/wm_'.$file_name;
        }else  //如果输出文件名是一个文件，则直接输出
        {
            $output_file_name=$output_image_path;
        }
        imagejpeg($im,$output_file_name);   //输出图像
        imagedestroy($im);
    }

    /*遍历目录下的所有图片
     *@param string $input_image_path  图片目录
     *@param array &image_addr_arr  引用传递，用于存储图片目录下的所有图片的地址
     *@param boolean $is_recursion  是否递归遍历
    */
    private function traversalImgFile($input_image_path,&$image_addr_arr,$is_recursion)
    {
        $dir_handle=opendir($input_image_path);   //打开目录句柄
        while(false!==($file_name=readdir($dir_handle)))
        {
            if($file_name !='.' && $file_name!='..')
            {
                $file_full_name=$input_image_path.'/'.$file_name;  //拼接全路径
                if(is_dir($file_full_name))
                {
                    if($is_recursion)
                    {
                        $this->traversalImgFile($file_full_name,$image_addr_arr,true);
                     }
                }else
                {
                    //获取该文件的mime类型
                    $finfo=new Finfo(FILEINFO_MIME_TYPE);
                    $file_mime=$finfo->file($file_full_name);  //获取文件的mime类型
                    $suffix=strrchr($file_name,'.');  //获取文件名后缀
                    if(!isset($this->file_type[$file_mime]))
                    {
                        die('不支持该文件格式！');
                    }
                    if($this->file_type[$file_mime]!=$suffix)
                    {
                        die('文件名后缀错误！');
                    }
                    //将该图片的路径存入数组
                    $image_addr_arr[]= $file_full_name;
                }
            }
        }
        closedir($dir_handle);
    }
}