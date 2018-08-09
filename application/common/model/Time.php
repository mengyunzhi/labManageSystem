<?php
namespace app\common\model;
use think\Model;
use app\common\model\TeacherTime;

/**
 * 时间表操作
 */
class Time extends Model
{
	private $TeachersId;

	public function getTeacherId(){
		$this->TeachersId=array();
		//从中间表取得关联信息
    	$Instance=new TeacherTime;
    	$TeacherTimes=$Instance->where('time_id', '=', $this->id)->select();
    	//获得关联教师
    	foreach ($TeacherTimes as $TeacherTime){
    		$TeacherId=$TeacherTime['teacher_id'];
    		array_push($this->TeachersId, $TeacherId);
    	}
    	return $this->TeachersId;
	}
}