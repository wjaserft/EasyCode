<?php
/*
 * 使用PDO扩展操作mysql数据库,以数组方式传入数据库配置
 * 实现iDAO接口
 * Author:Hao
 * date:2017-4-5
 * */
namespace  easycode\dao;
use easycode\dao\iDAO;
use \PDO;
use \PDOException;
final class DAOPDO implements iDAO
{
   private static $instance;   //静态属性，用于存储类的实例化对象
   private $pdo;

    /*
     * 构造函数，用于初始化操作
     * @param array $config  数据库配置信息
     * */
    private function __construct($config)
    {
        $host=isset($config['host'])?$config['host']:'';
        if($host=='')
        {
          die('参数不完整！');
        }
        $user_name=isset($config['user_name'])?$config['user_name']:'';
        if($user_name=='')
        {
          die('参数不完整！');
        }
        $password=isset($config['password'])?$config['password']:'';
        if($password=='')
        {
          die('参数不完整！');
        }
        $db_name=isset($config['db_name'])?$config['db_name']:'';
        if($db_name=='')
        {
          die('参数不完整！');
        }
        $dsn="mysql:host=$host;dbname=$db_name;port=3306;charset=utf8";
        $this->pdo=new PDO($dsn,$user_name,$password);
    }

    /*
     * 阻止克隆
     * */
    private function __clone()
    {
    }

    /*
     * 获取DAOPDO类的实例化对象
     * */
    public function getInstance($config)
    {
        if(!DAOPDO::$instance instanceof self)
        {
            DAOPDO::$instance=new self($config);
        }
        return DAOPDO::$instance;
    }

    /*
     * 获取一条记录
     * @param string $sql  sql语句
     * @return array 返回结果信息
     * */
    public function fetchRow($sql)
    {
        $pdo_statement=$this->pdo->query($sql);
        if(!$pdo_statement)
        {
            echo "操作数据库失败！<br/>";
            echo '错误的SQL语句是：'.$sql.'<br/>';
            $error=$this->pdo->errorInfo();
            echo '错误的信息是：'.$error[2];
            exit;
        }
        $res=$pdo_statement->fetch(PDO::FETCH_ASSOC);  //获取一条记录，以关联数组的形式返回数据
        if(!$res)
        {
            echo "操作数据库失败！<br/>";
            echo '错误的SQL语句是：'.$sql.'<br/>';
            $error=$pdo_statement->errorInfo();
            echo '错误的信息是：'.$error[2];
            exit;
        }
        return $res;
    }

    /*
     * 获取多条数据记录
     * @param string $sql  sql语句
     * @return array 返回结果信息
     * */
    public function fetchAll($sql)
    {
        $pdo_statement=$this->pdo->query($sql);
        if(!$pdo_statement)
        {
            echo "操作数据库失败！<br/>";
            echo '错误的SQL语句是：'.$sql.'<br/>';
            $error=$this->pdo->errorInfo();
            echo '错误的信息是：'.$error[2];
            exit;
        }
        $res=$pdo_statement->fetchAll(PDO::FETCH_ASSOC);  //获取多条记录，以关联数组的形式返回数据
        if(!$res)
        {
            echo "操作数据库失败！<br/>";
            echo '错误的SQL语句是：'.$sql.'<br/>';
            $error=$pdo_statement->errorInfo();
            echo '错误的信息是：'.$error[2];
            exit;
        }
        return $res;
    }

    /*
     * 获取一条记录中的一列
     * @param string $sql
     * @return array 返回结果信息
     * */
    public function fetchColumn($sql)
    {
        $pdo_statement=$this->pdo->query($sql);
        if(!$pdo_statement)
        {
            echo "操作数据库失败！<br/>";
            echo '错误的SQL语句是：'.$sql.'<br/>';
            $error=$this->pdo->errorInfo();
            echo '错误的信息是：'.$error[2];
            exit;
        }
        $res=$pdo_statement->fetchColumn();  //获取一条记录中的一列数据
        if(!$res)
        {
            echo "操作数据库失败！<br/>";
            echo '错误的SQL语句是：'.$sql.'<br/>';
            $error=$pdo_statement->errorInfo();
            echo '错误的信息是：'.$error[2];
            exit;
        }
        return $res;
    }

    /*
     * 执行DML操作
     * @param string $sql  sql语句
     * @return boolean  执行成功返回true
     * */
    public function exec($sql)
    {
        $res=$this->pdo->exec($sql);
        if($res===false)
        {
            echo "操作数据库失败！<br/>";
            echo '错误的SQL语句是：'.$sql.'<br/>';
            $error=$this->pdo->errorInfo();
            echo '错误的信息是：'.$error[2];
            exit;
        }else
        {
            return true;
        }
    }

    /*使用斜线对特殊字符进行转义*/

    /*
     * 实现斜线对特殊字符进行转义
     * @param string $data  待转义的字符串
     * @return string  返回转义后的字符串
     * */
    public function quote($data)
    {
        return $this->pdo->quote($data);
    }

    /*
     * 获取上一次插入记录的主键id
     * @return int
     * */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
