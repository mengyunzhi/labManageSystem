<?php

/*
 * 教师的模型
 * */

namespace app\common\model;


use think\Model;

class Teacher extends Model
{

    //对klass进行多对多关联
    public function klasses()
    {
        return $this->belongsToMany('Klass','teacher_klass');
    }

    /*
     * 获取是否存在老师和班级的相关关联记录
     * */

    public function getKlassesIsChecked(Klass &$Klass)
    {
        //取ID
        $teacherId = (int)$this->id;
        $klassId = (int)$Klass->id;

        //定制查询条件
        $map = array();
        $map['klass_id'] = $klassId;
        $map['teacher_id'] = $teacherId;

        //从关联表取信息
        $teacherKlass = TeacherKlass::get($map);

        //判断是否存在
        if (is_null($teacherKlass)){
            return false;
        }else{
            return true;
        }
    }


    //对time进行多对多关联
    public function times()
    {
        return $this->belongsToMany('Time','teacher_time');
    }

    /*
     * 老师与老师班级中间表的一对多关联
     * */
    public function teacherKlass()
    {
        return $this->hasMany('TeacherKlass');
    }

    /*
     * 老师与课程中间表的一对多关联
     * */
    public function course()
    {
        return $this->hasMany('Course');
    }
    /**
    *返回与老师有关的行程
    *@param array $sechedules 行程数组
    *@return Sechedule
    *@return null 
    */
    public function getSelfSechedule($sechedules)
    {
        foreach ($sechedules as $key => $sechedule) {
            if ($sechedule->teacher_id==$this->id) {
                return $sechedule;
            }
        }
        return null;
    }

    //teacher和major的关联
    public function teacherMajor()
    {
        return $this->hasMany('TeacherMajor');
    }

    //teahcer和grade的关联
    public function teacherGrade()
    {
        return $this->hasMany('TeacherGrade');
    }

    //teacher和College的关联
    public function teacherCollege()
    {
        return $this->hasMany('TeacherCollege');
    }
}
