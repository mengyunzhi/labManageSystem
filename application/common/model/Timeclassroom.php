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


    public function teacher(){
    	return $this->belongsTo('Teacher');
    }
    public function course(){
    	return $this->belongsTo('Course');
    }	
}