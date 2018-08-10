<?php
/**
 * Created by PhpStorm.
 * User: ASUS-PC
 * Date: 2018/8/8
 * Time: 10:31
 */


/*
 * 学生的模型
 * */
namespace app\common\model;
use think\Model;

class Student extends Model
{
	public function Klass()
    {
        return $this->belongsTo('Klass');
    }
}