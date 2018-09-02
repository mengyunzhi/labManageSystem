<?php
namespace app\index\controller;

use app\common\model\Administrator;
use app\common\model\Classroom;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Log;
use app\common\model\Message;
use app\common\model\Sechedule;
use app\common\model\Semester;
use app\common\model\Teacher;
use think\Controller;
use think\facade\Request;

/**
 * 管理员页面和个人信息页面的功能
 */
class AdministratorController extends Controller
{
    private $sechedule; /*行程范围 @param where查询后返回值*/

    private $currentSemester; /*当前查询学期 默认为本学期 @param Semester*/

    private $currentWeekorder; /*当前查询周次 默认本周次 @param int*/

    private $currentClassroom; /*当前查询教室 @param Classroom*/

    private $administrator; /*登录的管理员 @param Administrator*/
    /**
     *构造函数 初始化查询条件
     */
    public function __construct()
    {
        parent::__construct();
        $userId = session('userId');
        $this->administrator = Administrator::get(['user_id' => $userId]);
        if (is_null($this->administrator)) {
            return $this->error("请先登录", url('Login/index'));
        }
        if (!Semester::select()->isEmpty() && !Classroom::select()->isEmpty()) {
            $this->currentSemester = Semester::currentSemester(Semester::select());
            $this->currentWeekorder = $this->currentSemester->getWeekorder();
            $classrooms = Classroom::select();
            $this->currentClassroom = $classrooms[0];
            $this->setRange($this->currentSemester->id, $this->currentWeekorder, $this->currentClassroom->id);
        }

    }
    public function index()
    {
        $postData = Request::instance()->post();
        if (!empty($postData)) {
            $this->setRange((int) $postData['semester_id'], (int) $postData['weekorder'], (int) $postData['classroom_id']);
        }
        $secheduleList = $this->editSechedule();
        $total_number = $this->noReadMessageNumber();
        //像v层传送老师数据
        $this->assign([
            'secheduleList' => $secheduleList,
            'Klasses' => Klass::select(),
            'Courses' => Course::select(),
            'todayWeek' => Semester::currentSemester(Semester::select()),
            'allSemester' => Semester::select(),
            'currentClassroom' => $this->currentClassroom,
            'currentSemester' => $this->currentSemester,
            'currentWeekorder' => $this->currentWeekorder,
            'allClassroom' => Classroom::select(),
            'null' => null,
            'total_number' => $total_number,
        ]);
        return $this->fetch();
    }
    /**
     *根据查询条件设置范围
     *@param int $semesterId 查询的学期id
     *@param int $weekorder 查询的周次
     *@param int $classroomId 查询的教室id
     */
    public function setRange($semesterId, $weekorder, $classroomId)
    {
        $this->currentSemester = Semester::get($semesterId);
        $this->currentWeekorder = $weekorder;
        $this->currentClassroom = Classroom::get($classroomId);
        $this->sechedule = Sechedule::where('semester_id', '=', $semesterId)->where('weekorder', '=', $weekorder)->where('classroom_id', '=', $classroomId);
    }
    /**
     *获取行程 编辑行程格式
     *@return array
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
     *注销登录
     */
    public function logout()
    {
        session('userId', null);
        return $this->success('注销成功', url('Login/index'));
    }
    //管理员个人信息界面
    public function personalinformation()
    {
        $Administrator = $this->administrator;

        $total_number = $this->noReadMessageNumber();

        //向v层传数据
        $this->assign('Administrator', $Administrator);
        $this->assign('total_number', $total_number);
        return $this->fetch("personalInformation");
    }

    //保存管理员提交的个人信息
    public function save()
    {
        $id = Request::instance()->post('id');
        $Administrator = Administrator::get($id);
        //存储
        $Administrator->name = input('post.name');
        $Administrator->password = input('post.password');
        $Administrator->save();
        return $this->success('操作成功', url('index'));
    }

    //生成二维码
    public function creatcode()
    {
        $total_number = $this->noReadMessageNumber();
        $this->assign('total_number',$total_number);
        return  $this->fetch('creatCode');
    }

    //换课申请消息界面
    public function message()
    {
        //获取当前管理员
        $user_id = $this->administrator->user_id;

        // 获取查询信息
        $name = Request::instance()->get('name');

        // 设置每页大小
        $pageSize = 5;

        // 获取Message
        $Messages = Message::where('user_id', '=', $user_id)->where('isAgreeStatus', '=', '1')->order('id desc')->paginate($pageSize, false);
        //未读消息数
        $total_number = $this->noReadMessageNumber();
        // 向V层传数据
        $this->assign('messages', $Messages);
        $this->assign('total_number', $total_number);

        return $this->fetch('message');
    }

    //处理请求
    public function handlemessage($id, $request)
    {
        //获取消息对象
        $requestMessage = Message::get($id);
        //将消息置为已读
        $requestMessage->isReadStatus = 1;
        $requestMessage->save();
        //根据消息对象获取要换的两节课
        $applySechedule = Sechedule::get($requestMessage->apply_sechedule_id);
        $targetSechedule = Sechedule::get($requestMessage->target_sechedule_id);

        //如果不同意，则将isAgreeStatus置为4,并向两位换课的教师各发送一条消息
        if ($request == 0) {
            //修改状态为换课审核未通过，换课失败
            $requestMessage->isAgreeStatus = 4;
            $requestMessage->save();

            //取消换课状态
            $applySechedule->isChangeLesson = 0;
            $targetSechedule->isChangeLesson = 0;
            $applySechedule->save();
            $targetSechedule->save();

            //向两位换课的教师各发送一条消息
            $this->disagreeToApply($applySechedule, $targetSechedule);
            $this->disagreeToTarget($applySechedule, $targetSechedule);

            return $this->redirect('message');
        }
        //如果同意,则将isAgreeStatus置为3，然后向两位换课教师各发一条消息，最后进行换课
        else {
                //修改状态为换课审核通过，换课成功
                $requestMessage->isAgreeStatus = 3;
                $requestMessage->save();

                //取消换课状态
                $applySechedule->isChangeLesson = 0;
                $applySechedule->save();
                $targetSechedule->isChangeLesson = 0;
                $targetSechedule->save();

                //发送消息
                $this->agreeToApply($applySechedule, $targetSechedule);
                $this->agreeToTarget($applySechedule, $targetSechedule);

                //传入要换的sechedule的id进行换课
                Sechedule::exchange($requestMessage->apply_sechedule_id, $requestMessage->target_sechedule_id);

                //生成日志
                $this->creatlog($requestMessage);
                return $this->redirect('message');
        }
    }

