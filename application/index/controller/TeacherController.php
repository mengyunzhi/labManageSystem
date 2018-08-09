<?php

namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Time;
use app\common\model\Klass;
use app\common\model\Teacher;
/**
 */
class TeacherController extends Controller
{
	private $Klass;//界面中查询条件的教室
	private $Times;//界面中查询条件的时间

	private $TeachersId;//查询条件的教师范围

	//构造函数 
	public function __construct(){
		parent::__construct();
		//默认选中第一个教室
		$this->Klass=Klass::get(1);
		$this->Times=Time::where('weekly','=', 1);
	}

	//展示抢课界面
	public function index(){
		$instance=new Request();
		$postData=$instance->post();
		//获得条件查询
		if(!empty($postData)){
			$KlassId=(int)$postData["Klass"];
			if(!$KlassId==0){
			$this->Klass=Klass::get($KlassId);
			}
			$WeeklyNum=(int)$postData["Weekly"];
			if (!$WeeklyNum==0) {
				$this->Times=Time::where('weekly','=', $WeeklyNum);
			}
		}
		
		//获得所选教室的关联教师
		$this->TeachersId=$this->Klass->getTeacherId();
	
		//获得时间关联数组
		$this->editTimeAndTeacher();

		$this->assign("ChooseKlass",$this->Klass);
		$this->assign("Times",$this->Times);
		
		$AllKlass=Klass::select();
		$this->assign('AllKlass',$AllKlass);
		return $this->fetch();
	}

	//获得与时间关联的教师、
	//返回数组
	public function editTimeAndTeacher(){
		var_dump($this->TeachersId);
		echo "<br/>";
		//二维数组 第一维节次 第二维星期
		$TimeAndTeacher=array();
		for($i=1;$i<=5;$i++){
			$Temp1=clone $this->Times; 
			//按节次缩小范围
			$Scope=$Temp1->where('node','=',$i);
			for($n=1;$n<=7;$n++){
				$Temp2=clone $Scope;
				$Time=$Temp2->where('week','=',$n)->select();
				$TeachersId=$Time[0]->getTeacherId();
				var_dump($TeachersId);
				echo "<br/>";
				foreach($TeachersId as $TeacherId){
					$key=array_search($TeacherId, $this->TeachersId);
					var_dump($key);
					echo "<br/>";
					print_r(Teacher::get($key));
					echo "<br/>";
				}
				return ;
			}
			
		}
	}
}