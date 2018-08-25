<?php
namespace app\common\model;
use think\Model;


/**
 * 课程
 */
class Course extends Model
{
	//一对多课程与老师关联
	public function Teacher()
    {
        return $this->belongsTo('Teacher');
    }
}