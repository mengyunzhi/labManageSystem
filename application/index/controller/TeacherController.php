<?php

/**
 * Created by PhpStorm.
 * User: ASUS-PC
 * Date: 2018/8/9
 * Time: 9:27
 */

namespace app\index\controller;
use think\Request;
use app\common\model\Time;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Teacher;
use think\Controller;
use think\Exception;
use think\exception\HttpResponseException;
use think\facade\Request;

/*
 * 老师选课页面和个人信息页面的功能
 *
 *
 * */

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

    //进入老师的信息页面
    public function information()
    {

        $Teacher = new Teacher();
        $Teacher->id = 1;


        //得到课程和班级的信息
        $klasses = Klass::all();
        $courses = Course::all();

        //把信息传递给V层
        $this->assign('klasses',$klasses);
        $this->assign('courses',$courses);

        //向v层传数据
        $this->assign('Teacher', $Teacher);

        //取回打包的数据
        return $this->fetch('information');

    }

    //保存个人信息
    public function saveInformation()
    {
        try{
            $id = Request::instance()->post('id');

            //判断Id存不存在
            if (is_null($id) || $id === 0)
            {
                throw new \Exception('未获取到id信息',1);
            }

            $Teacher = Teacher::get($id);

            //判断对象是否存在
            if (null === $Teacher)
            {
                return $this->error('未找到id为'. $id .'的对象');
            }

            $Teacher->name = Request::instance()->post('name');

           //存储
            $courses_ids = Request::instance()->post('course_id');
            $klass_ids = Request::instance()->post('klass_id');
            var_dump($klass_ids);
            $Teacher->courses()->saveAll($courses_ids);
            $Teacher->klasses()->saveAll($klass_ids);



            //获取到正常的异常，输出异常
        }catch (\Exception $exception)
        {
            return $exception->getMessage();
        }catch (HttpResponseException $exception)
        {
            throw  $exception;
        }


    }

}