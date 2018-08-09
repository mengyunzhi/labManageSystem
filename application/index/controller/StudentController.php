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
    public function student()
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

    public function index()
    {
        // 获取查询信息
        $name = Request::instance()->get('name');

        // 设置每页大小
        $pageSize = 5;

        // 实例化Student
        $Student = new Student;
        
        // 按条件查询数据并调用分页
        $students = $Student->where('name', 'like', '%' . $name . '%')->paginate($pageSize, false, [
            'query'=>[
                'name' => $name,
                ],
            ]);         
        // 向V层传数据
        $this->assign('students', $students);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;       
    }

    public function insert()
    {
        // 接收传入数据
        $postData = Request::instance()->post();    

        // 实例化Teacher空对象
        $Student = new Student();

        // 为对象赋值
        $Student->name = $postData['name'];
        $Student->klass_id = $postData['klass_id'];
        
        // 新增对象至数据表
        $Student->save();

        // 提示操作成功，并跳转至教师管理列表
        return $this->success('学生' . $Student->name . '新增成功。', url('index'));
    }


    public function add()
    {
        $htmls = $this->fetch();
        return $htmls;
    }

    public function delete()
    {
        // 获取pathinfo传入的ID值.
        $id = Request::instance()->param('id/d'); // “/d”表示将数值转化为“整形”

        if (is_null($id) || 0 === $id) {
            return $this->error('未获取到ID信息');
        }

        // 获取要删除的对象
        $Student = Student::get($id);

        // 要删除的对象不存在
        if (is_null($Student)) {
            return $this->error('不存在id为' . $id . '的学生，删除失败');
        }

        // 删除对象
        if (!$Student->delete()) {
            return $this->error('删除失败:' . $Student->getError());
        }

        // 进行跳转
        return $this->success('删除成功', url('index'));
    }

    public function edit()
    {       
        // 获取传入ID
        $id = Request::instance()->param('id/d');

        // 在Student表模型中获取当前记录
        if (is_null($Student = Student::get($id))) {
            return '系统未找到ID为' . $id . '的记录';
        } 
        
        // 将数据传给V层
        $this->assign('Student', $Student);

        // 获取封装好的V层内容
        $htmls = $this->fetch();

        // 将封装好的V层内容返回给用户
        return $htmls;
    }

    public function update()
    {
        // 接收数据，获取要更新的关键字信息
        $id = Request::instance()->post('id/d');

        // 获取当前对象
        $Student = Student::get($id);

        // 写入要更新的数据
        $Student->name = input('post.name');
        $Student->klass_id = input('post.klass_id');
        // 更新
        $Student->save();
        return $this->success('操作成功', url('index'));
    }


}