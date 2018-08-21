<?php

namespace app\common\model;
use think\Model;
/**
*用户表 判断用户信息
*/
class User extends Model
{
	/**
	*判断用户密码是否正确
	*@param string $password 密码
	*@return boolean 
	*/
	public  function login($password)
	{
		if (User::encryptPassword($password)==$this->password) {
			return true;
		}else{
			return false;
		}
	}
	/**
	*密码加密
	*@param string $password 密码
	*@return string 加密后密码
	*/
	public static function encryptPassword($password)
	{
		return sha1(md5($password).'mengyunzhi');
	}
	/**
	*与角色表一对一
	*/
	public function role()
	{
		return $this->belongsTo('Role'); 
	}
}