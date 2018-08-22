<?php
namespace app\common\model;

use think\Model;

// 使用model
/**
 * 换课申请表
 */
class Changelesson extends Model
{
    //根据applyshechedule_id获取sechedule对象    changelesson
    public function getApply()
    {
        return Sechedule::where('id', '=', $this->applysechedule_id)->find();
    }

    //根据targetsechedule_id获取sechedule对象
    public function getTarget()
    {
        return Sechedule::where('id', '=', $this->targetsechedule_id)->find();
    }
    //获取器，转义state字段
    public function getStateAttr($value)
    {
        $state = [0 => '等待目标教师答复', 1 => '目标教师已同意，等待管理员审核中', 2 => '目标教师已拒绝', 3 => '管理员已同意,换课成功，请查看', 4 => '管理员已拒绝'];
        return $state[$value];
    }

    //筛选登陆的教师向他人换课的请求信息
    public static function findapply($changelessons, $id)
    {
        //新建换课请求信息数组
        $applymessages = array();

        //将申请老师为登陆老师的换课请求存放到新建数组中
        $count = count($changelessons);
        $j = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($changelessons[$i]->getApply()->teacher->id == $id) {
                $applymessages[$j++] = $changelessons[$i];
            }

        }

        //返回换课请求信息数组
        return $applymessages;
    }

    //筛选他人向登陆的教师换课的请求信息
    public static function findrequest($changelessons, $id)
    {
        //新建换课请求信息数组
        $requestmessages = array();

        //将目标老师为登陆老师的换课请求存放到新建数组中
        $count = count($changelessons);
        $j = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($changelessons[$i]->getTarget()->teacher->id == $id) {
                $requestmessages[$j++] = $changelessons[$i];
            }

        }

        //返回换课请求信息数组
        return $requestmessages;
    }

    //筛选管理员处理的请求结果
    public static function findresult($changeresults, $id)
    {
        //新建换课请求信息数组
        $resultmessages = array();

        //将目标老师为登陆老师的换课请求存放到新建数组中
        $count = count($changeresults);
        $j = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($changeresults[$i]->getTarget()->teacher->id == $id or $changeresults[$i]->getApply()->teacher->id == $id) {
                $resultmessages[$j++] = $changeresults[$i];
            }

        }

        //返回换课请求信息数组
        return $resultmessages;
    }

    //判断是否处于换课中
    public static function isChangeLesson($id)
    {
        $state = Changelesson::where('state', '<', 2)
        ->where('applysechedule_id|targetsechedule_id', '=',$id)
        ->select();
        if (empty($state[0])) {
            return false;
        } else {
            return true;
        }
    }
}
