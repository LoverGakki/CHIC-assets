<?php

namespace app\admin\controller\coupons;

use app\common\controller\Backend;
use app\common\model\ProjectType;

class Coupons extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'coupons_id,name';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/Coupons');
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
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        if ($list) {
            $project_type_ids = array_column($list->items(), 'project_type_ids');
            if ($project_type_ids) {
                $project_type_ids_arr = array_values(array_unique(explode(',', implode(',', $project_type_ids))));
                $projectTypeData = (new ProjectType())->where('id', 'in', $project_type_ids_arr)->select();
                if ($projectTypeData) {
                    $projectTypeData = array_column(collection($projectTypeData)->toArray(), null, 'id');
                }
                foreach ($list as $k => $v) {
                    $list[$k]['project_type_name'] = '';
                    if ($v['project_type_ids']) {
                        $thisCanUseIds = explode(',', $v['project_type_ids']);
                        foreach ($thisCanUseIds as $tk => $tv) {
                            if (array_key_exists($tv, $projectTypeData) && $projectTypeData[$tv]) {
                                $list[$k]['project_type_name'] .= $projectTypeData[$tv]['type_name'] . '，';
                            }
                        }

                    }
                }
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function selectpage()
    {
        return parent::selectpage();
    }

}