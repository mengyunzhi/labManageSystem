<?php
/**
 * Created by PhpStorm.
 * User: ASUS-PC
 * Date: 2018/8/9
 * Time: 9:27
 */

/*
 * 教师的模型
 * */

namespace app\common\model;


use think\Model;

class Teacher extends Model
{

    //对course进行多对多关联
    public function courses()
    {
        return $this->belongsToMany('Course','teacher_course');
    }




    //对klass进行多对多关联
    public function klasses()
    {
        return $this->belongsToMany('Klass','teacher_klass');
    }

    //对time进行多对多关联
    public function times()
    {
        return $this->belongsToMany('Time','teacher_time');
    }


}
