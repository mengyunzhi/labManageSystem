<?php

//年级的模型
namespace app\common\model;
use think\Model;

class Grade extends Model
{
    public function major()
    {
        return $this->belongsTo('Major');
    }

}