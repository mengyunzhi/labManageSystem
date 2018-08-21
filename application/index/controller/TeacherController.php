<?php

namespace app\index\controller;

use app\common\model\Changelesson;
use app\common\model\Classroom;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Sechedule;
use app\common\model\Teacher;
use think\Controller;
use think\facade\Request;

/*
 * 老师选课页面和个人信息页面的功能
 *
 * */

class TeacherController extends Controller
{

    private $sechedule;
    public function __construct()
    {
        parent::__construct();
        $this->sechedule = Sechedule::where('semester', '=', '2018/01');
        $this->sechedule = $this->sechedule->where('weekly', '=', 1);
        $this->sechedule = $this->sechedule->where('classroom_num', '=', 1);

    }
    //index页面
    public function index()
    {

        //初始化设置
        $onWeekly = 1;
        $onClassroom = 1;
        //获得登录老师及其信息
        $Teacher = Teacher::get(1);
        $Courses = Course::select();
        $Klasses = Klass::select();

        $postData = Request::instance()->post();
        //查询条件
        if (!empty($postData)) {
            $this->timeclassroom = Timeclassroom::where('semester', '=', '2018/01');
            $this->timeclassroom = $this->timeclassroom->where('weekly', '=', (int) $postData['weekly']);

            $onWeekly = (int) $postData['weekly'];
            $this->timeclassroom = $this->timeclassroom->where('classroom_num', '=', (int) $postData['classroom_num']);
            $onClassroom = (int) $postData['classroom_num'];
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
            $nodeList = array(); //节数组
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

        //得到老师信息，由于无法扫码登录，暂时以这个代替
        $Teacher = Teacher::get(1);

        //得到课程和班级的信息
        $klasses = Klass::all();
        $courses = Course::all();

        //把信息传递给V层
        $this->assign('klasses', $klasses);
        $this->assign('courses', $courses);

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
        if (is_null($id) || $id === 0) {
            throw new \Exception('未获取到id信息', 1);
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

        //判断Id存不存在
        if (is_null($id) || $id === 0) {
            throw new \Exception('未获取到id信息', 1);
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

        //删除原有信息
        $map = ['teacher_id' => $id];
        //执行删除操作，由于可能存在删除0条记录，故使用flase来进行判断
        if (false === $Teacher->teacherCourse()->where($map)->delete()) {
            return $this->error('删除老师课程关联信息发生错误' . $Teacher->TeacherCourse()->getError());
        }
        $coursesIds = Request::instance()->post('course_id/a');

        //对老师班级关联信息执行以上操作
        if (!is_null($coursesIds)) {
            if (!$Teacher->courses()->saveAll($coursesIds)) {
                return $this->error('老师课程信息保存错误', $Teacher->courses()->getError());
            }
        }

        if (false === $Teacher->teacherKlass()->where($map)->delete()) {
            return $this->error('删除老师班级关联信息失败' . $Teacher->teacherKlass()->getError());
        }

        //增加数据
        $klassIds = Request::instance()->post('klass_id/a');

        if (!is_null($klassIds)) {
            if (!$Teacher->klasses()->saveAll($klassIds)) {
                return $this->error('老师班级信息保存错误' . $Teacher->klasses()->getError());
            }
        }

        //成功返回提示
        return $this->success('更新成功', url('index'));

        //获取到正常的异常，输出异常

    }

    //抢课功能
    public function takeLesson()
    {

        //接收数据
        $teacherId = Request::instance()->post('teacherId/d');
        $timeClassroomId = Request::instance()->post('timeClassroomId/d');
        $courseId = Request::instance()->post('courseId/d');
        $klassIds = (array) Request::instance()->post('KlassIds');

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
        //接收要换课的id
        $applyid = Request::instance()->post('id');
        $ApplySchedule = Sechedule::get($applyid); //通过id，找到Sechedule表里对应的对象
        //通过周次，星期，节次，教室找到目标课的id
        $weekly = Request::instance()->post('weekly');
        $week = Request::instance()->post('week');
        $node = Request::instance()->post('node');
        $classroom_id = Request::instance()->post('classroom_num');
        $targetid = Sechedule::findtarget($weekly, $week, $node, $classroom_num);

        //判断是否是同一教室时间
        if ($apply == $targetid) {
            return $this->error('换课失败，目标课不能为同一节课', 'index');
        }

        //实例化目标课对象
        $TargetSechedule = Sechedule::get($targetid);

        //判断目标教室时间是否有课，如果没课或者为同一老师的课，直接调换
        if ($TargetSechedule->teacher_id === null or $TargetSechedule->teacher_id == $ApplySchedule->teacher_id) {
            Sechedule::exchange($applyid, $targetid);
            return $this->success('换课成功', 'index');
        }

        //如果不同，则向目标课程的教师发送消息，取得同意后再向管理员发送请求，通过后进行交换(此功能待完善)
        else {

            //生成请求消息
            $message = new Changelesson();
            $message->applysechedule_id = $applyid;
            $message->targetsechedule_id = $targetid;
            $message->save();
            return $this->success('发送换课请求已成功，请等待审核', 'index');
        }
    }

    //消息界面
    public function message()
    {
        //获取当前老师的id
        $id = 1;

        //从换课表中找到所有换课申请
        $Changelessons = new Changelesson;
        $changelessons = $Changelessons->where('state', '<', 3)->order('id', 'desc')->select();
        $changeresults = $Changelessons->where('state', '>', 2)->order('id', 'desc')->select();

        //筛选出登陆的教师向他人换课的请求信息
        $applymessages = Changelesson::findapply($changelessons,$id);

        //筛选他人向登陆的教师换课的请求信息
        $requestmessages = Changelesson::findrequest($changelessons,$id);

        //筛选出管理员处理的请求结果
        $resultmessages = Changelesson::findresult($changeresults,$id);

        //向v层传送数据
        $this->assign('applymessages', $applymessages);
        $this->assign('requestmessages', $requestmessages);
        $this->assign('resultmessages',$resultmessages);
        return $this->fetch("message");
    }

    //处理请求
    public function handlemessage($id, $request)
    {
        //实例化换课请求对象
        $Changelesson = Changelesson::get($id);

        //如果request为1，则同意换课请求
        if ($request == 1) {
            $Changelesson->state = 1; //修改状态为教师已同意
            $Changelesson->save(); //保存修改
            return $this->success('操作成功,已同意该请求', url('message'));
        }

        //如果request为0，则拒绝换课请求
        else if ($request == 0) {
            $Changelesson->state = 2; //修改状态为教师不同意
            $Changelesson->save(); //保存修改
            return $this->success('操作成功,已拒绝该请求', url('message'));
        } else {
            return $this->error('操作失败,请重试', url('message'));
        }
    }

}
