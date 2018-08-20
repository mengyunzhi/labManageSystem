<?php


namespace app\index\controller;

use app\common\model\Classroom;
use app\common\model\College;
use app\common\model\Course;
use app\common\model\Grade;
use app\common\model\Klass;
use app\common\model\Major;
use app\common\model\Teacher;
use app\common\model\TeacherKlass;
use app\common\model\Timeclassroom;
use think\Controller;
use think\exception\HttpException;
use think\facade\Request;

/*
 * 老师选课页面和个人信息页面的功能
 *
 * */

class TeacherController extends Controller
{

    private $timeclassroom;

    public function __construct()
    {
        parent::__construct();
        $this->timeclassroom = Timeclassroom::where('semester', '=', '2018/01');
        $this->timeclassroom = $this->timeclassroom->where('weekly', '=', 1);
        $this->timeclassroom = $this->timeclassroom->where('classroom_num', '=', 1);

    }

    //index页面
    public function index()
    {

        //初始化设置
        $onWeekly = 1;
        $onClassroom = 1;
        //获得登录老师及其信息
        $Teacher = Teacher::get(2);
        $Courses = Course::select();
        $Klasses = Klass::select();


        $postData = Request::instance()->post();
        //查询条件
        if (!empty($postData)) {
            $this->timeclassroom = Timeclassroom::where('semester', '=', '2018/01');
            $this->timeclassroom = $this->timeclassroom->where('weekly', '=', (int)$postData['weekly']);

            $onWeekly = (int)$postData['weekly'];
            $this->timeclassroom = $this->timeclassroom->where('classroom_num', '=', (int)$postData['classroom_num']);
            $onClassroom = (int)$postData['classroom_num'];
        }
        $weekList = $this->editTimeClassroom();

        $this->assign('weekList', $weekList);
        $allClassroom = Classroom::select();
        $this->assign('allClassroom', $allClassroom);

        //像v层传送老师数据
        $this->assign('Klasses', $Klasses);
        $this->assign('Courses', $Courses);
        $this->assign('Teacher', $Teacher);
        $this->assign('onWeekly', $onWeekly);
        $this->assign('onClassroom', $onClassroom);

        return $this->fetch();
    }


    public function editTimeClassroom()
    {
        $weekList = array();
        for ($i = 1; $i <= 5; $i++) {
            $nodeList = array();//节数组
            //划定每节范围
            $temp = clone $this->timeclassroom;
            $temp = $temp->where('node', '=', $i);
            $weeklyList = $temp->select();
            foreach ($weeklyList as $weekly) {
                $nodeList[$weekly['week']] = $weekly;
            }
            ksort($nodeList);
            array_push($weekList, $nodeList);
        }
        return $weekList;
    }


