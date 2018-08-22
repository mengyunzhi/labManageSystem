<?php

//专业的模型
namespace app\common\model;
use think\Model;

class Major extends Model
{
	public function College()
    {
        return $this->belongsTo('College');
    }

}