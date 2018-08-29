<?php
namespace app\common\model;

use think\Model;

// 使用model
/**
 * 换课申请表
 */
class Message extends Model
{
    //根据applyshechedule_id获取sechedule对象    changelesson
    public function getApply()
    {
        return Sechedule::get($this->apply_sechedule_id);
    }

    //根据targetsechedule_id获取sechedule对象
    public function getTarget()
    {
        return Sechedule::get($this->target_sechedule_id);
    }

    //获取申请teacher对象
    public function getApplyTeacher()
    {
        return Teacher::get($this->apply_teacher_id);
    }

    //获取目标teacher对象
    public function getTargetTeacher()
    {
        return Teacher::get($this->target_teacher_id);
    }

    //获取申请course对象
    public function getApplyCourse()
    {
        return Course::get($this->apply_course_id);
    }
    
    //获取目标course对象
    public function getTargetCourse()
    {
        return Course::get($this->target_course_id);
    }

    //获取器，转义status字段
    public function getIsAgreeStatusAttr($value)
    {
        $isAgreeStatus = [0 => '等待审核', 1 => '对方已同意，等待系统审核中', 2 => '对方已拒绝',3 => '换课审核通过，换课成功',4 => '换课审核未通过，换课失败'];
        return $isAgreeStatus[$value];
    }
}

