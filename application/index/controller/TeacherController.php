<?php


namespace app\index\controller;
use app\common\model\Classroom;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Teacher;
use app\common\model\Timeclassroom;
use think\Controller;
use think\facade\Request;

/*
 * 老师选课页面和个人信息页面的功能
 *
 * */

class TeacherController extends Controller
{

    private $timeclassroom;
    public function __construct(){
        parent::__construct();
        $this->timeclassroom=Timeclassroom::where('semester','=','2018/01');
        $this->timeclassroom=$this->timeclassroom->where('weekly','=',1);
        $this->timeclassroom=$this->timeclassroom->where('classroom_num','=',1);
    }
    //index页面
    public function index()
    {
        $postData=Request::instance()->post();
        //查询条件
        if (!empty($postData)) {
                $this->timeclassroom=Timeclassroom::where('semester','=','2018/01');
                $this->timeclassroom=$this->timeclassroom->where('weekly','=',(int)$postData['weekly']);
                $this->timeclassroom=$this->timeclassroom->where('classroom_num','=',(int)$postData['classroom_num']);
        }
        $weekList=$this->editTimeClassroom();

        $this->assign('weekList',$weekList);
        $allClassroom=Classroom::select();
        $this->assign('allClassroom',$allClassroom);
        return $this->fetch();

    }

    public function editTimeClassroom(){
        $weekList=array();
        for($i=1;$i<=5;$i++){
            $nodeList=array();//节数组
            //划定每节范围
            $temp=clone $this->timeclassroom;
            $temp=$temp->where('node','=',$i);
            $weeklyList=$temp->select();
            foreach($weeklyList as $weekly){
            $nodeList[$weekly['week']]=$weekly;
            }
            ksort($nodeList);
            array_push($weekList, $nodeList);
        }
        return $weekList;
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

               $id = Request::instance()->post('id/d');

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


               //存储姓名
               $Teacher->name = Request::instance()->post('name');
               if (is_null($Teacher->save()))
               {
                   return $this->error('姓名更新失败' . $Teacher->getError());
               }



               //删除原有信息
               $map = ['teacher_id'=>$id];
               //执行删除操作，由于可能存在删除0条记录，故使用flase来进行判断
               if (false === $Teacher->teacherCourse()->where($map)->delete())
               {
                   return $this->error('删除老师课程关联信息发生错误' . $Teacher->TeacherCourse()->getError());
               }
               $coursesIds = Request::instance()->post('course_id/a');

               //对老师班级关联信息执行以上操作
               if (!is_null($coursesIds)){
                   if (!$Teacher->courses()->saveAll($coursesIds)){
                       return $this->error('老师课程信息保存错误',$Teacher->courses()->getError());
                   }
               }

               if (false === $Teacher->teacherKlass()->where($map)->delete())
               {
                   return $this->error('删除老师班级关联信息失败' . $Teacher->teacherKlass()->getError());
               }

               //增加数据
               $klassIds = Request::instance()->post('klass_id/a');

               if (!is_null($klassIds))
               {
                   if (!$Teacher->klasses()->saveAll($klassIds))
                   {
                       return $this->error('老师班级信息保存错误' . $Teacher->klasses()->getError());
                   }
               }

               //成功返回提示
               return $this->success('更新成功',url('index'));

               //获取到正常的异常，输出异常

    }

    //抢课功能
    public function takeLesson()
    {
        
    }

}