<?php

namespace app\admin\controller\message;

use app\common\controller\Backend;

class Sms extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'id,mobile';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/Sms');
    }

    public function index()
    {
        return parent::index();
    }

}