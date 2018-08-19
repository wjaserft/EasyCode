<?php
/* 说明：分页类  
 * 版本：V1.0
 * 使用说明：
 * 1、实例化对象时，需要传入参数，详见构造函数；
 * 2、调用setPageNow()可以设置当前页；
 * 3、调用getPageData()方法可以获取每页对应的数据，调用之前需要page_now参数已被设置（不需要重复设置）。
 * 4、调用getPageNav()方法可以获取页码导航字符串，调用之前需要设置page_now参数；
 * 作者：Hao
 */
namespace easycode\tools;
class Page
{
    private $page_now=1;    //当前页
    private $page_size=10;   //每页显示的条数
    private $mysqliTools_instance;   //MysqliTools类的实例化对象
    private $table_name;    //要操作的数据库表名
    private $url;     //点击页码，跳转的地址
    private $page_count;  //总页数
    private $row_count;   //总记录数
    
    
    /*构造函数，用于初始化操作*/
    public function __construct($page_size,$mysqliTools_instance,$table_name,$url)
    {
        //获取page_size的值
        if(isset($page_size))
        {
            if(is_numeric($page_size))
            {
                $this->page_size=$page_size;
            }else 
            {
                echo "构造函数初始化参数错误";
                exit;
            }
        }else 
        {
            echo "构造函数初始化参数错误";
            exit;
        }
        
        //获取MysqliTools类的实例化
        if(isset($mysqliTools_instance))
        {
            if(is_object($mysqliTools_instance))
            {
                $this->mysqliTools_instance=$mysqliTools_instance;
            }else 
            {
                echo "构造函数初始化参数错误";
                exit;
            }  
        }else 
        {
            echo "构造函数初始化参数错误";
            exit;
        }    
        
        //获取要操作的表的名称
        if(isset($table_name) && $table_name!='')
        {
            $this->table_name=$table_name;
        }else 
        {
            echo "构造函数初始化参数错误";
            exit;
        }
        
        //获取url
        if(!empty($url))
        {
            $this->url=$url.'?page_now=';
        }else
        {
            echo "构造函数初始化参数错误";
            exit;
        }
        
        //查询数据库，获取总记录数
        $sql="select count(*) as sum from `$this->table_name`";
        $res=$mysqliTools_instance->execute_dql($sql);
        $this->row_count=$res[0]['sum'];
        
        //计算总页数
        $this->page_count=ceil($this->row_count/$this->page_size);
    }
    
    /*获取page_now的值*/
    public function getPageNow()
    {
        return $this->page_now; 
    }
    
    /*设置page_now的值*/
    public function setPageNow($value)
    {
        if(!empty($value))
        {
            if(!is_numeric($value))
            {
                echo "page_now参数必须是数字";
                exit;
            }
            //对page_now的合法性进行校验
            if($value<1)
            {
                $value=1;
            }
            if($value>$this->page_count)
            {
                $value=$this->page_count;
            }
           
            $this->page_now=$value;
        }
    }
    
    
    /*获取页码导航,调用之前需要设置成员属性page_now的值
     * return string  返回一个页码导航字符串
     * */
    public function getPageNav()
    {   
        //拼接页码导航——左侧
        $pre_num=$this->page_now-1;
        $next_num=$this->page_now+1;
        $page_str=<<<HTML
        <ul class="pagination">
            <li><a href="{$this->url}1">首页</a></li>   
            <li><a href="{$this->url}{$pre_num}">上一页</a></li>
HTML;
        
        //拼接页码导航——中部数字导航（当前页前面显示4页，后面显示5页）
        for($i=$this->page_now-4;$i<=$this->page_now+6;$i++)
        {
            if($i<1 || $i>$this->page_count)
            {
                continue;
            }
            $active=($this->page_now==$i)?'active':'';
            $page_str.=<<<HTML
            <li class="{$active}"><a href="{$this->url}{$i}">$i</a></li>
HTML;
        }
        
        //拼接页码导航——右侧
        $page_str.=<<<HTML
             <li><a href="{$this->url}{$next_num}">下一页</a></li>
             <li><a href="{$this->url}{$this->page_count}">末页</a></li>
        </ul>
HTML;
            return $page_str;
        }
    
    /*获取分页数据，调用之前需要设置成员属性page_now的值。
     * @param array  需要显示的字段名称，如果没有传递参数，则默认为全部显示
     * return array  返回一个二维数组
     * */
    public function getPageData(array $fields=array())
    {
        //计算起始值
        $start=($this->page_now-1)*$this->page_size;
        $fields_str='';
        //判断用户是否传递参数
        if(empty($fields))
        {
            $fields_str="*";   //获取全部字段的信息
        }else 
        {
            $fields_str=implode(',',$fields);
        }
        //拼接sql语句
        $sql="select $fields_str from $this->table_name limit $start,$this->page_size";
        $res=$this->mysqliTools_instance->execute_dql($sql);
        return $res;
    }    
}  