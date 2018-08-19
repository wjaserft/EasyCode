<?php 
/*
 * 说明：文件上传类
 * 版本: V1.0
 * 使用方法：
 *          1、实例化对象时，需传递配置参数（数组形式）,数组的格式如$config
 *          2、调用uploadFile()方法即可实现上传，可实现多个文件上传。上传成功后，会返回文件的保存地址，失败后会返回false。
 *          多个文件上传时，会返回一个包含上传成功文件地址的数组，如果全部未上传成功，则返回一个空数组。
 *          3、文件上传失败，可以通过获取$error成员属性的值，查看错误信息。
 * 作者：Hao
 */

namespace easycode\tools;
use \Finfo;
class Upload
{
    //配置参数
    private $config = array(
        'form_field_name'=>'',     //form表单上传控件的名称
        'upload_path'=>'',   //文件上传路径
        'allow_max_file_size'=>0,   //允许的最大文件大小，单位字节
        'allow_file_suffix'=>array(),   //文件名后缀
        'allow_file_mime'=>array(),   //允许的文件mime类型 
        'new_file_prefix'=>''       //随机文件名前缀
    );
    
    //错误信息
    public $error = array(
        'errorno'=>'0',
        'error'=>'文件上传成功！',  
    );
    
    /*构造函数，初始化文件上传参数*/
    public function __construct($config)
    {
        //设置文件上传参数
        $this->setConfig($config);    
     
    }
    
    /*文件上传，程序会自动判断是单个文件，还是多个文件上传*/
    public function uploadFile()
    {   
        //判断文件是否被上传
        if(empty($_FILES))
        {
            $this->setError(11);   //设置错误信息（没有文件上传）
            return false;
        }        
        if(empty($_FILES[$this->config['form_field_name']]['name']))
        {
            $this->setError(11);   //设置错误信息（没有文件上传）
            return false;
        }
        
        $finfo=new Finfo(FILEINFO_MIME_TYPE);    //实例化PHP中的finfo类，用于获取上传文件的MIME类型
        //获取上传文件信息
        $origin_file_name=$_FILES[$this->config['form_field_name']]['name'];   //源文件名
        $tmp_file_path=$_FILES[$this->config['form_field_name']]['tmp_name'];  //临时文件全路径
        $file_size=$_FILES[$this->config['form_field_name']]['size'];    //文件大小
        $upload_error=$_FILES[$this->config['form_field_name']]['error'];   //获取上传错误编号
        
        if(is_array($origin_file_name))   //如果是多个文件上传
        {
            $res1=array();
            for($i=0;$i<count($origin_file_name);$i++)
            {
                if(empty($origin_file_name[$i]))
                {
                    continue;
                }
                $origin_file_mime=$finfo->file($tmp_file_path[$i]);  //获取上传文件的真实MIME类型
                //调用方法上传文件               
                $res1[$i]=$this->uploadFileOne($origin_file_name[$i],$tmp_file_path[$i],$origin_file_mime,$file_size[$i],$upload_error[$i]);
                if(!$res1[$i])
                {
                    unset($res1[$i]);   
                    return $res1;  //未上传或部分上传成功
                }
            }
            return $res1;   //全部上传成功
        }else  //如果是单个文件上传
        {
            $origin_file_mime=$finfo->file($tmp_file_path);  //获取上传文件的真实MIME类型
            $res2=$this->uploadFileOne($origin_file_name,$tmp_file_path,$origin_file_mime,$file_size,$upload_error);
            return $res2;
        }            
    }
 
    /*单个文件上传*/
    private  function uploadFileOne($origin_file_name,$tmp_file_path,$origin_file_mime,$file_size,$upload_error)
    {  
        //判断文件是否是通过HTTP POST上传
        if(!is_uploaded_file($tmp_file_path))
        {
            $this->setError(11);   //设置错误信息（没有文件上传）
            return false;
        }
        //检查文件上传过程中是否存在错误
        if($upload_error!=0)
        {
            $this->setError($upload_error);   //设置错误信息
            return false;
        }
        
        //判断待上传文件是否符合规范
        $b=$this->checkUploadFile($origin_file_name,$origin_file_mime,$file_size);
        if(!$b)
        {
            return false;
        }
        //创建以日期命名的目录
        date_default_timezone_set('PRC');
        $date_str=date('Ymd');
        $directory=$this->config['upload_path'].'/'.$date_str.'/';
        if(!is_dir($directory))
        {
            if(!@mkdir($directory,0755,true))
            {
                $this->setError(11);   //设置错误信息(创建目录失败)
                return false;
            }
        }
        //产生唯一随机文件名
        $new_file_name=$this->createUniqueName();
        $suffix=strrchr($origin_file_name,'.');   //截取文件名后缀
        $new_file_name=$new_file_name.$suffix;
        $new_file_path=$directory.$new_file_name;
        
        //移动临时文件至目标目录
        if(!move_uploaded_file($tmp_file_path,$new_file_path))
        {
            $this->setError(12);   //设置错误信息(移动临时文件失败)
            return false; 
        }
        //返回文件的路径
        return $date_str.'/'.$new_file_name;  
    }
    
