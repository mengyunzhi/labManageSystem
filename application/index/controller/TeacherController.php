<?php

/**
 * Created by PhpStorm.
 * User: ASUS-PC
 * Date: 2018/8/9
 * Time: 9:27
 */

namespace app\index\controller;
use app\common\model\Course;
use app\common\model\Klass;
use app\common\model\Teacher;
use think\Controller;
use think\Exception;
use think\exception\HttpResponseException;
use think\facade\Request;

/*
 * 老师选课页面和个人信息页面的功能
 *
 *
 * */

class TeacherController extends Controller
{

    //index页面
    public function index()
    {
        //通过扫码得到当前用户的信息


        //得到课程和班级的信息

        $klasses = Klass::all();
        $courses = Course::all();

        //把信息传递给V层
        $this->assign('klasses',$klasses);
        $this->assign('courses',$courses);

        //取回打包的数据
        return $this->fetch();

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
        try{
            $id = Request::instance()->post('id');

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

            $Teacher->name = Request::instance()->post('name');

           //存储
            $courses_ids = Request::instance()->post('course_id');
            $klass_ids = Request::instance()->post('klass_id');
            var_dump($klass_ids);
            $Teacher->courses()->saveAll($courses_ids);
            $Teacher->klasses()->saveAll($klass_ids);



            //获取到正常的异常，输出异常
        }catch (\Exception $exception)
        {
            return $exception->getMessage();
        }catch (HttpResponseException $exception)
        {
            throw  $exception;
        }


    }


}