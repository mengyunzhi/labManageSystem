<?php


/*
 * 学生的模型
 * */
namespace app\common\model;
use think\Model;

class Student extends Model
{
	public function Klass()
    {
        return $this->belongsTo('Klass');
    }
    /**
	*返回学生有关的行程
	*@param array $allSechedule 有关的行程
	*@return Sechedule
	*@return null
    */
    public function getSelfSechedule($allSechedule)
    {	
    	foreach ($allSechedule as $key => $sechedule) {
    		$map=['sechedule_id'=>$sechedule->id,'klass_id'=>$this->klass_id];
    		if (!is_null(SecheduleKlass::get($map))) {
    			return $sechedule;
    		}
    	}
    	return null;
    }
}