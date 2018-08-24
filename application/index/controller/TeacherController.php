<?php

namespace app\index\controller;

use app\common\model\Changelesson;
use app\common\model\Classroom;
use app\common\model\College;
use app\common\model\Course;
use app\common\model\Grade;
use app\common\model\Klass;
use app\common\model\Major;
use app\common\model\Sechedule;
use app\common\model\Semester;
use app\common\model\Teacher;
use app\common\model\TeacherCollege;
use app\common\model\TeacherGrade;
use app\common\model\TeacherKlass;
use app\common\model\TeacherMajor;
use think\Controller;
use think\facade\Request;

/**
 * 老师选课页面和个人信息页面的功能
 */
class TeacherController extends Controller
{
    private $sechedule; /*行程范围 @param where查询后返回值*/

    private $currentSemester; /*当前查询学期 默认为本学期 @param Semester*/

    private $currentWeekorder; /*当前查询周次 默认本周次 @param int*/

    private $currentClassroom; /*当前查询教室 @param Classroom*/

    private $teacher; /*登录的教师 @param Teacher*/

    private $tklassIds = []; /*登录教师教的班级id*/

    private $tmajorsIds = []; //登陆教师教的的专业的id

    private $tcollegesIds = []; //登录教师教的学院的id

    private $tgradesIds = []; //登录教师教的年级的id

    private $tcourses = []; //登录教师教的课程

    /**
     *构造函数 初始化查询条件 取得登录信息
     */
    public function __construct()
    {
        parent::__construct();
        $userId = session('userId');
        $this->teacher = Teacher::get(['user_id' => $userId]);
        if (is_null($this->teacher)) {
            return $this->error("请先登录", url('Login/index'));
        }
        $this->currentSemester = Semester::currentSemester(Semester::select());
        $this->currentWeekorder = $this->currentSemester->getWeekorder();
        $this->currentClassroom = Classroom::get(1);
        $this->setRange($this->currentSemester->id, $this->currentWeekorder);
        //寻找和老师有关的信息的条件
        $map['teacher_id'] = $this->teacher->id;
        //和老师有关的信息
        $this->tklassIds = $this->teacher->teacherKlass()->where($map)->select();
        $this->tmajorsIds = $this->teacher->teacherMajor()->where($map)->select();
        $this->tcollegesIds = $this->teacher->teacherCollege()->where($map)->select();
        $this->tgradesIds = $this->teacher->teacherGrade()->where($map)->select();
        $this->tcourses = $this->teacher->course()->where($map)->select();

    }

    /*
     *显示教师课表
     */
    public function index()
    {
        $postData = Request::instance()->post();
        if (!empty($postData)) {
            $this->setRange((int) $postData['semester_id'], (int) $postData['weekorder']);
        }
        $secheduleList = $this->editIndexSechedule();
        //像v层传送老师数据
        $this->assign([
            'secheduleList' => $secheduleList,
            'Klasses' => Klass::select(),
            'allSemester' => Semester::select(),
            'currentClassroom' => $this->currentClassroom,
            'currentSemester' => $this->currentSemester,
            'currentWeekorder' => $this->currentWeekorder,
            'allClassroom' => Classroom::select(),
            'null' => null,
            'teacher' => $this->teacher,
        ]);
        return $this->fetch();
    }

    /**
     *根据查询条件设置范围
     * @param int $semesterId 查询的学期id
     * @param int $weekorder 查询的周次
     * @param int $classroomId 查询的教室id
     */
    public function setRange($semesterId, $weekorder, $classroomId = null)
    {
        $this->currentSemester = Semester::get($semesterId);
        $this->currentWeekorder = $weekorder;
        $this->sechedule = Sechedule::where('semester_id', '=', $semesterId)->where('weekorder', '=', $weekorder);
        if ($classroomId !== null) {
            $this->currentClassroom = Classroom::get($classroomId);
            $this->sechedule->where('classroom_id', '=', $classroomId);
        }
    }