    //进入老师的信息页面
    public function information()
    {


        //测试信息
        $teacherId = 1;
        $Teacher = Teacher::get(1);

//        测试环境直接得到id
//        $teacherId = Request::instance()->post('id/d');

        //找出和老师有关的课程信息
        $map['teacher_id'] = $teacherId;

        $courses = $Teacher->teacherCourse()->where($map)->select();
            //得到课程和班级的信息
        $klasses = Klass::all();

        $colleges = College::all();
        $majors = Major::all();
        $grades = Grade::all();

        $tklassIds = $Teacher->teacherKlass()->where($map)->select();

        $tklass = array();
        $i=0;
        foreach ($tklassIds as $klassId)
        {
            $id = $klassId->getData('klass_id');
            if (is_null($tklass[$i++] = Klass::get($id)))
            {
                return $this->error('找的班级不存在');
            }
        }
        //防止传过来的数据为空
        if (is_null($tklass))
        {
            $tklass = 0;
        }

        $tmajorsIds = $Teacher->teacherMajor()->where($map)->select();
        //防止数据为空
        $tmajors = array();
        $i = 0;
        foreach ($tmajorsIds as $majorId)
        {

            $id = $majorId->getData('major_id');
            $tmajors[$i++] = Major::get($id);
        }
        if (is_null($tmajors))
        {
            $tmajors = 0;
        }

        $tcolleges = array();
        $i = 0;
        $tcollegesIds = $Teacher->teacherCollege()->where($map)->select();
        foreach ($tcollegesIds as $collegeId)
        {
            $id = $collegeId->getData('college_id');
            $tcolleges[$i++] = College::get($id);
        }
        if (is_null($tcolleges))
        {
            $tcolleges = 0;
        }

        $tgrades = array();
        $tgradesIds = $Teacher->teacherGrade()->where($map)->select();
        $i = 0;
        foreach ($tgradesIds as $gradeId )
        {
            $id = $gradeId->getData('grade_id');
            $tgrades[$i++] = Grade::get($id);
        }
        if (is_null($tgrades))
        {
            $tgrades = 0;
        }
        //把信息传递给V层
        $this->assign('tklass',$tklass);
        $this->assign('grades',$grades);
        $this->assign('colleges',$colleges);
        $this->assign('majors',$majors);
        $this->assign('klasses', $klasses);
        $this->assign('courses', $courses);
        $this->assign('Teacher', $Teacher);
        $this->assign('tmajors',$tmajors);
        $this->assign('tcolleges',$tcolleges);
        $this->assign('tgrades', $tgrades);
        //取回打包的数据
        return $this->fetch('information');

    }

    //保存个人信息
    public function saveInformation()
    {

        $id = Request::instance()->post('id/d');

        //判断Id存不存在
        if (is_null($id) || $id === 0) {
            throw new \Exception('未获取到id信息', 1);
        }

        $Teacher = Teacher::get($id);

        //判断对象是否存在
        if (null === $Teacher) {
            return $this->error('未找到id为' . $id . '的对象');
        }


        $Teacher = Teacher::get($id);

        //判断对象是否存在
        if (null === $Teacher) {
            return $this->error('未找到id为' . $id . '的对象');
        }


        //存储姓名
        $Teacher->name = Request::instance()->post('name');
        if (is_null($Teacher->save())) {
            return $this->error('姓名更新失败' . $Teacher->getError());
        }


        //成功返回提示
        return $this->success('更新成功', url('index'));

    }

    //抢课功能
    public function takeLesson()
    {

        //接收数据
        $teacherId = Request::instance()->post('teacherId/d');
        $timeClassroomId = Request::instance()->post('timeClassroomId/d');
        $courseId = Request::instance()->post('courseId/d');
        $klassIds = (array)Request::instance()->post('KlassIds');

        if (($teacherId === 0 && $timeClassroomId === 0 && is_null($klassIds) && $courseId === 0)) {
            throw new \Exception('id有误', 1);
        }

        //得到timeClassroom对象
        $TimeClassroom = TimeClassroom::get($timeClassroomId);

        if (is_null($TimeClassroom)) {
            throw new \Exception('不存在处于这个时间段的这个教室', 1);
        }

        //存数据
        $TimeClassroom->teacher_id = $teacherId;
        $TimeClassroom->course_id = $courseId;


        //判断添加的关联是否重复
        foreach ($klassIds as $id) {
            $Klass = Klass::get($id);
            if (!$TimeClassroom->getKlassesIsChecked($Klass)) {

                $TimeClassroom->klasses()->save($id);

            }
        }

        $TimeClassroom->save();

        //成功返回提示
        return $this->success('恭喜，抢课成功', 'index');

    }

