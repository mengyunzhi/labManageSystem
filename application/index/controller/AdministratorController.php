<?php
namespace app\index\controller;
use think\Controller;
use think\facade\Request;           // 引用Request
use app\common\model\Administrator;

class AdministratorController extends Controller
{
    //管理员首页
    public function index()
    {
        return $this->fetch();
    }
    //管理员个人信息界面
    public function personalinformation()
    {
        $Administrator = new Administrator();
        $Administrator = Administrator::get(1);

        //向v层传数据
        $this->assign('Administrator', $Administrator);
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
        return $this->fetch("creatcode");
    }
}
