<?php
namespace app\index\controller;
use app\common\model\Classroom;
use app\common\model\Semester;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Teacher;
use app\common\model\Sechedule;
use think\Controller;
use think\facade\Request;

/**
 * 老师选课页面和个人信息页面的功能 
 */

class TeacherController extends Controller
{
    private $sechedule;/*行程范围 @param where查询后返回值*/

    private $currentSemester;/*当前查询学期 默认为本学期 @param Semester*/
    
    private $currentWeekorder;/*当前查询周次 默认本周次 @param int*/
    
    private $currentClassroom;/*当前查询教室 @param Classroom*/
    
    private $teacher;/*登录的教师 @param Teacher*/
    
    /**
    *构造函数 初始化查询条件
    */
    public function __construct(){
        parent::__construct();
        $this->currentSemester=Semester::currentSemester(Semester::select());
        $this->currentWeekorder=$this->currentSemester->getWeekorder();
        $this->currentClassroom=Classroom::get(1);
        $this->teacher=Teacher::get(2);
        $this->setRange($this->currentSemester->id,$this->currentWeekorder);
    }
    /*
    *显示教师课表
    */
    public function index()
    { 
        $postData=Request::instance()->post();
        if (!empty($postData)) {
          $this->setRange((int)$postData['semester_id'],(int)$postData['weekorder']);
        }
        $secheduleList=$this->editIndexSechedule();
        //像v层传送老师数据
        $this->assign([
          'secheduleList'=>$secheduleList,
          'Klasses'=>Klass::select(),
          'currentSemester'=>Semester::currentSemester(Semester::select()),
          'allSemester'=>Semester::select(),
          'currentClassroom'=>$this->currentClassroom,
          'currentSemester'=>$this->currentSemester,
          'currentWeekorder'=>$this->currentWeekorder,
          'allClassroom'=>Classroom::select(),
          'null'=>null,
        ]);
        return $this->fetch();
    }

    /**
    *根据查询条件设置范围
    *@param int $semesterId 查询的学期id
    *@param int $weekorder 查询的周次
    *@param int $classroomId 查询的教室id
    */
    public function setRange($semesterId,$weekorder,$classroomId=null)
    {
      $this->currentSemester=Semester::get($semesterId);
      $this->currentWeekorder=$weekorder;
      $this->currentClassroom=Classroom::get($classroomId);
      $this->sechedule=Sechedule::where('semester_id','=',$semesterId)->where('weekorder','=',$weekorder);
      if ($classroomId!==null) {
        $this->sechedule->where('classroom_id','=',$classroomId);
      }
    }
    /**
    *编辑首页课表行程格式
    *@return array
    */
    public function editIndexSechedule()
    {
      $secheduleList=array();
        for($i=1;$i<=5;$i++){
            $nodeList=array();
            $temp1=clone $this->sechedule;
            $temp1=$temp1->where('node','=',$i);
            for ($j=1; $j<=7 ;$j++) {
                $temp2=clone $temp1; 
                $temp2=$temp2->where('week','=',$j)->select();       
                $nodeList[$j]=$this->teacher->getSelfSechedule($temp2);
            }
            ksort($nodeList);
            array_push($secheduleList, $nodeList);
        }
        return $secheduleList;
    }
    /**
    *获取行程 编辑抢课行程格式
    *@return array
    */
    public function editSechedule(){
        $weekList=array();
        for($i=1;$i<=5;$i++){
            $nodeList=array();//节数组
            //划定每节范围
            $temp=clone $this->sechedule;
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
    /**
    *显示换课抢课界面
    */
    public function takelessonInterface()
    {
      // 判断是否为选课系统开放时间
      $time=time();
      if($time>=$this->currentSemester->getData('starttaketime')&&$time<=$this->currentSemester->getData('endtaketime')){
          $this->setRange($this->currentSemester->id,$this->currentWeekorder,1);
          $this->currentSemester=Semester::getOpenSemester(Semester::select());
          $postData=Request::instance()->post();
          if (!empty($postData)) {
              $this->setRange($this->currentSemester->id,(int)$postData['weekorder'],(int)$postData['classroom_id']);
          }
          $secheduleList=$this->editSechedule();
          $this->assign([
              'currentSemester'=>$this->currentSemester,
              'currentWeekorder'=>$this->currentWeekorder,
              'startweekorder'=>$this->currentSemester->startweekorder,
              'endweekorder'=>$this->currentSemester->endweekorder,
              'currentClassroom'=>$this->currentClassroom,
              'allClassroom'=>Classroom::select(),
              'Klasses'=>Klass::select(),
              'teacher'=>$this->teacher,
              'null'=>null,
              'secheduleList'=>$secheduleList,
          ]);
          return $this->fetch('takelessonInterface');
      }else{
          return $this->error("未到开放的时间");
      }
      
    }

    //进入老师的信息页面
    public function information()
    {

        //得到老师信息，由于无法扫码登录，暂时以这个代替
        $Teacher = Teacher::get(1);

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

            //接收数据
            $teacherId = Request::instance()->post('teacherId/d');
            $timeClassroomId = Request::instance()->post('timeClassroomId/d');
            $courseId = Request::instance()->post('courseId/d');
            $klassIds = (array)Request::instance()->post('KlassIds');

           
            if (($teacherId === 0 && $timeClassroomId === 0 && is_null($klassIds) && $courseId === 0))
            {
                throw new \Exception('id有误',1);
            }

            //得到timeClassroom对象
            $Sechedule = Sechedule::get($timeClassroomId);

            if (is_null($Sechedule))
            {
                throw new \Exception('不存在处于这个时间段的这个教室',1);
            }

            //存数据
            $Sechedule->teacher_id = $teacherId;
            $Sechedule->course_id = $courseId;


        //判断添加的关联是否重复
            foreach ($klassIds as $id)
            {
                $Klass = Klass::get($id);
                if (!$Sechedule->getKlassesIsChecked($Klass))
                {

                    $Sechedule->klasses()->save($id);
              
                 }
            }

            $Sechedule->save();



        //成功返回提示
        return $this->success('恭喜，抢课成功','index');

    }

    //换课功能
    public function changeLesson()
    {
      //接收要换课的id
      $id = Request::instance()->post('id');
      $ChangeLesson = TimeClassroom::get($id);//通过id，找到timeclassroom表里对应的对象
      //通过周次，星期，节次，教室找到目标课的id
      $weekly = Request::instance()->post('weekly');
      $week = Request::instance()->post('week');
      $node = Request::instance()->post('node');
      $classroom_num = Request::instance()->post('classroom_num');     
      $targetid = Sechedule::findtarget($weekly,$week,$node,$classroom_num);

      //判断是否是同一教室时间
      if ($id == $targetid) {
        return $this->error('换课失败，目标课不能为同一节课','index');
      }

      //实例化目标课对象
      $TargetLesson = Sechedule::get($targetid);


      //判断目标教室时间是否有课，如果没课或者为同一老师的·课，直接调换
      if ($TargetLesson->teacher_id == 0 or $TargetLesson->teacher_id == $ChangeLesson->teacher_id) 
      {
        Timeclassroom::exchange($id,$targetid);
        return $this->success('换课成功','index');
      }
      
      //向目标课程的教师发送消息，取得同意后再向管理员发送请求，通过后进行交换(此功能待完善)
      else {
        return '向目标课程的教师发送消息，取得同意后再向管理员发送请求，通过后进行交换(此功能待完善)';
      }
    }

}