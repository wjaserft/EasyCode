<?php
/*
 * 为数据库连接对象类（DAO）定义接口
 * Author:Hao
 * date:2017-4-5
 * */
namespace framework\dao;
interface iDAO
{
    /*获取一条记录*/
    public function fetchRow($sql);

    /*获取多条记录*/
    public function fetchAll($sql);

    /*获取记录中的一列*/
    public function fetchColumn($sql);

    /*执行DML操作*/
    public function exec($sql);

    /*使用斜线对特殊字符进行转义*/
    public function quote($data);

    /*获取上一次插入记录的主键id*/
    public function lastInsertId();
}