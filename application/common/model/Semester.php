<?php
namespace app\common\model;
use think\Model;			//引用model
class Semester extends Model//继承model
{
    /**
    *学期表中开学时间的获取器
    *@param int $value 开学的时间戳
    *@return string
    */
    public function getBegintimeAttr($value)
    {
    	return date("Y年m月d日 ",$value);
    }
    /**
    *学期表中学期结束时间的获取器
    *@param int $value 结束时间的时间戳
    *@return string
    */
    public function getClosetimeAttr($value)
    {
    	return date("Y年m月d日",$value);
    }
    /**
    *学期表中开始抢课时间的获取器
    *@param int $value 开始抢课的时间戳
    *@return string
    */
    public function getstarttaketimeAttr($value)
    {
        return date("Y年m月d日",$value);
    }
    /**
    *学期表中结束抢课时间的获取器
    *@param int $value 结束抢课的时间戳
    *@return string
    */
    public function getendtaketimeAttr($value)
    {
        return date("Y年m月d日",$value);
    }
    /**
    *将时间戳转换为Y-m-d的格式
    *@param int $date 要转化的时间戳
    *@return string
    */
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
    				return $semester;
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
    /**
    *返回当前开放抢课的周次
    *@param Semester $allSemester 所有的学期
    *@return Semester
    */
    public static function getOpenSemester($allSemester)
    {
        foreach ($allSemester as $key => $semester) {
            if ($semester->istakesemester=="true") {
                return $semester;
            }
        }
    }
}