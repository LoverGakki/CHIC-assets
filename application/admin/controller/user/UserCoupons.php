<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\model\ProjectType;

class UserCoupons extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'user_coupons_id,user_id';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/UserCoupons');
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with('user')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        if ($list) {
            $can_use_project_type_ids = array_column($list->items(), 'project_type_ids');
            if ($can_use_project_type_ids) {
                $can_use_project_type_ids_arr = array_values(array_unique(explode(',', implode(',', $can_use_project_type_ids))));
                $projectTypeData = (new ProjectType())->where('id', 'in', $can_use_project_type_ids_arr)->select();
                if ($projectTypeData) {
                    $projectTypeData = array_column(collection($projectTypeData)->toArray(), null, 'id');
                }
                foreach ($list as $k => $v) {
                    $list[$k]['can_use_project_type_name'] = '';
                    if ($v['project_type_ids']) {
                        $thisCanUseIds = explode(',', $v['project_type_ids']);

                        foreach ($thisCanUseIds as $tk => $tv) {
                            if (array_key_exists($tv, $projectTypeData) && $projectTypeData[$tv]) {
                                $list[$k]['can_use_project_type_name'] .= $projectTypeData[$tv]['type_name'] . '，';
                            }
                        }

                    }
                }
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

}