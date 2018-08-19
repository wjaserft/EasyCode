<?php
/*
 * 模型类基类
 * */
namespace easycode\core;
use easycode\dao\DAOPDO;
class Model
{
    protected $daopdo;
    protected $true_table;   //用于存储表名
    protected $pk_name;   //主键名称

    /*构造函数，用于初始化操作*/
    public function __construct()
    {
        $this->initDAOPDO();   //实例化daopdo对象
        $this->initTrueTable();   //获取待操作的表名
        $this->initFieldName();   //获取主键字段名称

    }

    /*初始化daopdo对象*/
    protected function initDAOPDO()
    {
        $db_config=array(
            'host'  =>   $GLOBALS['config']['host'],
            'user_name'  =>  $GLOBALS['config']['user_name'],
            'password'  => $GLOBALS['config']['password'],
            'db_name'   =>  $GLOBALS['config']['db_name']
        );
        $this->daopdo=DAOPDO::getInstance($db_config);
    }

    /*获取要操作的数据表名*/
    protected function initTrueTable()
    {
        //此处logic_table的值来自于其子类定义的属性，因为子类继承父类时会自动继承并调用父类的构造函数，调用时父类在构造函数中便可获取子类的logic_table属性。
        $this->true_table='`'.$GLOBALS['config']['table_prefix'].$this->logic_table.'`';
    }

    /*获取主键名称*/
    protected function initFieldName()
    {
        $sql="desc ".$this->true_table;
        $res=$this->daopdo->fetchAll($sql);
        foreach($res as $k=>$v)
        {
            if($v['Key']=='PRI')
            {
                $this->pk_name='`'.$v['Field'].'`';
                break;
            }
        }
    }

    /*数据插入操作
    *@param array $data  需要插入数据库的数据键值对，键值表示字段名，值表示对应的数据
     * insert into `goods`(字段列表) values(数据列表)
    */
    protected function insert($data)
    {
        if(!empty($data))
        {
            $fields=array_keys($data);   //获取数组的所有键值，并以数组形式保存
            $fields=array_map(function($arr){
                return "`".$arr."`";
            },$fields);     //将字段列表用反引号包裹起来
            $fields=implode(',',$fields);   //拼接字段列表
            $values=array_values($data);  //获取数组的所有值，并以数组形式保存
            $values=array_map(function($arr){
                $arr=addslashes($arr);  //转义特殊字符
                return "'".$arr."'";
            },$values);   //将数组中的每个值用单引号包裹起来
            $values=implode(",",$values);   //将数据拼接为字符串
            $sql='insert into '.$this->true_table.'('.$fields.') values('.$values.')';
            $this->daopdo->exec($sql);
            return $this->daopdo->lastInsertId();
        }
    }


    /*数据删除操作
     *@param  int $id    待删除记录的主键值，通常为整型
     * delete from 表名 where 条件
    */
    protected function delete($id)
    {
        if(!empty($id))
        {
            $sql='delete from '.$this->true_table.' where '.$this->pk_name.'='."'".$id."'";
            return $this->daopdo->exec($sql);
        }
    }

    /*数据修改操作
     * @param array $data 需要更新的数据，为键值对格式，键名是字段名称，值是需要更新的值
     * @param array $where 更新数据的条件。键值对格式，键名是字段名称。
     * update 表名 set 字段名=值，字段名2=值2 where 条件
    */
    protected function update($data,$where=null)
    {
        if(!empty($where))
        {
            $fields=array_keys($data);   //获取所有字段
            $fields=array_map(function($arr){
                return '`'.$arr.'`';
            },$fields);   //给所有字段名称添加反引号包裹
            $values=array_values($data);   //获取所有的值
            $values=array_map(function($arr){
                return "'".$arr."'";
            },$values);    //给所有的值添加单引号包裹
            $str='';
            for($i=0;$i<count($fields);$i++)
            {
                $str.=$fields[$i].'='.$values[$i].',';
            }
            $str=rtrim($str,',');   //删除最右边的逗号
            $str2='';
            foreach($where as $k=>$v)
            {
                $str2.="`".$k."`".'='."'".$v."'".',';
            }
            $str2=rtrim($str2,',');  //删除最右边的逗号
            //拼接sql语句
            $sql='update '.$this->true_table.' set '.$str.'where '.$str2;
           return $this->daopdo->exec($sql);
        }
    }

    /*数据查询操作
     *@param array $fields  需要查询的字段，索引数组形式
     * @param array $where   查询的条件，键值对格式，仅支持等于条件符
     * select 字段列表 from 表名 where 条件
    */
    protected function find($where=array(),$fields=array())
    {
        if(!empty($fields))
        {
            $fields_str='';
            foreach($fields as $v)
            {
                $fields_str.="`".$v."`".',';   //拼接字段列表
            }
            $fields_str=rtrim($fields_str,',');   //删除最右边的逗号
        }else
        {
            $fields_str='*';
        }
        if(!empty($where))
        {
            $where_str="";
            //如果只有一个条件
            if(count($where)==1)
            {
                foreach($where as $k=>$v)
                {
                    $where_str="`".$k."`".'='."'".$v."'";
                }
                $where_str=' where '.$where_str;
            }else  //多个条件
            {
                foreach($where as $k=>$v)
                {
                    $where_str.="`".$k."`".'='."'".$v."' "."and ";  //拼接条件列表
                }
                $where_str=rtrim($where_str,'and ');
                $where_str=' where '.$where_str;
            }
        }else
        {
            $where_str='';
        }
        //拼接sql语句
        $sql='select '.$fields_str.' from '.$this->true_table.$where_str;
        $res=$this->daopdo->fetchAll($sql);
        return $res;
    }
}