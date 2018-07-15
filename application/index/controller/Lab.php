<?php
namespace app\index\controller;
use think\Controller;

class Lab extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}
