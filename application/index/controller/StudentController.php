<?php
/**
 * Created by PhpStorm.
 * User: ASUS-PC
 * Date: 2018/8/8
 * Time: 10:32
 */

namespace app\index\controller;

use app\common\model\Student;
use think\Controller;
use think\exception\HttpResponseException;
use think\facade\Request;

class StudentController extends Controller
{
    public function index()
    {

        //通过扫码得到一个信息，通过这个找到这个学生



        return $this->fetch('student');
    }

    //保存数据
    public function save()
    {
        try{
            $id = Request::instance()->post('id');

            //判断是否接收成功
            if (is_null($id) || 0 === $id){
                throw new \Exception('未获取到Id的信息',1);
            }
            $Student = Student::get($id);
            if (null === $Student)
            {
                return $this->error('系统未找到id为'. $id .'的记录');
            }

            $Student->name = Request::instance()->post('name');
            $Student->class = Request::instance()->post('class');
            $result = $Student->save();

            if (!$result)
            {
                return $this->error('操作失败' . $Student->getError());
            }


            //成功跳转到原页面

            return $this->success('操作成功',url('student'));

            //获取到正常的异常时输出异常
        }catch (\Exception $exception)
        {
            return $exception->getMessage();
        }catch (HttpResponseException $exception){
            throw $exception;
        }

    }


}