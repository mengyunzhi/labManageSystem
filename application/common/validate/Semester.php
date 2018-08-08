<?php
namespace app\common\validate;
use think\Validate;     // 内置验证类

class Semester extends Validate
{
    protected $rule = [       
        'name'  => 'require|length:2,25',
   
    ];
}