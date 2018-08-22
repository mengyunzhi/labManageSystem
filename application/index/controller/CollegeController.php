<?php
namespace app\index\controller;
use app\common\model\College;
use think\Controller;
use think\facade\Request;

class CollegeController extends controller
{
	public function index()
	{
		//获取查询信息
		$name =Request::instance()->get('name');
		//设置每页大小
		$pageSize = 5;
		//实例化College
		$College = new College;
		//增加数据显示在第一个，按条件查询并分页
		$colleges = $College->where('name', 'like', '%' . $name .'%')->order('id desc')->paginate($pageSize, false,[
			'query' =>[
				'name' => $name,
			],
		]);
		//向V层传数据
		$this->assign('colleges',$colleges);
		//渲染数据
		return $this->fetch();
	}

	//新增学院
	public function save()
	{
		// 接收传入数据
        $Request = Request::instance();
        // 实例化
        $College = new College();
        //为对象的属性赋值
        $College->name = $Request->post('name');
 
        // 添加数据到数据表
        if (!$College->save()) {
            return $this->error('数据添加错误：' . $Course->getError());
        }
        // 新增成功跳转界面
        return $this->success($College->name . '新增成功', url('index'));
	}

	//编辑学院
	public function update()
	{
		//获取编辑ID
		$id = Request::instance()->post('id/d');
		//获取传入信息
		$College = College::get($id);
		if (is_null($College)) {
            return $this->error('系统未找到ID为' . $id . '的记录');
        }
        // 数据更新
        $College->name = Request::instance()->post('name');
        if (!$College->save()) {  
            return $this->error('更新错误：' . $College->getError());
        } else {
        // 更新成功进行跳转
         return $this->success($College->name . '更新成功', url('index'));
        }
	}


	//删除学院
	public function delete()
	{
		// 获取ID
        $id = Request::instance()->param('id/d'); 
        if (is_null($id) || 0 === $id) {
            return $this->error('未获取到ID信息');
        }
        // 获取要删除的对象
        $College = College::get($id);
        // 要删除的对象不存在
        if (is_null($College)) {
            return $this->error('不存在id为' . $id . '的学院，删除失败');
        }
        // 删除对象
        if (!$College->delete()) {
            return $this->error('删除失败:' . $College->getError());
        }
        // 删除成功进行跳转
        return $this->success($College->name .'删除成功', url('index'));
	}

}