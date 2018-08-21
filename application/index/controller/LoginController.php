<?php

namespace app\index\controller;
use app\common\model\User;
use think\Controller;
use think\facade\Request;

/**
*登录类 包含学生注册 老师注册 登录验证 登录界面
*/
class LoginController extends Controller
{
	/**
	*登录的界面 可以让学生注册
	*/
	public function index()
	{
		return $this->fetch('index');
	}
	/**
	*对用户登录做出处理
	*/
	public function login()
	{
		$postData=Request::instance()->post();
		$user=User::get(['username'=>$postData['username']]);
		if (is_null($user)) {
			return $this->error("用户不存在");
		}
		if (!$user->login($postData['password'])) {
			return $this->error("密码错误");
		}
		return $this->role($user);
	}
	/**
	*根据用户的角色跳转到课表页面
	*@param User $user 用户
	*/
	public function role($user)
	{	
		$roleName=$user->role->name;
		session('userId',$user->getData('id'));
		if ($roleName=="老师") {
			return $this->success("教师登录成功",url('Teacher/index'));
		}else if($roleName=="学生"){
			return $this->success("学生登录成功",url('Student/index'));
		}else if($roleName=="管理员"){
			return $this->success("管理员登录成功",url("Administrator/index"));
		}else{
			return $this->error("身份验证失败，登录失败");
		}
	}
	/**
	*老师注册界面
	*/
	public function register58ea6b4a87950961320699af09b251744f7a2198()
	{
		return $this->fetch();
	}
}