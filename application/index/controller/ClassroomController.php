<?php
namespace app\index\controller;
use think\Controller;    //引用controller
use app\common\model\Sechedule;
use app\common\model\Semester; 
use app\common\model\Classroom;  // 教室模型
use think\facade\Request;			// 引用Request
class ClassroomController extends Controller 
{
    public function index()
    {
    	// 获取查询信息
        $name = Request::instance()->get('name');

        // 设置每页大小
        $pageSize = 5;

    	// 实例化Classroom
    	$Classroom = new Classroom;
		
		// 按条件查询数据并调用分页
        $classrooms = $Classroom->where('name', 'like', '%' . $name . '%')->paginate($pageSize, false, [
            'query'=>[
                'name' => $name,
                ],
            ]);              	
        // 向V层传数据
        $this->assign('classrooms', $classrooms);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;       
    }

    public function insert()
    {
    	// 接收传入数据
        $postData = Request::instance()->post();
        $Classroom = new Classroom();
        $Classroom->name = $postData['name'];
        // 新增对象至数据表
        if ($Classroom->save()){
            $this->newsechedule($Classroom->id);
            return $this->success('教室' . $Classroom->name . '新增成功。', url('index'));
        }else{
            return $this->error("保存失败");
        }
    }


    public function delete()
    {
    	// 获取pathinfo传入的ID值.
        $id = Request::instance()->param('id/d'); // “/d”表示将数值转化为“整形”
        if (is_null($id) || 0 === $id) {
            return $this->error('未获取到ID信息');
        }

        // 获取要删除的对象
        $Classroom = Classroom::get($id);

        // 要删除的对象不存在
        if (is_null($Classroom)) {
            return $this->error('不存在id为' . $id . '的学期，删除失败');
        }

        // 删除对象
        if ($this->deletesechedule($Classroom->id)&&!$Classroom->delete()) {
            return $this->error('删除失败:' . $Classroom->getError());
        }

        // 进行跳转
        return $this->success('删除成功', url('index'));
    }

    public function update()
    {
        // 接收数据，获取要更新的关键字信息
        $id = Request::instance()->post('id/d');

        // 获取当前对象
        $Classroom = Classroom::get($id);

        // 写入要更新的数据
        $Classroom->name = input('post.name');
        // 更新
        $Classroom->save();
        return $this->success('操作成功', url('index'));
    }

    /**
    *删除有关教室行程
    *@param int $classId 删除的教室id
    *@return boolean
    */
    public function deletesechedule($classId)
    {
        if (Sechedule::where("classroom_id","=",$classId)->delete()) {
            return true;
        }else{
            return false;
        }
    }
    /**
    *增加教室行程
    *@param int $classId 增加教室id
    */
    public function newsechedule($classId)
    {
        $allSemester=Semester::select();
        foreach ($allSemester as $key => $semester) {
                for($i=1;$i<=$semester->getData('totalweek');$i++){
                    for($week=1;$week<=7;$week++){
                        for($node=1;$node<=5;$node++){                         
                                $sechedule=new Sechedule();
                                $sechedule->weekorder=$i;
                                $sechedule->week=$week;
                                $sechedule->node=$node;
                                $sechedule->classroom_id=$classId;
                                $sechedule->semester_id=$semester->getData('id');
                                $sechedule->save();
                        }
                    }
                }   
        }
        return;
        
    }
}
