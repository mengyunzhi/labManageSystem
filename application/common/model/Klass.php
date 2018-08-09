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

class Klass extends Model
{
    private $TeachersId;

    //返回与教室关联的教师id数组
    public function getTeacherId(){
    	$this->TeachersId=array();
    	//从中间表取得关联信息
    	$Instance=new TeacherClassroom;
    	$TeacherClassroomes=$Instance->where('classroom_id', 'like', '%' . $this->id .'%')->select();
    	//获得关联教师
    	foreach ($TeacherClassroomes as $TeacherClassroom) {
    		$TeacherId=$TeacherClassroom['teacher_id'];
    		array_push($this->TeachersId, $TeacherId);
    	}
    	return $this->TeachersId;
    }
}