    /**
     *编辑首页课表行程格式
     * @return array
     */
    public function editIndexSechedule()
    {
        $secheduleList = array();
        for ($i = 1; $i <= 5; $i++) {
            $nodeList = array();
            $temp1 = clone $this->sechedule;
            $temp1 = $temp1->where('node', '=', $i);
            for ($j = 1; $j <= 7; $j++) {
                $temp2 = clone $temp1;
                $temp2 = $temp2->where('week', '=', $j)->select();
                $nodeList[$j] = $this->teacher->getSelfSechedule($temp2);
            }
            ksort($nodeList);
            array_push($secheduleList, $nodeList);
        }
        return $secheduleList;
    }

    /**
     *获取行程 编辑抢课行程格式
     * @return array
     */
    public function editSechedule()
    {
        $weekList = array();
        for ($i = 1; $i <= 5; $i++) {
            $nodeList = array(); //节数组
            //划定每节范围
            $temp = clone $this->sechedule;
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

    /**
     *显示换课抢课界面
     */
    public function takelessonInterface()
    {
        // 判断是否为选课系统开放时间
        $time = time();
        if ($time >= $this->currentSemester->getData('starttaketime') && $time <= $this->currentSemester->getData('endtaketime')) {
            $postData = Request::instance()->post();
            if (!empty($postData)) {
                $this->setRange($this->currentSemester->id, (int) $postData['weekorder'], (int) $postData['classroom_id']);
                $this->currentClassroom = Classroom::get((int) $postData['classroom_id']);
            } else {
                $this->setRange($this->currentSemester->id, $this->currentWeekorder, $this->currentClassroom->id);
            }
            $secheduleList = $this->editSechedule();

            //得到老师教的班级

            $tklasses = [];
            $i = 0;
            foreach ($this->tklassIds as $klassId) {
                $id = $klassId->getData('klass_id');
                if (is_null($tklasses[$i++] = Klass::get($id))) {
                    return $this->error('找的班级不存在');
                }
            }
            //防止传过来的数据为空
            if (is_null($tklasses)) {
                $tklasses = 0;
            }

            //得到老师教的专业
            $tmajors = array();
            $i = 0;
            foreach ($this->tmajorsIds as $majorId) {
                $id = $majorId->getData('major_id');
                $tmajors[$i++] = Major::get($id);
            }
            if (is_null($tmajors)) {
                $tmajors = 0;
            }

            //得到老师教的学院
            $tcolleges = array();
            $i = 0;
            foreach ($this->tcollegesIds as $collegeId) {
                $id = $collegeId->getData('college_id');
                $tcolleges[$i++] = College::get($id);
            }
            if (is_null($tcolleges)) {
                $tcolleges = 0;
            }

            //得到老师的年级
            $tgrades = array();
            $i = 0;
            foreach ($this->tgradesIds as $gradeId) {
                $id = $gradeId->getData('grade_id');
                $tgrades[$i++] = Grade::get($id);
            }
            if (is_null($tgrades)) {
                $tgrades = 0;
            }

            $this->assign([
                'currentSemester' => $this->currentSemester,
                'currentWeekorder' => $this->currentWeekorder,
                'startweekorder' => $this->currentSemester->startweekorder,
                'endweekorder' => $this->currentSemester->endweekorder,
                'currentClassroom' => $this->currentClassroom,
                'allClassroom' => Classroom::select(),
                'Klasses' => Klass::select(),
                'teacher' => $this->teacher,
                'null' => null,
                'secheduleList' => $secheduleList,
                'tcolleges' => $tcolleges,
                'tgrades' => $tgrades,
                'tmajors' => $tmajors,
                'tklasses' => $tklasses,
                'tcourses' => $this->tcourses,
                'changelesson' => new Changelesson,
            ]);
            return $this->fetch('takelessonInterface');
        } else {
            return $this->error("未到开放的时间");
        }

    }

    /**
     *注销登录
     */
    public function logout()
    {
        session('userId', null);
        return $this->success('注销成功', url('Login/index'));
    }

    //进入老师的信息页面
    public function information()
    {

        //得到课程和班级的信息
        $klasses = Klass::all();
        $colleges = College::all();
        $majors = Major::all();
        $grades = Grade::all();

        //得到和老师有关的信息
        $tklass = array();
        $i = 0;
        foreach ($this->tklassIds as $klassId) {
            $id = $klassId->getData('klass_id');
            if (is_null($tklass[$i++] = Klass::get($id))) {
                return $this->error('找的班级不存在');
            }
        }

        $tmajors = array();
        $i = 0;
        foreach ($this->tmajorsIds as $majorId) {

            $id = $majorId->getData('major_id');
            $tmajors[$i++] = Major::get($id);
        }

        $tcolleges = array();
        $i = 0;
        foreach ($this->tcollegesIds as $collegeId) {
            $id = $collegeId->getData('college_id');
            $tcolleges[$i++] = College::get($id);
        }

        $tgrades = array();
        $i = 0;
        foreach ($this->tgradesIds as $gradeId) {
            $id = $gradeId->getData('grade_id');
            $tgrades[$i++] = Grade::get($id);
        }

        //把信息传递给V层
        $this->assign('tklass', $tklass);
        $this->assign('grades', $grades);
        $this->assign('colleges', $colleges);
        $this->assign('majors', $majors);
        $this->assign('klasses', $klasses);
        $this->assign('courses', $this->tcourses);
        $this->assign('teacher', $this->teacher);
        $this->assign('tmajors', $tmajors);
        $this->assign('tcolleges', $tcolleges);
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

        //存储姓名
        $Teacher->name = Request::instance()->post('name');

        if (is_null($Teacher->save())) {
            return $this->error('姓名更新失败' . $Teacher->getError());
        }

        //成功返回提示
        return $this->success('更新成功', url('information'));
    }

    //抢课功能
    public function takeLesson()
    {

        //接收数据
        $teacherId = Request::instance()->post('teacherId/d');
        $secheduleId = Request::instance()->post('secheduleId/d');
        $courseId = Request::instance()->post('courseId/d');
        $klassIds = (array) Request::instance()->post('klassIds');

        if (($teacherId === 0 || $secheduleId === 0 || empty($klassIds) || $courseId === 0 || is_null($courseId))) {
            $this->error("请填写完整信息");

        }

        //得到timeClassroom对象
        $Sechedule = Sechedule::get($secheduleId);

        if (is_null($Sechedule)) {
            throw new \Exception('不存在处于这个时间段的这个教室', 1);
        }

        $theTameTimeSechedules = $Sechedule->findTheSameTimeSechedule($Sechedule);

        //判断相同时间段内老师或学生是否在其他地方上课
        foreach ($theTameTimeSechedules as $theTameTimeSechedule) {
            if ($Sechedule->isExist($theTameTimeSechedule, $teacherId, $klassIds)) {
                return $this->error('抢课失败，您或学生当前时间在其他地方已经有课了', 'takelessonInterface');
            }
        }

        //存数据
        $Sechedule->teacher_id = $teacherId;
        $Sechedule->course_id = $courseId;

        foreach ($klassIds as $id) {

            $Sechedule->klasses()->save($id);
        }

        $Sechedule->save();

        //成功返回提示
        return $this->success('恭喜，抢课成功', 'takelessonInterface');

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
            foreach ($ids as $id) {
                if (!Course::destroy($id)) {
                    return $this->error('删除失败');
                }
            }

            //获取到tp内置异常是，直接向上抛出
        } catch (HttpException $exception) {
            throw $exception;
            //获取到正常的异常时输出异常
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        //成功进行跳转
        return $this->success('删除成功', url('index'));
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
        return $this->success('课程增加成功', url('takelessonInterface'));
    }

    //老师增加班级
    public function addKlass()
    {
        //接收数据
        $collegeId = Request::instance()->post('aCollege');
        $majorId = Request::instance()->post('aMajor');
        $gradeId = Request::instance()->post('aGrade');
        $klassId = Request::instance()->post('aKlass');

        //定制查询条件
        $mapCollege['college_id'] = $collegeId;
        $mapCollege['teacher_id'] = $this->teacher->id;

        $mapMajor['major_id'] = $majorId;
        $mapMajor['teacher_id'] = $this->teacher->id;

        $mapGrade['grade_id'] = $gradeId;
        $mapGrade['teacher_id'] = $this->teacher->id;

        $mapKlass['klass_id'] = $klassId;
        $mapKlass['teacher_id'] = $this->teacher->id;

        //查询数据并判断是否添加
        //判断学院是否需要添加
        if (empty(TeacherCollege::get($mapCollege))) {
            $TeacherCollege = new TeacherCollege();
            $TeacherCollege->college_id = $collegeId;
            $TeacherCollege->teacher_id = $this->teacher->id;
            $TeacherCollege->save();
        }

        //判断专业是否需要添加
        if (empty(TeacherMajor::get($mapMajor))) {

            $TeacherMajor = new TeacherMajor();
            $TeacherMajor->major_id = $majorId;
            $TeacherMajor->teacher_id = $this->teacher->id;
            $TeacherMajor->save();
        }

        //判断年级是否需要添加
        if (empty(TeacherGrade::get($mapGrade))) {
            $TeacherGrade = new TeacherGrade();
            $TeacherGrade->grade_id = $gradeId;
            $TeacherGrade->teacher_id = $this->teacher->id;
            $TeacherGrade->save();
        }

        //判断课程能否需要添加
        if (!empty(TeacherKlass::get($mapKlass))) {
            return $this->error('添加班级失败，因为这个班已经被添加了');
        }

        $TeacherKlass = new TeacherKlass();
        $TeacherKlass->teacher_id = $this->teacher->id;
        $TeacherKlass->klass_id = $klassId;
        $TeacherKlass->save();

        return $this->success('新增班级成功', url('information'));

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
            foreach ($klassIds as $id) {
                $map['klass_id'] = $id;
                $map['teacher_id'] = $teacherId;
                if (!TeacherKlass::destroy($map)) {
                    return $this->error('删除失败');
                }
            }
            //获取到tp内置异常是，直接向上抛出

        } catch (HttpException $exception) {
            throw $exception;
            //获取到正常的异常时输出异常
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        //成功进行跳转
        return $this->success('删除成功', url('information'));
    }

    //换课功能
    public function changeLesson()
    {
        //接收要换课的id
        $applyid = Request::instance()->post('id');
        $ApplySechedule = Sechedule::get($applyid); //通过id，找到Sechedule表里对应的对象

        //通过周次，星期，节次，教室找到目标课的id
        $weekorder = Request::instance()->post('weekorder');
        $week = Request::instance()->post('week');
        $node = Request::instance()->post('node');
        $classroom_id = Request::instance()->post('classroom_id');
        $targetid = Sechedule::findtarget($weekorder, $week, $node, $classroom_id);

        //实例化目标课对象
        $TargetSechedule = Sechedule::get($targetid);

        //判断是否是同一教室时间
        if ($applyid == $targetid) {
            return $this->error('换课失败,要换的课与目标相同时间教室', 'takelessonInterface');
        }

        //判断目标是否为换课中
        if (Changelesson::ischangeLesson($targetid)) {
            return $this->error('换课失败，目标正在换课中', 'takelessonInterface');
        }

        //判断教师和班级与目标课是否时间冲突，避免同一时间在不同教室上课这种情况
        $allSameSechedule = $TargetSechedule->findTheSameTimeSechedule($TargetSechedule); //将同一时间所有sechedule查出来
        $applyTeacherId = $ApplySechedule->teacher_id; //申请换课教师的ID
        $applyKlassIds = $ApplySechedule->getKlasses()->column('klass_id'); //申请换课的班级的ID数组
        $result = $TargetSechedule->isChangeExist($allSameSechedule, $applyTeacherId, $applyKlassIds, $TargetSechedule);

        //如果班级冲突，则输出时间冲突的班级，并返回抢课换课界面
        if (is_array($result)) {
            $klassName = $this->getklassname($result);
            return $this->error('换课失败,' . $klassName . '在这个时间的其他教室有课', 'takelessonInterface');
        }

        //如果老师时间冲突，则输出提示信息，并返回抢课换课界面
        if ($result === 1) {
            return $this->error('换课失败,您在这个时间的其他教室有课', 'takelessonInterface');
        }

        //判断目标教室时间是否有课，如果没课，直接调换
        if ($TargetSechedule->teacher_id === null) {
            Sechedule::exchangenull($applyid, $targetid);
            return $this->success('目标无课，换课成功', 'takelessonInterface');
        }

        //如果有课，判断是否是申请者自己的课，如果是，则直接进行交换
        if ($TargetSechedule->teacher_id == $applyTeacherId) {
            Sechedule::exchange($applyid, $targetid);
            return $this->success('目标为您已抢的课，换课成功', 'takelessonInterface');
        }

        //如果不是申请者自己的课，则判断目标教师换课后是否冲突
        //判断目标课是否时间冲突，避免目标教师班级换课后出现同一时间在不同教室上课这种情况
        $allSameSechedule1 = $ApplySechedule->findTheSameTimeSechedule($ApplySechedule); //将同一时间所有sechedule查出来
        $targetTeacherId = $TargetSechedule->teacher_id; //申请换课教师的ID
        $targetKlassIds = $TargetSechedule->getKlasses()->column('klass_id'); //申请换课的班级的ID数组
        $result1 = $ApplySechedule->isChangeExist($allSameSechedule1, $targetTeacherId, $targetKlassIds, $ApplySechedule);
        if ($result != 2) {
            return $this->error('换课失败，目标老师或班级换课后会时间冲突', 'takelessonInterface');
        }

        //如果不是申请者自己的课,且目标老师换课=后时间不冲突，则向目标课的教师发送消息，取得同意后再向管理员发送请求，通过后进行交换
        if ($TargetSechedule->teacher_id !== $applyTeacherId) {
            //生成请求消息
            $message = new Changelesson();
            $message->applysechedule_id = $applyid;
            $message->targetsechedule_id = $targetid;
            $message->save();
            return $this->success('目标已被其他老师抢占，发送换课请求成功，请等待审核', 'takelessonInterface');
        }
    }

    //消息界面
    public function message()
    {
        //获取当前老师的id
        $id = $this->teacher->id;

        //从换课表中找到所有换课申请
        $Changelessons = new Changelesson;
        $changelessons = $Changelessons->where('state', '<', 3)->order('id', 'desc')->select();
        $changeresults = $Changelessons->where('state', '>', 2)->order('id', 'desc')->select();

        //筛选出登陆的教师向他人换课的请求信息
        $applymessages = Changelesson::findapply($changelessons, $id);

        //筛选他人向登陆的教师换课的请求信息
        $requestmessages = Changelesson::findrequest($changelessons, $id);

        //筛选出管理员处理的请求结果
        $resultmessages = Changelesson::findresult($changeresults, $id);

        //向v层传送数据
        $this->assign('applymessages', $applymessages);
        $this->assign('requestmessages', $requestmessages);
        $this->assign('resultmessages', $resultmessages);
        $this->assign('teacher', $this->teacher);
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
        } //如果request为0，则拒绝换课请求
        else if ($request == 0) {
            $Changelesson->state = 2; //修改状态为教师不同意
            $Changelesson->save(); //保存修改
            return $this->success('操作成功,已拒绝该请求', url('message'));
        } else {
            return $this->error('操作失败,请重试', url('message'));
        }
    }

    public function getMajor()
    {
        $collegeIndex = Request::instance()->param('college/d');
        $map['college_id'] = $collegeIndex;
        $majors = Major::Where($map)->select();

        return $majors;
    }

    public function getGrade()
    {
        $majorIndex = Request::instance()->param('major/d');
        $map['major_id'] = $majorIndex;
        $grades = Grade::Where($map)->select();

        return $grades;
    }

    public function getKlass()
    {
        $gradeIndex = Request::instance()->param('grade/d');
        $map['grade_id'] = $gradeIndex;
        $klasses = Klass::Where($map)->select();

        return $klasses;
    }

    /*根据输入的ID数组，输出相应的班级名称*/
    public function getklassname($ids)
    {
        $i = 0;
        $names = '';
        foreach ($ids as $id) {
            $klass = Klass::get($id);
            $names .= $klass->name;
        }
        return $names;
    }

}
