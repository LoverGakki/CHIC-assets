<?php

namespace app\admin\controller\project_config;

use app\common\controller\Backend;

class AppConfig extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'config_id,name';

    protected $redis = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/AppConfig');
    }

    public function index()
    {
        return parent::index();
    }

}