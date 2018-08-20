<?php
namespace app\index\controller;
use app\common\model\Student;
use app\common\model\Klass;
use app\common\model\Timeclassroom;
use app\common\model\Course;
use app\common\model\Classroom;
use think\Controller;
use think\exception\HttpResponseException;
use think\facade\Request;
/*
 * 学生个人信息页面的功能
 * */
class StudentController extends Controller
{
    private $timeclassroom;
    public function __construct(){
        parent::__construct();
        $this->timeclassroom=Timeclassroom::where('semester','=','2018/01');
        $this->timeclassroom=$this->timeclassroom->where('weekly','=',1);
        $this->timeclassroom=$this->timeclassroom->where('classroom_num','=',1);
       
    }
    //index页面
    public function index()
    {

        //初始化设置
        $onWeekly=1;
        $onClassroom=1;
        $Courses=Course::select();
        $Klasses=Klass::select();



        $postData=Request::instance()->post();
        //查询条件
        if (!empty($postData)) {
                $this->timeclassroom=Timeclassroom::where('semester','=','2018/01');
                $this->timeclassroom=$this->timeclassroom->where('weekly','=',(int)$postData['weekly']);

                $onWeekly=(int)$postData['weekly'];
                $this->timeclassroom=$this->timeclassroom->where('classroom_num','=',(int)$postData['classroom_num']);
                $onClassroom=(int)$postData['classroom_num'];
        }
        $weekList=$this->editTimeClassroom();

        $this->assign('weekList',$weekList);
        $allClassroom=Classroom::select();
        $this->assign('allClassroom',$allClassroom);

        //向v层传送数据
        $this->assign('Klasses',$Klasses);
        $this->assign('Courses',$Courses);
        $this->assign('onWeekly',$onWeekly);
        $this->assign('onClassroom',$onClassroom);
        //没有扫码，因此直接得到学生信息
        $Student = Student::get('1');
 $this->assign('Student',$Student);

        return $this->fetch();
    }

    public function editTimeClassroom(){
        $weekList=array();
        for($i=1;$i<=5;$i++){
            $nodeList=array();//节数组
            //划定每节范围
            $temp=clone $this->timeclassroom;
            $temp=$temp->where('node','=',$i);
            $weeklyList=$temp->select();
            foreach($weeklyList as $weekly){
            $nodeList[$weekly['week']]=$weekly;
            }
            ksort($nodeList);
            array_push($weekList, $nodeList);
        }
        return $weekList;
    }

    public function student()
    {
        //没有扫码，因此直接得到学生信息
        $Student = Student::get('1');

        if(is_null($Student))
        {
            return $this->error('不存在这个学生');
        }
        $klasses = Klass::all();

        $this->assign('classes',$klasses);
        $this->assign('Student',$Student);

        return $this->fetch('student');


       
    }

    //保存数据
    public function save()
    {

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

            $Student->klass_id = Request::instance()->post('class');
            $Student->save();


            //成功跳转到原页面
            return $this->success('操作成功',url('student'));

    }


    public function administrate()
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
        //获取所有班级
        $allklass=Klass::select();
                
        // 向V层传数据
        $this->assign('students', $students);
        $this->assign('allklass',$allklass);

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
        return $this->success('学生' . $Student->name . '新增成功。', url('administrate'));
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
        return $this->success('删除成功', url('administrate'));
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
        return $this->success('操作成功', url('administrate'));
    }


}