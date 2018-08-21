<?php
namespace app\index\controller;
use app\common\model\Classroom;
use app\common\model\Semester;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Teacher;
use app\common\model\Sechedule;
use app\common\model\Administrator;
use think\Controller;
use think\facade\Request;
/**
 * 管理员页面和个人信息页面的功能 
 */
class AdministratorController extends Controller
{
    private $sechedule;/*行程范围 @param where查询后返回值*/

    private $currentSemester;/*当前查询学期 默认为本学期 @param Semester*/
    
    private $currentWeekorder;/*当前查询周次 默认本周次 @param int*/
    
    private $currentClassroom;/*当前查询教室 @param Classroom*/
    
    private $administrator;/*登录的管理员 @param Teacher*/
    /**
    *构造函数 初始化查询条件
    */
    public function __construct(){
        parent::__construct();
        $this->currentSemester=Semester::currentSemester(Semester::select());
        $this->currentWeekorder=$this->currentSemester->getWeekorder();
        $this->currentClassroom=Classroom::get(1);
        $this->setRange($this->currentSemester->id,$this->currentWeekorder,$this->currentClassroom->id);
    }
    public function index()
    {
        $postData=Request::instance()->post();
        if (!empty($postData)) {
          $this->setRange((int)$postData['semester_id'],(int)$postData['weekorder'],(int)$postData['classroom_id']);
        }
        $secheduleList=$this->editSechedule();
        //像v层传送老师数据
        $this->assign([
          'secheduleList'=>$secheduleList,
          'Klasses'=>Klass::select(),
          'Courses'=>Course::select(),
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
    public function setRange($semesterId,$weekorder,$classroomId)
    {
      $this->currentSemester=Semester::get($semesterId);
      $this->currentWeekorder=$weekorder;
      $this->currentClassroom=Classroom::get($classroomId);
      $this->sechedule=Sechedule::where('semester_id','=',$semesterId)->where('weekorder','=',$weekorder)->where('classroom_id','=',$classroomId);
    }
     /**
    *获取行程 编辑行程格式
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
    //管理员个人信息界面
    public function personalinformation()
    {
        $Administrator = new Administrator();
        $Administrator = Administrator::get(1);

        //向v层传数据
        $this->assign('Administrator', $Administrator);
        return $this->fetch("personalInformation");
        return $this->fetch("creatCode");
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
        return $this->fetch("creatcode");
    }
}