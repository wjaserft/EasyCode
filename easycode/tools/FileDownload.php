<?php
/*文件下载类
 * Atuhor:Hao
 * */
namespace easycode\tools;
class FileDownload
{
    /*下载文件*/
    public function download($file_path)
    {
        if(!file_exists($file_path))
        {
            die('文件不存在！');
        }
        $file_name=basename($file_path);  //获取目录中的文件名
        $fp=fopen($file_path,"rb");
        $file_size=filesize($file_path);   //获取文件的大小
        header("content-type:application/octet-stream"); //发送http响应，以文件流的形式接收数据
        header("Accept-Ranges:bytes");    //以字节的形式传输文件
        header("Accept-length:".$file_size);   //文件的大小
        header("Content-Disposition:attachment; filename=".$file_name); //文件的名称
        $buffer=1024;
        $buffer_count=0;   //计数器
        while(!feof($fp) && $buffer_count<=$file_size)
        {
            $data=fread($fp,$buffer);  //读取文件
            $buffer_count+=$buffer;
            echo $data;   //输出数据，开始下载
        }
        fclose($fp);  //关闭文件指针
    }
}