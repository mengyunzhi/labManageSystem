<?php
namespace app\index\controller;
use think\Controller;
use app\common\model\Semester;  // 学期模型
use app\common\model\Sechedule;
use app\common\model\Classroom;
use think\facade\Request;		// 引用Request
class SemesterController extends Controller 
{
    /**
    *显示学期管理的界面
    *@param string
    */
    public function index()
    {
    	// 获取查询信息
        $name = Request::instance()->get('name');

        // 设置每页大小
        $pageSize = 5;

    	// 实例化Semester
    	$Semester = new Semester;
		
		//增加数据显示在第一个,按条件查询并分页
        $semesters= $Semester->where('name', 'like', '%' . $name . '%')->order('id desc')->paginate($pageSize, false,[
            'query'=>[
                'name' =>$name,
            ],
        ]);

       // 向V层传数据
        $this->assign('semesters', $semesters);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;       
    }
    /**
    *新增学期的方法
    */
    public function insert()
    {
    	
          //接收传入数据
        $postData = Request::instance()->post();        
        $Semester = new Semester();
        // 为对象的属性赋值
        $Semester->name = $postData['name'];
        $Semester->starttaketime=strtotime($postData['starttaketime']);
        $Semester->endtaketime=strtotime($postData['endtaketime']);
        $Semester->totalweek=(int)$postData['totalweek'];
        $Semester->begintime=strtotime($postData['begintime']);
        $Semester->closetime=strtotime($postData['closetime']);
        $Semester->startweekorder=$postData['startweekorder'];
        $Semester->endweekorder=$postData['endweekorder'];
        // 新增对象至数据表
       if ($Semester->save()){
            $this->newsechedule($Semester->id,$Semester->totalweek,1);
            return $this->success('学期' . $Semester->name . '新增成功。', url('index'));
       }else{
            return $this->error("保存失败");
       }    

    }

    /**
    *删除学期的方法
    */
    public function delete()
    {
          // 获取pathinfo传入的ID值.
        $id = Request::instance()->param('id/d'); // “/d”表示将数值转化为“整形”

        if (is_null($id) || 0 === $id) {
            return $this->error('未获取到ID信息');
        }
        //删除学期
        $Semester = Semester::get($id);
        if (is_null($Semester)) {
            return $this->error('不存在id为' . $id . '的学期，删除失败');
        }
        if ($this->deletesechedule($id,1,$Semester->totalweek)&&!$Semester->delete()) {
            return $this->error('删除失败:' . $Semester->getError());
        }
        return $this->success('删除成功', url('index'));
    }
    /**
    *更新学期的方法
    */
    public function update()
    {
        $postData = Request::instance()->post();    
        $Semester = Semester::get((int)$postData['id']);
        // 为对象的属性赋值
        $oldtotalweek=$Semester->totalweek;
        $Semester->name = $postData['name'];
        $Semester->starttaketime=strtotime($postData['starttaketime']);
        $Semester->endtaketime=strtotime($postData['endtaketime']);
        $Semester->totalweek=(int)$postData['totalweek'];
        $Semester->begintime=strtotime($postData['begintime']);
        $Semester->closetime=strtotime($postData['closetime']);
        $Semester->startweekorder=(int)$postData['startweekorder'];
        $Semester->endweekorder=(int)$postData['endweekorder'];
        $this->editsechedule($Semester->id,$oldtotalweek,$Semester->totalweek);
        // 更新
        $Semester->save();
        return $this->success('操作成功', url('index'));
    }


    /**
    *根据学期新建行程
    *@param int $id 学期的id
    *@param int $totalweek 生成周次总数
    *@param int $startweek 开始生成的周次
    *@return boolean 
    */
    public function newsechedule($id,$totalweek,$startweek)
    {
        $num=0;
        $classrooms=Classroom::select();
        $count=count($classrooms);
        for($i=$startweek;$i<=$totalweek;$i++){
            for($week=1;$week<=7;$week++){
                for($node=1;$node<=5;$node++){
                    for($j=0;$j<$count;$j++){
                        $sechedule=new Sechedule();
                        $sechedule->weekorder=$i;
                        $sechedule->week=$week;
                        $sechedule->node=$node;
                        $sechedule->classroom_id=$classrooms[$j]->id;
                        $sechedule->semester_id=$id;
                        $sechedule->save();
                    }
                }
            }
        }
    }

    /**
    *删除学期的行程
    *@param int $id 要删除的学期id
    *@param int $startweek 删除周次的起始
    *@param int $endweek 结束的周次
    *@return boolean
    */
    public function deletesechedule($id,$startweek,$endweek)
    {   
        if (Sechedule::where("semester_id","=",$id)->where("weekorder","between",[$startweek,$endweek])->delete()) {
            return true;
        }else{
            return false;
        }
    }
    
    /**
    *修改学期的行程
    *@param int $id修改的学期id
    */
    public function editsechedule($id,$oldtotalweek,$newtotalweek)
    {   
        if($oldtotalweek>$newtotalweek){
            $this->deletesechedule($id,$newtotalweek+1,$oldtotalweek);
        }else if($oldtotalweek<$newtotalweek){
            $this->newsechedule($id,$newtotalweek,$oldtotalweek+1);
        }else{
            return ;
        }
    }
    /**
    *设置开放选课的学期
    */
    public function setTakeSemester()
    {
        $id = Request::instance()->param('id/d');
        if (is_null(Semester::get($id))) {
            return $this->error("没有这个学期");
        }
        foreach (Semester::select() as $key => $semester) {
            if ($semester->id==$id) {
                $semester->istakesemester="true";
            }else{
                $semester->istakesemester="false";
            }
            $semester->save();
        }
        return $this->success("设置成功");
    }
}
