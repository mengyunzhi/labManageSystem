<?php
namespace app\index\controller;
use app\common\model\Major;
use app\common\model\College;
use think\Controller;
use think\facade\Request;

class MajorController extends controller
{
	public function index()
	{
        // 获取所有的学院信息
        $colleges = College::all();
        $this->assign('colleges', $colleges);
		//获取查询信息
		$name =Request::instance()->get('name');
		//设置每页大小
		$pageSize = 5;
		//实例化
		$Major = new Major;
		//增加数据显示在第一个，按条件查询并分页
		$majors = $Major->where('name', 'like', '%' . $name .'%')->order('id desc')->paginate($pageSize, false,[
			'query' =>[
				'name' => $name,
			],
		]);
		//向V层传数据
		$this->assign('majors',$majors);
		//渲染数据
		return $this->fetch();
	}

	//新增专业
	public function save()
	{
		// 接收传入数据
        $Request = Request::instance();
        // 实例化
        $Major = new Major();
        //为对象的属性赋值
        $Major->name       = $Request->post('name');
 		$Major->college_id = $Request->post('college_id/d');
        // 添加数据到数据表
        if (!$Major->save()) {
            return $this->error('数据添加错误：' . $Course->getError());
        }
        // 新增成功跳转界面
        return $this->success($Major->name . '新增成功', url('index'));
	}

	//编辑专业
	public function update()
	{
		//获取编辑ID
		$id = Request::instance()->post('id/d');
		//获取传入信息
		$Major = Major::get($id);
		if (is_null($Major)) {
            return $this->error('系统未找到ID为' . $id . '的记录');
        }
        // 数据更新
        $Major->name = Request::instance()->post('name');
        $Major->college_id = Request::instance()->post('college_id/d');
        if (!$Major->save()) {  
            return $this->error('更新错误：' . $Major->getError());
        } else {
        // 更新成功进行跳转
         return $this->success('更新成功', url('index'));
        }
	}


	//删除专业
	public function delete()
	{
		// 获取ID
        $id = Request::instance()->param('id/d'); 
        if (is_null($id) || 0 === $id) {
            return $this->error('未获取到ID信息');
        }
        // 获取要删除的对象
        $Major = Major::get($id);
        // 要删除的对象不存在
        if (is_null($Major)) {
            return $this->error('不存在id为' . $id . '的学院，删除失败');
        }
        // 删除对象
        if (!$Major->delete()) {
            return $this->error('删除失败:' . $Major->getError());
        }
        // 删除成功进行跳转
        return $this->success($Major->name .'删除成功', url('index'));
	}

}