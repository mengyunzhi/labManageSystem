<?php
/**
 * 换课日志
 */

namespace app\common\model;

use think\Model;

class Log extends Model
{
    //获取器，转义status字段
    public function getIsAgreeStatusAttr($value)
    {
        $isAgreeStatus = [0 => '等待审核', 1 => '对方已同意，等待系统审核中', 2 => '对方已拒绝',3 => '换课审核通过，换课成功',4 => '换课审核未通过，换课失败'];
        return $isAgreeStatus[$value];
    }
}
