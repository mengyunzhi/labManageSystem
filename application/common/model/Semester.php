<?php
namespace app\common\model;
use think\Model;			//引用model
class Semester extends Model//继承model
{
    public function getBegintimeAttr($value)
    {
    	return date("Y年m月d日 ",$value);
    }
    public function getClosetimeAttr($value)
    {
    	return date("Y年m月d日",$value);
    }
    public function getstarttaketimeAttr($value)
    {
        return date("Y年m月d日",$value);
    }
    public function getendtaketimeAttr($value)
    {
        return date("Y年m月d日",$value);
    }
    public function getDate($date)
    {
        return date("Y-m-d",$date);
    }
    /**
	*获取当前学期 
 	*@param Semester $allSemester 所有的学期
	*@return Semester
    */
    public static function currentSemester($allSemester)
    {
    	$time=time();
    	foreach ($allSemester as $key => $semester) {
    		if($semester->getData('begintime')<=$time&&$semester->getData('closetime')>=$time){
    			{
    				return $semester;
    			}
    		}
    	}
    	return $allSemester[0];
    }

    /**
    *是否为当前学期
    *@return boolean
    */
    public function isCurrent()
    {
        $time=time();
        if ($this->getData('begintime')<=$time&&$this->getData('closetime')>=$time) {
            return true;
        }
        return false;
    }
    /**
    *返回当前周次
    *@return int 
    */
    public function getWeekorder()
    {
        $time=time();
        return (intval(($time-$this->getData('begintime'))/604800)+1);
    }
}