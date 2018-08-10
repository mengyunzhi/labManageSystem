<?php


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