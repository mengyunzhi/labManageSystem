<?php
namespace app\index\controller;
use app\common\model\Course;
use think\Controller;
use think\facade\Request;


class CourseController extends Controller
{
    public function index()
    {
        // 获取查询信息
        $name = Request::instance()->get('name');

        $pageSize = 7; // 每页显示5条数据

        // 实例化Teacher
        $Course = new Course; 

        trace($Course, 'debug');

        // 按条件查询数据并调用分页
        $courses = $Course->where('name', 'like', '%' . $name . '%')->paginate($pageSize, false, [
            'query'=>[
                'name' => $name,
                ],
            ]); 

        // 向V层传数据
        $this->assign('courses', $courses);
        return $this->fetch();
    }

    //添加
    public function add()
    {
    	return $this->fetch();
    }

    public function save()
    {
    	// 实例化请求信息
         $Request = Request::instance();

        // 实例化班级并赋值
        $Course = new Course();
        $Course->name = $Request->post('name');
 
        // 添加数据
        if (!$Course->save()) {
            return $this->error('数据添加错误：' . $Course->getError());
        }

        // 进行跳转
        return $this->success('操作成功', url('index'));
   
    }

    //编辑
    public function edit()
    {
         //获取ID
        $id = Request::instance()->param('id/d');

        $Course = Course::get($id);

        if (is_null($Course)) {
            return $this->error('不存在ID为' . $id . '的记录');
        }
        // 将数据传给V层
        $this->assign('Course', $Course);
        return $this->fetch();

    }
    public function update()
    {
        //获取ID
        $id = Request::instance()->post('id/d');

        // 获取传入课程信息
        $Course = Course::get($id);
        if (is_null($Course)) {
            return $this->error('系统未找到ID为' . $id . '的记录');
        }

        // 数据更新
        $Course->name = Request::instance()->post('name');
        if (!$Course->save()) {  
            return $this->error('更新错误：' . $Course->getError());
        } else {
        // 进行跳转
         return $this->success('操作成功', url('index'));
        }
    	
    }


    

    public function delete()
    {

       // 获取ID
        $id = Request::instance()->param('id/d'); 

        if (is_null($id) || 0 === $id) {
            return $this->error('未获取到ID信息');
        }

        // 获取要删除的对象
        $Course = Course::get($id);

        // 要删除的对象不存在
        if (is_null($Course)) {
            return $this->error('不存在id为' . $id . '的教师，删除失败');
        }

        // 删除对象
        if (!$Course->delete()) {
            return $this->error('删除失败:' . $Course->getError());
        }

        // 进行跳转
        return $this->success('删除成功', url('index'));
    }
}