    //换课功能
    public function changeLesson()
    {
        //接收要换的课
        $id = Request::instance()->post('id');
        $ChangeLesson = TimeClassroom::get($id);
        $ChangeKlass = $ChangeLesson->getKlasses();
        $count = count($ChangeKlass);
        //接收要换到哪
        $week = Request::instance()->post('week');
        $node = Request::instance()->post('node');
        $classroom_num = Request::instance()->post('classroom_num');
        $weekly = Request::instance()->post('weekly');
        //通过查询，找到目标教室时间
        $target = Timeclassroom::where([
            ['weekly', '=', $weekly],
            ['week', '=', $week],
            ['node', '=', $node],
            ['classroom_num', '=', $classroom_num]
        ])->select();
        $targetid = $target[0]['id'];
        $TargetLesson = TimeClassroom::get($targetid);
        //判断是否是同一教室时间
        if ($id == $targetid) {
            return $this->error('换课失败', 'index');
        }
        //新建中间变量,用于交换
        $Trans = new TimeClassroom();
        //交换教师
        $Trans->teacher_id = $ChangeLesson->teacher_id;
        $ChangeLesson->teacher_id = $TargetLesson->teacher_id;
        $TargetLesson->teacher_id = $Trans->teacher_id;
        //交换课程
        $Trans->course_id = $ChangeLesson->course_id;
        $ChangeLesson->course_id = $TargetLesson->course_id;
        $TargetLesson->course_id = $Trans->course_id;
        //交换班级
        $TargetKlass = $TargetLesson->getKlasses();
        $t = $TargetKlass[0]['timeclassroom_id'];
        //var_dump($TargetKlass);
        //return;
        for ($i = 0; $i < count($TargetKlass); $i++) {
            $TargetKlass[$i]['timeclassroom_id'] = $ChangeKlass[0]['timeclassroom_id'];
            $TargetKlass[$i]->save();
        }
        for ($i = 0; $i < $count; $i++) {
            $ChangeKlass[$i]['timeclassroom_id'] = $t;
            $ChangeKlass[$i]->save();
        }
        $ChangeLesson->save();
        $TargetLesson->save();
        return $this->success('换课成功', 'index');
    }

    //老师增加课程的方法
    public function addCourse()
    {
        //得到一个新Course对象
        $NewCourse = new Course();
        //保存数据
        $NewCourse->name = Request::instance()->param('newCourseName');
        $NewCourse->teacher_id = Request::instance()->param('teacherId');
        $NewCourse->save();

        //成功返回结果
        return $this->success('课程增加成功', url('index'));
    }

    //老师删除课程
    public function deleteCourse()
    {
        try {
            //实例化请求
            $Request = Request::instance();

            //获取id数据
           $ids = $Request->post('ids');

            //删除对象
            foreach ($ids as $id)
            {
                if (!Course::destroy($id)) {
                    return $this->error('删除失败');
                }
            }

        //获取到tp内置异常是，直接向上抛出
        }catch (HttpException $exception){
            throw $exception;
            //获取到正常的异常时输出异常
        }catch (\Exception $exception){
            return $exception->getMessage();
        }

        //成功进行跳转
        return $this->success('删除成功', url('index'));
    }

    //老师增加班级
    public function addKlass()
    {

    }

    //老师删除班级
    public function deleteKlass()
    {
        try {
            //实例化请求
            $Request = Request::instance();

            //获取id数据
            $klassIds = $Request->post('ids');

            $teacherId = $Request->post('teacherId/d');


            //删除对象
            foreach ($klassIds as $id)
            {
                $map['klass_id'] = $id;
                $map['teacher_id'] = $teacherId;
               if (!TeacherKlass::destroy($id)) {
                   return $this->error('删除失败');
               }
            }
            //获取到tp内置异常是，直接向上抛出
        }catch (HttpException $exception){
            throw $exception;
            //获取到正常的异常时输出异常
        }catch (\Exception $exception){
            return $exception->getMessage();
        }

        //成功进行跳转
        return $this->success('删除成功', url('index'));
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
      $targetid = Timeclassroom::findtarget($weekly,$week,$node,$classroom_num);

      //判断是否是同一教室时间
      if ($id == $targetid) {
        return $this->error('换课失败，目标课不能为同一节课','index');
      }

      //实例化目标课对象
      $TargetLesson = TimeClassroom::get($targetid);

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