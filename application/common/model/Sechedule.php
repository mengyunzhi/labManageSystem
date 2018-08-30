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

    public function teacher()
    {
        return $this->belongsTo('Teacher');
    }

    public function course()
    {
        return $this->belongsTo('Course');
    }

    /**
     *行程与教室一对多关联
     */

    public function classroom()
    {
        return $this->belongsTo('Classroom');
    }

    //判断中间表中是否存在该关联
    public function getKlassesIsChecked(Klass &$Klass)
    {

        //取id
        $secheduleId = $this->id;
        $klassId = $Klass->id;

        //定制查询条件
        $map = array();
        $map['klass_id'] = $klassId;
        $map['sechedule_id'] = $secheduleId;

        //从关联表中取信息
        $SecheduleKlass = SecheduleKlass::get($map);

        //判断是否存在
        if (is_null($SecheduleKlass)) {
            return false;
        } else {
            return true;
        }

    }

    //传入周次，星期，节次，教室,查询在sechedule表里对应的对象并返回该对象的id
    public static function findtarget($weekorder, $week, $node, $classroom_id,$semesterid)
    {
        $target = Sechedule::where([
            ['weekorder', '=', $weekorder],
            ['week', '=', $week],
            ['node', '=', $node],
            ['classroom_id', '=', $classroom_id],
            ['semester_id', '=', $semesterid],
        ])->find();
        return $target->id;
    }

    //找到sechedule_klass表里所有sechedule_id为sechedule表里的id的对象
    public function getKlasses()
    {
        return SecheduleKlass::where('sechedule_id', '=', $this->id)->select();
    }

    //传入交换课和目标课的id，交换两课的教师，课程，班级
    public static function exchange($id, $targetid) //id为交换课的id，target为目标课的id

    {
        $ApplySechedule = Sechedule::get($id); //通过id，找到sechedule表里对应的对象
        $ApplyKlass = $ApplySechedule->getKlasses(); //找到sechedule_klass表里所有sechedule_id为id的对象，存到数组$Change中
        $TargetSechedule = Sechedule::get($targetid); //通过targetid，找到sechedule表里对应的对象
        //新建中间变量,用于交换教师和课程
        $Trans = new Sechedule();
        //交换教师
        $Trans->teacher_id = $ApplySechedule->teacher_id;
        $ApplySechedule->teacher_id = $TargetSechedule->teacher_id;
        $TargetSechedule->teacher_id = $Trans->teacher_id;
        //交换课程
        $Trans->course_id = $ApplySechedule->course_id;
        $ApplySechedule->course_id = $TargetSechedule->course_id;
        $TargetSechedule->course_id = $Trans->course_id;
        //交换班级
        $ApplyKlass = $ApplySechedule->getKlasses();
        $TargetKlass = $TargetSechedule->getKlasses();
        $t = $TargetKlass[0]['sechedule_id']; //t为交换的中间变量
        for ($i = 0; $i < count($TargetKlass); $i++) {
            $TargetKlass[$i]['sechedule_id'] = $ApplyKlass[0]['sechedule_id'];
            $TargetKlass[$i]->save();
        }
        for ($i = 0; $i < count($ApplyKlass); $i++) {
            $ApplyKlass[$i]['sechedule_id'] = $t;
            $ApplyKlass[$i]->save();
        }
        //保存
        $ApplySechedule->save();
        $TargetSechedule->save();
    }

    //传入交换课和空目标课的id，交换两课的教师，课程，班级
    public static function exchangenull($applyid, $targetid) //id为交换课的id，target为目标课的id
    {
        $ApplySechedule = Sechedule::get($applyid); //通过id，找到sechedule表里对应的对象
        $ApplyKlassIds = $ApplySechedule->getKlasses()->column('klass_id'); //找到sechedule_klass表里所有sechedule_id为id的对象，存到数组$Change中
        $TargetSechedule = Sechedule::get($targetid); //通过targetid，找到sechedule表里对应的对象

        $TargetSechedule->teacher_id = $ApplySechedule->teacher_id;
        $TargetSechedule->course_id = $ApplySechedule->course_id;

        //判断添加的关联是否重复
        foreach ($ApplyKlassIds as $id) 
        {
            //添加新的班级时刻中间表
            $Klass = Klass::get($id);
            if (!$TargetSechedule->getKlassesIsChecked($Klass)) 
            {
                $TargetSechedule->klasses()->save($id);
            }


        }
        //删除已有的班级时刻中间表               
        $ApplyKlasses = SecheduleKlass::where('Sechedule_id','=',$applyid)->delete();
        $ApplySechedule->teacher_id = null;
        $ApplySechedule->course_id = null;

        $TargetSechedule->save();
        $ApplySechedule->save();


    }
   /*
    * 找到相同时间的其他教室
    * */

    public function findTheSameTimeSechedule(Sechedule &$Sechedule)
    {
        //定制查询条件
        $map = array();
        $map['weekorder'] = $Sechedule->weekorder;
        $map['node'] = $Sechedule->node;
        $map['week'] = $Sechedule->getData("week");
        $map['semester_id'] = $Sechedule->semester_id;

        //找到相同时间的教室
        $sechedules = $Sechedule->where($map)->select();

        return $sechedules;
    }

    /*
     * 判断老师和班级是否在这个教室
     * */
    public function isExist(Sechedule &$Sechedule, $teacherId, $klassIds)
    {
        //定制查询当前教室的班级的条件
        $map['sechedule_id'] = $Sechedule->id;
        $flag = true;
        //找到当前教室的班级
        $currentSechedules = array();
        $currentSechedules = SecheduleKlass::where($map)->select();


        //判断教师在不在
        if (!($Sechedule->teacher_id == $teacherId)) {

            //判断空值
            if (empty($currentSechedules)) {

                return false;
            }

            //判断学生在不在
            foreach ($currentSechedules as $currentSechedule) {

                foreach ($klassIds as $klassId) {

                    if ($currentSechedule->klass_id == $klassId) {

                        $flag = false;
                    }

                    //判断标记
                    if (!$flag)

                    return true;
                }
            }
        } else {

            return true;
        }

        //如果前面没有得出结果
        return false;


    }

     /*换课时判断是否时间冲突*/
    public function isChangeExist($allSameSechedule,$applyTeacherId,$applyKlassIds,$TargetSechedule)
    {
        //如果教师时间冲突，则返回1
        if ($TargetSechedule->isChangeTeacherExist($allSameSechedule,$applyTeacherId,$TargetSechedule)) {
            return 1;
        }
        //如果教师时间不冲突，则判断班级是否冲突   
        else
        {
            //如果冲突的班级为空，即班级不冲突，则返回2
            if (empty($TargetSechedule->isChangeKlassExist($allSameSechedule,$applyKlassIds,$TargetSechedule))) {
                return 2;
            }
            //如果冲突的班级不为空，则返回冲突的班级ID
            else{
                $result = $TargetSechedule->isChangeKlassExist($allSameSechedule,$applyKlassIds,$TargetSechedule);
                return $result;
            }
        }

    }

    /*换课时判断老师是否时间冲突*/
    public function isChangeTeacherExist($allSameSechedule,$applyTeacherId,$TargetSechedule)
    {
        //判断老师是否在目标时间的所有教室有没有课
        foreach ($allSameSechedule as $SameSechedule) {
            if ($SameSechedule->teacher_id === $applyTeacherId) {
                $teacherSameSechedules[] = $SameSechedule;
            }
        }

        //如果没有，则申请教师换到目标课后不会引起时间冲突
        if (empty($teacherSameSechedules)) {
            return false;
        }
        //如果有，判断有课的教室是不是有且仅有目标教室,如果是，则教师要换的是自己的课，并且不会引起时间冲突
        else if($teacherSameSechedules[0]->classroom_id === $TargetSechedule->classroom_id and count($teacherSameSechedules) === 1){
            return false;
        }
        //除此以外，教师换到目标后会引起时间冲突
        else {
            return true;
        }
    }

    /*换课时判断班级是否时间冲突*/
    public function isChangeKlassExist($allSameSechedule,$applyKlassIds,$TargetSechedule)
    {
        $sameKlassIds = array();
        //判断班级是否在目标时间所有其他教室有课
        foreach ($allSameSechedule as $SameSechedule) 
        {
            if ($SameSechedule->id != $TargetSechedule->id) {
                //在相同时间情况下，寻找此教室里的所有有课的班级集合         
                if (!empty($SameSechedule->getKlasses())) {
                    $klass_ids = $SameSechedule->getKlasses()->column('klass_id');

                    //比较有课的班级集合与申请换课的班级集合，返回相同的班级的ID
                    $sameKlassId = array_intersect($klass_ids,$applyKlassIds);

                    //如果相同的班级不为空，则申请换课的班级集合里至少有一个班时间冲突，将冲突的班级存到$sameKlassIds数组中
                    if (!empty($sameKlassId)) {                    
                         $sameKlassIds = $sameKlassId;
                    }
                }      
            }                
        }

        //移除$sameKlassIds中重复的值,将时间冲突的班级id返回
        $result = array_unique($sameKlassIds);
        return $result;
    }

    //获取器，转义week字段
    public function getWeekAttr($value)
    {
        $week = [1 => '一', 2 => '二', 3 => '三',4 => '四',5 => '五',6 =>'六',7 =>'日'];
        return $week[$value];
    }
}

