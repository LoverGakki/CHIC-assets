<?php

namespace app\admin\controller\project;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class ProjectType extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'id,type_name';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/ProjectType');
    }

    /*public function index()
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
        $typeData = $this->model->select();
        if ($typeData) {
            $typeData = array_column(collection($typeData)->toArray(), null, 'id');
        }
        foreach ($list as $k => &$v) {
            $v['give_coupons_can_use_type'] = '';
            if ($v['give_coupons_can_use_type_ids']) {
                $typeIds = explode(',', $v['give_coupons_can_use_type_ids']);
                foreach ($typeIds as $tk => $tv) {
                    $v['give_coupons_can_use_type'] .= array_key_exists($tv, $typeData) ? $typeData[$tv]['type_name'] . ',' : '';
                }
            }
            if ($v['give_coupons_can_use_type']) {
                $v['give_coupons_can_use_type'] = substr($v['give_coupons_can_use_type'], 0, -1);
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function selectpage()
    {
        return parent::selectpage();
    }

    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            if ($params['is_give_coupons'] == 1 && !$params['give_coupons_can_use_type_ids']) {
                throw new Exception('若赠送优惠券需要选择优惠券可使用板块');
            }
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            if ($params['is_give_coupons'] == 1 && !$params['give_coupons_can_use_type_ids']) {
                throw new Exception('若赠送优惠券需要选择优惠券可使用板块');
            }
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }*/

    public function index()
    {
        return parent::index();
    }

    public function selectpage()
    {
        return parent::selectpage();
    }
}