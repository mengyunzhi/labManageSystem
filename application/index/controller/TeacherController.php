<?php

namespace app\index\controller;
use think\Controller;
/**
 */
class TeacherController extends Controller
{
	//展示抢课界面
	public function index(){
		return $this->fetch();
	}
	//展示编辑个人信息界面
	public function edit(){
		return $this->fetch();
	}
}