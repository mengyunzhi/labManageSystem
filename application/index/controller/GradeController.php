<?php
/**
 * Created by PhpStorm.
 * User: emmmm
 * Date: 2018/8/24
 * Time: 19:04
 */

namespace app\index\controller;
use app\common\model\College;
use app\common\model\Grade;

use app\common\model\Major;
use app\common\model\Teacher;
use think\Controller;
use think\facade\Request;

class GradeController extends Controller
{
    public function index()
    {

        // 页面的查询功能和分页
        $name = input('get.name');
        $pageSize = 5;
        $colleges = College::all();
        $majors = Major::all();
        $grade =Grade::get(6);

        //按条件查询数据并调用分页
        $grades = $grade->where('name','like','%'.$name.'%')->order('id desc')
            ->paginate($pageSize,false,[
                'query' =>[
                    'name' => $name,
                ]
            ]);

        $total_number = action($url = 'Administrator\noReadMessageNumber', $vars = "app\index\controller", $layer = 'controller', $appendSuffix = true);
        $this->assign('total_number', $total_number);
        //向V层传数据
        $this->assign('grades', $grades);
        $this->assign('colleges', $colleges);
        $this->assign('majors', $majors);
        //渲染数据
        return $this->fetch();
    }

    //班级的删除功能
    public function delete()
    {
        try{
            //实例化请求类
            $Request =  Request::instance();

            //获取get数据

            $id = $Request->param('id/d');

            //判断是否接收成功
            if (0 === $id) {
                throw new \Exception("未获取到ID信息",1);
            }

            //获取要删除的对象
            $Grade = Grade::get($id);

            //要删除的对象不存在
            if (is_null($Grade)) {
                throw new \Exception('不存在id为' . $id . '的年级，删除失败');
            }

            //删除对象
            if (!$Grade->delete()) {
                return $this->error('删除失败:' . $Grade->getError());
            }
            //获取到ThinkPHP的内置异常时，直接向上抛出，交给ThinkPHP处理
        }catch (HttpResponseException $exception) {
            throw $exception;

            //获取到正常的异常时，输出异常
        } catch (\Exception $exception){
            return $exception->getMessage();
        }

        //进行跳转
        return $this->success('删除成功',url("index"));
    }


    //对数据进行保存或更新
    private function  saveGrade(Grade &$Grade, $isUpdate = false)
    {

        //数据更新
        $Grade->name = Request::instance()->post('name');
        $Grade->major_id = Request::instance()->post('aMajor');
        $result =  $Grade->save();
        return $result;

    }

    //保存新增班级信息
    public function insert()
    {
        $message = '';//提示信息

        try{
            //实例化班级并赋值
            $Grade = new Grade();

            //新增数据
            if (!$this->saveGrade($Grade)){
                // 验证未通过,发生错误
                $message = '数据添加错误: '.$Grade->getError();
            } else {
                // 提示操作成功,并跳转至班级管理页面
                return $this->success('年级'.$Grade->name.'新增成功', url('index'));
            }


            // 获取到ThinkPHP的内置异常时，直接向上抛出，交给ThinkPHP处理
        } catch (\think\Exception\HttpResponseException $e) {
            throw $e;

            // 获取到正常的异常时，输出异常
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $this->error($message);
    }

    //执行更新操作
    public function update()
    {
        try{
            //接收数据，获取要更新的关键词信息
            $id = Request::instance()->post('id/d');

            //获取传入的年级信息
            $Grade = Grade::get($id);

            if(!is_null($Grade)){
                if (!$this->saveGrade($Grade)){
                    return $this->error('更新失败' . $Grade->getError());
                }
                // 调用PHP内置类时，需要在前面加上 \

            }
        }catch (\think\Exception\HttpResponseException $e) {
            throw $e;

            // 获取到正常的异常时,输出异常
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        // 成功跳转至index控制器
        return $this->success('操作成功',url('index'));
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
}