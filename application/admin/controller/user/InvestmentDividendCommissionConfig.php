<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

class InvestmentDividendCommissionConfig extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'id,sub_level';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/InvestmentDividendCommissionConfig');
    }

    public function index()
    {
        return parent::index();
    }

}