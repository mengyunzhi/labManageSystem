<?php
namespace app\index\controller;
use think\Controller;

class LabController extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
    public function creatCode()
    {
    	return $this->fetch("creatCode");
    }

    public function changeClasses()
    {
    	return $this->fetch("changeClasses");
    }
    public function personalInformation()
    {
    	return $this->fetch("personalInformation");
    }
    public function takeLesson()
    {
    	return $this->fetch("takeLesson");
    }
    public function classroomAdministrate()
    {
        return $this->fetch("classroomAdministrate");
    }
    public function courseManage()
    {
        return $this->fetch("courseManage");
    }
    public function semesterAdministrate()
    {
        return $this->fetch("semesterAdministrate");
    }
}