    //向申请者发送拒绝换课消息
    public function disagreeToApply($applySechedule, $targetSechedule)
    {
        $Message = new Message();
        $Message->user_id = $applySechedule->teacher->user_id;
        $Message->apply_sechedule_id = $applySechedule->id;
        $Message->target_sechedule_id = $targetSechedule->id;
        $Message->apply_teacher_id = $applySechedule->teacher_id;
        $Message->target_teacher_id = $targetSechedule->teacher_id;
        $Message->apply_course_id = $applySechedule->course_id;
        $Message->target_course_id = $targetSechedule->course_id;
        $Message->isApplyStatus = 1;
        $Message->isAgreeStatus = 4;
        $Message->isReadStatus = 0;
        $Message->save();
    }

    //向被申请发送拒绝换课消息
    public function disagreeToTarget($applySechedule, $targetSechedule)
    {
        $Message = new Message();
        $Message->user_id = $targetSechedule->teacher->user_id;
        $Message->apply_sechedule_id = $applySechedule->id;
        $Message->target_sechedule_id = $targetSechedule->id;
        $Message->apply_teacher_id = $applySechedule->teacher_id;
        $Message->target_teacher_id = $targetSechedule->teacher_id;
        $Message->apply_course_id = $applySechedule->course_id;
        $Message->target_course_id = $targetSechedule->course_id;
        $Message->isApplyStatus = 0;
        $Message->isAgreeStatus = 4;
        $Message->isReadStatus = 0;
        $Message->save();
    }

    //向申请者发送同意换课消息
    public function agreeToApply($applySechedule, $targetSechedule)
    {
        $Message = new Message();
        $Message->user_id = $applySechedule->teacher->user_id;
        $Message->apply_sechedule_id = $applySechedule->id;
        $Message->target_sechedule_id = $targetSechedule->id;
        $Message->apply_teacher_id = $applySechedule->teacher_id;
        $Message->target_teacher_id = $targetSechedule->teacher_id;
        $Message->apply_course_id = $applySechedule->course_id;
        $Message->target_course_id = $targetSechedule->course_id;
        $Message->isApplyStatus = 1;
        $Message->isAgreeStatus = 3;
        $Message->isReadStatus = 0;
        $Message->save();
    }

    //向被申请发送同意换课消息
    public function agreeToTarget($applySechedule, $targetSechedule)
    {
        $Message = new Message();
        $Message->user_id = $targetSechedule->teacher->user_id;
        $Message->apply_sechedule_id = $applySechedule->id;
        $Message->target_sechedule_id = $targetSechedule->id;
        $Message->apply_teacher_id = $applySechedule->teacher_id;
        $Message->target_teacher_id = $targetSechedule->teacher_id;
        $Message->apply_course_id = $applySechedule->course_id;
        $Message->target_course_id = $targetSechedule->course_id;
        $Message->isApplyStatus = 0;
        $Message->isAgreeStatus = 3;
        $Message->isReadStatus = 0;
        $Message->save();
    }

    // 生成日志
    public function creatlog($message)
    {
        $log = new Log;

        $log->applyinformation = $message->getApplyTeacher()->name . '-第' . $message->getApply()->weekorder . '周' . '-星期' . $message->getApply()->week . '-第' . $message->getApply()->node . '节-' . $message->getApply()->classroom->name.'-'.$message->getApplyCourse()->name;

        $log->targetinformation = $message->getTargetTeacher()->name . '-第' . $message->getTarget()->weekorder . '周' . '-星期' . $message->getTarget()->week . '-第' . $message->getTarget()->node . '节-' . $message->getTarget()->classroom->name.'-'.$message->getTargetCourse()->name;

        $log->isAgreeStatus = $message->getData('isAgreeStatus');

        $log->save();
        return '日志生成成功';
    }

    //换课日志
    public function log()
    {
        // 获取查询信息
        $information = Request::instance()->get('information');

        // 设置每页大小
        $pageSize = 5;

        // 实例化Classroom
        $Log = new Log;

        // 按条件查询数据并调用分页
        $logs = $Log
            ->where('applyinformation|targetinformation', 'like', '%' . $information . '%')
            ->order('id', 'desc')
            ->paginate($pageSize, false, [
                'query' => [
                    'applyinformation' => $information,
                ],
            ]);
        $total_number = $this->noReadMessageNumber();
        // 向V层传数据
        $this->assign('logs', $logs);
        $this->assign('total_number', $total_number);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;
    }
    //未读消息总数
    public function noReadMessageNumber()
    {
        //登陆管理员user_id
        $user_id = $this->administrator->user_id;

        //未读消息数
        $total_number = count(Message::where('user_id', '=', $user_id)->where('isReadStatus','=','0')->select());

        return $total_number;
    }
}
