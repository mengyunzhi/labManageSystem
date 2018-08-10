<?php
namespace app\common\model;
use think\Model;

/**
 * 时间教室表
 */
class Timeclassroom extends Model
{
    //
	public function klasses()
    {
        return $this->belongsToMany('Klass');
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
        $map['timeclassroom_id'] = $timeClassroomId;

        //从关联表中取信息
        $TimeClassroomKlass = TimeclassroomKlass::get($map);

        //判断是否存在
        if (is_null($TimeClassroomKlass)) {
            return false;
        }else{
          return true;
        }

    }


	
}