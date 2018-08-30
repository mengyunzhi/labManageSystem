<?php
/**
 * Created by PhpStorm.
 * User: ASUS-PC
 * Date: 2018/8/7
 * Time: 9:46
 */

namespace app\index\controller;
use app\common\model\College;
use app\common\model\Grade;
use app\common\model\Major;
use think\Controller;
use app\common\model\Klass;
use think\exception\HttpResponseException;
use think\facade\Request;

class KlassController extends Controller
{



    public function index()
    {

        $postData = Request::instance()->post();


        // 页面的查询功能和分页
        $name = input('get.name');

        $pageSize = 5;

        $colleges = College::all();
        $majors = Major::all();
        $grades = Grade::all();

        $Klass = new Klass();
        
       //增加数据显示在第一个,按条件查询并分页

        if (empty($postData)) {
            $klasses = $Klass->where('name','like','%'.$name.'%')->order('id desc')->
            paginate($pageSize,false,[
                'query' =>[
                    'name' => $name,
                ]
            ]);
        }else{
            $klasses = $Klass->where('grade_id','like','%'.$postData['searchGradeId'].'%')->
            order('id desc')->paginate($pageSize,false);
        }

        $total_number = action($url = 'Administrator\noReadMessageNumber', $vars = "app\index\controller", $layer = 'controller', $appendSuffix = true);
        $this->assign('total_number', $total_number);
        //向V层传数据
        $this->assign('klasses', $klasses);
        $this->assign('grades', $grades);
        $this->assign('colleges', $colleges);
        $this->assign('majors', $majors);
        //渲染数据
        return $this->fetch();
    }

    public function search($data)
    {
        $map['grade_id'] = $data;
        $klasses = Klass::where($map)->select();
        return $klasses;
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
            $Klass = Klass::get($id);

            //要删除的对象不存在
            if (is_null($Klass)) {
                throw new \Exception('不存在id为' . $id . '的班级，删除失败');
            }

            //删除对象
            if (!$Klass->delete()) {
                return $this->error('删除失败:' . $Klass->getError());
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
    private function  saveKlass(Klass &$Klass, $isUpdate = false)
    {
        //数据更新
        $Klass->name = Request::instance()->post('name');
        $Klass->grade_id = Request::instance()->post('aGrade');
        $result =  $Klass->save();
        return $result;

    }

    //保存新增班级信息
    public function insert()
    {
        $message = '';//提示信息

        try{
            //实例化班级并赋值
            $Klass = new Klass();

            //新增数据
            if (!$this->saveKlass($Klass)){
                // 验证未通过,发生错误
                $message = '数据添加错误: '.$Klass->getError();
            } else {
                // 提示操作成功,并跳转至班级管理页面
                return $this->success('班级'.$Klass->name.'新增成功', url('index'));
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

            //获取传入的班级信息
            $Klass = Klass::get($id);

            if(!is_null($Klass)){
                if (!$this->saveKlass($Klass)){
                    return $this->error('更新失败' . $Klass->getError());
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