    /*检查上传文件是否符合规范*/
    private  function checkUploadFile($origin_file_name,$origin_file_mime,$file_size)
    {        
        //判断上传文件的后缀是否符合规范
        $suffix=strrchr($origin_file_name,'.');   //截取文件名后缀
        if(!$suffix)
        {
            $this->setError(8);   //设置错误信息(不支持该文件后缀)
            return false;
        }
        if(!in_array($suffix,$this->config['allow_file_suffix']))
        {
            $this->setError(8);   //设置错误信息(不支持该文件后缀)
            return false;
        }
        //判断上传文件的MIME类型是否符合规范
        if(!in_array($origin_file_mime,$this->config['allow_file_mime']))
        {
            $this->setError(9);   //设置错误信息(不支持该文件类型)
            return false;
        }
        //判断上传文件的大小是否符合规范
        if($file_size>$this->config['allow_max_file_size'])
        {
            $this->setError(10);   //设置错误信息(文件大小超出限制)
            return false;
        }
        return true;
    }
    
    /*产生唯一的随机文件名*/
    private function createUniqueName()
    {
        $new_file_name=uniqid($this->config['new_file_prefix'].'_',true);  //调用函数产生唯一id
        return $new_file_name;
    }
    
    /*设置文件上传参数 */
    private function setConfig($config)
    {
        //判断参数的个数是否符合规范
        if(count($this->config)!=count($config))
        {
            $this->setError(5);   //设置错误信息(设置初始化参数错误)
           return false;
        }
        //判断各个参数是否被设置
        $this->config['upload_path']=isset($config['upload_path'])?$config['upload_path']:"";
        $this->config['allow_file_suffix']=isset($config['allow_file_suffix'])?$config['allow_file_suffix']:"";
        $this->config['allow_file_mime']=isset($config['allow_file_mime'])?$config['allow_file_mime']:"";
        $this->config['form_field_name']=isset($config['form_field_name'])?$config['form_field_name']:"";
        $this->config['allow_max_file_size']=isset($config['allow_max_file_size'])?$config['allow_max_file_size']:"";
        $this->config['new_file_prefix']=isset($config['new_file_prefix'])?$config['new_file_prefix']:"";
        if($this->config['upload_path']=='' || $this->config['allow_file_suffix']=='' || $this->config['allow_file_mime']=='' || $this->config['form_field_name']=='' || $this->config['allow_max_file_size']=='' || $this->config['new_file_prefix']=='')
        {
            $this->setError(5);   //设置错误信息(设置初始化参数错误)
             return false;
        }
        //判断文件后缀及文件mime参数是否是数组形式
        if(!is_array($this->config['allow_file_suffix']) || !is_array($this->config['allow_file_mime']))
        {
            $this->setError(5);   //设置错误信息(设置初始化参数错误)
            return false;
        }
        return true;
    }
    
    /* 设置错误信息，将错误信息存储至$error成员属性
     * 当用户调用成员方法返回false时，可通过$error成员属性获取对应的错误提示信息
     * @param int $errorno
     * */
    private function setError($errorno)
    {
        switch($errorno)
        {
            case 1:
                $this->error['error']='上传的文件的大小超过了php.ini中upload_max_filesize选项限制的值';
                break;
            case 2:
                $this->error['error']='上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';     
                break;
            case 3:   
                $this->error['error']='文件只有部分被上传';     
                break;
            case 4:       
                $this->error['error']='没有文件被上传';    
                break;
            case 5:              
                $this->error['error']='设置初始化参数错误';    //设置初始化参数时出错    
                break;
            case 6:              
                $this->error['error']='找不到临时文件';   
                break;         
            case 7:
                $this->error['error']='文件写入失败';    
                break;
            case 8:
                $this->error['error']='不支持该文件后缀';
                break;
            case 9:
                $this->error['error']='不支持该文件类型';
                break;
            case 10:
                $this->error['error']='文件大小超出限制';
                break;
            case 11:
                $this->error['error']='没有文件上传';  
                break;
            case 11:
                $this->error['error']='创建目录失败';  
                break;
            case 12:
                $this->error['error']='移动临时文件失败';
                break;
            default:
                $this->error['error']='未知错误';
                break;
        }
        $this->error['errorno']=$errorno;    //将错误编号赋值给成员属性
    }
}