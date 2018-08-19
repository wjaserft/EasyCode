<?php
/*
 * 工厂类，传递模型类名称，返回单例模式对象
 * */

namespace easycode\core;
class Factory
{
    /*工厂模式，传递模型类名称，返回该类的单例模式对象*/
    public static function M($model_name)
    {
        if(strrpos($model_name,'\\')===false)
        {
            $model_name=MODULE.'\\model\\'.$model_name;
        }
        static $model_list=array();
        if(!isset($model_list[$model_name]))
        {
            $model_list[$model_name]=new $model_name;
        }
        return $model_list[$model_name];
    }
}