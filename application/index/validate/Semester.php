<?php
namespace app\index\validate;
use think\Validate;

/**
 * 学期的验证类
 */
class Semester extends Validate
{
	protected $rule = [
        'name'  =>  'require|token',
        'starttaketime'  =>  'require',
        'endtaketime'  =>  'require',
        'totalweek'  =>  'number|between:1,30',
        'begintime'  =>  'require',
        'startweekorder'  =>  'require|number',
        'endweekorder'  =>  'require|number',
    ];
}


