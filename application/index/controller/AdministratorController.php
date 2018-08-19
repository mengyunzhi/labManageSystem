<?php
namespace app\index\controller;
use app\common\model\Classroom;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Teacher;
use app\common\model\Timeclassroom;
use app\common\model\Administrator;
use think\Controller;
use think\facade\Request;
/*
 * 管理员页面和个人信息页面的功能
 *
 * */
class AdministratorController extends Controller
{
    // private $timeclassroom;
    // public function __construct(){
    //     parent::__construct();
    //     $this->timeclassroom=Timeclassroom::where('semester','=','2018/01');
    //     $this->timeclassroom=$this->timeclassroom->where('weekly','=',1);
    //     $this->timeclassroom=$this->timeclassroom->where('classroom_num','=',1);
       
    // }
    //index页面
    public function index()
    {

    

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