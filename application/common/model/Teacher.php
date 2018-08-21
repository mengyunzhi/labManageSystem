<?php

/*
 * 教师的模型
 * */

namespace app\common\model;


use think\Model;

class Teacher extends Model
{


    /*
     * 获取是否存在老师和课程的相关关联记录
     * */
    public function getCoursesIsChecked(Course &$Course)
    {
        //取id
        $teacherId = $this->id;
        $courseId = $Course->id;

        //定制查询条件
        $map = array();
        $map['teacher_id'] = $teacherId;
        $map['course_id'] = $courseId;

        //从关联表中取信息
        $TeacherCourse = TeacherCourse::get($map);

        //判断是否存在
        if (is_null($TeacherCourse)) {
            return false;
        } else {
            return true;
        }
    }


    //对klass进行多对多关联
    public function klasses()
    {
        return $this->belongsToMany('Klass', 'teacher_klass');
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
        if (is_null($teacherKlass)) {
            return false;
        } else {
            return true;
        }
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
    public function teacherCourse()
    {
        return $this->hasMany('Course');
    }

    //teache和college的多对多关联
    public function colleges()
    {
        return $this->belongsToMany('College', 'teacher_college');
    }

    //teacher和grade进行多对多关联
    public function grades()
    {
        return $this->belongsToMany('Grade', 'teacher_grade');
    }

    //对teacher和major进行多对多关联
    public function majors()
    {
        return $this->belongsToMany('Major', 'teacher_major');
    }

    public function teacherCollege()
    {
        return $this->hasMany('TeacherCollege');
    }

    public function teacherMajor()
    {
        return $this->hasMany('TeacherMajor');
    }

    public function teacherGrade()
    {
        return $this->hasMany('TeacherGrade');
    }

}
