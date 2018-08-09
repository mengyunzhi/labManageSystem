<?php
/**
 * Created by PhpStorm.
 * User: ASUS-PC
 * Date: 2018/8/7
 * Time: 9:51
 */

namespace app\common\model;
use think\Model;
use app\common\model\TeacherClassroom;
use app\common\model\Teacher;

class Klass extends Model
{
    private $Teachers;

    //返回与教室关联的教师的数组
    public function getTeacher(){
    	$this->Teachers=array();
    	//从中间表取得关联信息
    	$Instance=new TeacherClassroom;
    	$TeacherClassroomes=$Instance->where('classroom_id', 'like', '%' . $this->id .'%')->select();
    	//获得关联教师
    	foreach ($TeacherClassroomes as $TeacherClassroom) {
    		$Teacher=Teacher::get($TeacherClassroom['teacher_id']);
    		array_push($this->Teachers, $Teacher);
    	}
    	return $this->Teachers;
    }
}