<?php

namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Time;
use app\common\model\Klass;
/**
 */
class TeacherController extends Controller
{
	public $Klass;//界面中查询条件的教室

	//构造函数 
	public function __construct(){
		parent::__construct();
		//默认选中第一个教室
		$this->Klass=Klass::get(1);
	}

	//展示抢课界面
	public function index(){
		$instance=new Request();
		$postData=$instance->post();
		//获取选择的教室
		$KlassId=(int)$postData["Klass"];
		if(!$KlassId==0){
			$this->Klass=Klass::get($KlassId);
		}
		$this->assign("ChooseKlass",$this->Klass);

		$AllKlass=Klass::select();
		$this->assign('AllKlass',$AllKlass);
		var_dump($this->Klass->getTeacher());
		return $this->fetch();
	}
	//展示编辑个人信息界面
	public function edit(){
		return $this->fetch();
	}
}