<?php
namespace app\common\model;
use think\Model;

/**
 * 时间教室表
 */
class Sechedule extends Model
{
    //
	public function klasses()
    {
        return $this->belongsToMany('Klass');
    }


    public function teacher(){
    	return $this->belongsTo('Teacher');
    }
    public function course(){
    	return $this->belongsTo('Course');
    }	

    //判断中间表中是否存在该关联
    public function getKlassesIsChecked(Klass &$Klass)
    {

        //取id
        $timeClassroomId = $this->id;
        $klassId = $Klass->id;

        //定制查询条件
        $map = array();
        $map['klass_id'] = $klassId;
        $map['sechedule_id'] = $timeClassroomId;

        //从关联表中取信息
        $TimeClassroomKlass = SecheduleKlass::get($map);

        //判断是否存在
        if (is_null($TimeClassroomKlass)) {
            return false;
        }else{
          return true;
        }

    }

    //传入周次，星期，节次，教室,查询在timeclassroom表里对应的对象并返回该对象的id
    public static function findtarget($weekly,$week,$node,$classroom_num)
    {
        $target = Timeclassroom::where([
          ['weekly','=',$weekly],
          ['week','=',$week],
          ['node','=',$node],
          ['classroom_num','=',$classroom_num]
      ])->select();
        return $target[0]['id'];
    }

    //找到timeclassroom_klass表里所有timeclassroom_id为id的对象
    public function getKlasses()
    {
        return TimeclassroomKlass::where('timeclassroom_id','=',$this->id)->select();
    }

    //传入交换课和目标课的id，交换两课的教师，课程，班级
    public static function exchange($id,$targetid)//id为交换课的id，target为目标课的id
    {
        $ChangeLesson = TimeClassroom::get($id);//通过id，找到timeclassroom表里对应的对象
        $ChangeKlass = $ChangeLesson ->getKlasses();//找到timeclassroom_klass表里所有timeclassroom_id为id的对象，存到数组$Change中
        $TargetLesson = TimeClassroom::get($targetid);//通过targetid，找到timeclassroom表里对应的对象
        //新建中间变量,用于交换教师和课程
        $Trans = new TimeClassroom();
        //交换教师
        $Trans ->teacher_id= $ChangeLesson ->teacher_id;
        $ChangeLesson ->teacher_id= $TargetLesson ->teacher_id;
        $TargetLesson ->teacher_id= $Trans ->teacher_id;
        //交换课程
        $Trans ->course_id= $ChangeLesson ->course_id;    
        $ChangeLesson ->course_id= $TargetLesson ->course_id;     
        $TargetLesson ->course_id= $Trans ->course_id;
        //交换班级
        $ChangeKlass = $ChangeLesson ->getKlasses();
        $TargetKlass = $TargetLesson ->getKlasses();
        $t = $TargetKlass[0]['timeclassroom_id'];//t为交换的中间变量
        for ($i=0; $i < count($TargetKlass); $i++) {        
           $TargetKlass[$i]['timeclassroom_id'] = $ChangeKlass[0]['timeclassroom_id'];
           $TargetKlass[$i] ->save();       
         }     
        for ($i=0; $i < count($ChangeKlass); $i++) {        
           $ChangeKlass[$i]['timeclassroom_id'] = $t;
           $ChangeKlass[$i] ->save();      
         }
        //保存
        $ChangeLesson ->save();
        $TargetLesson ->save();
    }
}