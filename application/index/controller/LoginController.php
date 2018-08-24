<?php

namespace app\index\controller;

use app\common\model\College;
use app\common\model\Grade;
use app\common\model\Klass;
use app\common\model\Major;
use app\common\model\User;
use think\Controller;
use think\facade\Request;
use app\common\model\Teacher;
use app\common\model\Student;
use app\common\model\Administrator;

/**
 *登录类 包含学生注册 老师注册 登录验证 登录界面
 */
class LoginController extends Controller
{
    /**
     *登录的界面 可以让学生注册
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     *对用户登录做出处理
     */
    public function login()
    {
        $postData = Request::instance()->post();
        $user = User::get(['username' => $postData['username']]);
        if (is_null($user)) {
            return $this->error("用户不存在");
        }
        if (!$user->login($postData['password'])) {
            return $this->error("密码错误");
        }
        return $this->role($user);
    }

    /**
     *根据用户的角色跳转到课表页面
     * @param User $user 用户
     */
    public function role($user)
    {
        $roleName = $user->role->name;
        session('userId', $user->getData('id'));
        if ($roleName == "老师") {
            return $this->success("教师登录成功", url('Teacher/index'));
        } else if ($roleName == "学生") {
            return $this->success("学生登录成功", url('Student/index'));
        } else if ($roleName == "管理员") {
            return $this->success("管理员登录成功", url("Administrator/index"));
        } else {
            return $this->error("身份验证失败，登录失败");
        }
    }

    /**
     *老师注册界面
     */
    public function register58ea6b4a87950961320699af09b251744f7a2198()
    {
        return $this->fetch();
    }

    /**
     *学生注册界面
     */
    public function studentRegister()
    {
        $klasses = Klass::all();
        $colleges = College::all();
        $majors = Major::all();
        $grades = Grade::all();

        $this->assign('klasses', $klasses);
        $this->assign('grades', $grades);
        $this->assign('colleges', $colleges);
        $this->assign('majors', $majors);
        return $this->fetch('studentRegister');
    }

    /**
     *进行注册
     */
    public function register()
    {
        $postData = Request::instance()->post();
        $username = $postData['username'];
        $password = $postData['password'];
        $role = $postData['role'];
        $name = $postData['name'];
        if ($role == "学生") {
            $klassId = $postData['klassId'];
        }
        if (empty($username) || empty($password)) {
            return $this->error("请输入完整信息");
        }
        // 判断用户名是否存在
        if (!is_null(User::get(['username' => $username]))) {
            return $this->error("用户名已存在");
        }
        if (User::register($username, $password, $role)) {
            $user = User::get(['username' => $username]);
            if ($role == "老师") {
                $this->addTeacher($name, $user->id);
            } else if ($role == "学生") {
                $this->addStudent($name, $user->id, $klassId);
            } else {
                return $this->error("数据出错");
            }
            $this->role(User::get(['username' => $username]));
        } else {
            return $this->error("注册失败");
        }
    }

    /**
     *增加教师
     * @param string $name 教师名字
     * @param int $id 用户id
     */
    public function addTeacher($name, $id)
    {
        $teacher = new Teacher();
        $teacher->name = $name;
        $teacher->user_id = $id;
        $teacher->save();
    }

    /**
     *增加学生
     * @param string $name 学生名字
     * @param int $id 用户id
     * @param int $klassId 学生的班级
     */
    public function addStudent($name, $id, $klassId)
    {
        $student = new Student();
        $student->name = $name;
        $student->user_id = $id;
        $student->klass_id = $klassId;
        $student->save();
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
}