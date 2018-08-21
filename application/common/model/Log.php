<?php
/**
 * 换课日志
 */

namespace app\common\model;

use think\Model;

class Log extends Model
{
    //获取器，转义state字段
    public function getStateAttr($value)
    {
        $state = [0 => '等待目标教师答复', 1 => '目标教师已同意，等待管理员审核中', 2 => '目标教师已拒绝', 3 => '管理员已同意', 4 => '管理员已拒绝'];
        return $state[$value];
    }
}
