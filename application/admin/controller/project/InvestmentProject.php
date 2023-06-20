<?php

namespace app\admin\controller\project;

use app\common\controller\Backend;

use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use app\common\job\OrderRelease;
use app\common\model\ProjectType;
use app\common\model\InvestmentProjectLabel;
use app\common\model\ProjectRebatesRateRecord;

class InvestmentProject extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'project_id,name';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/InvestmentProject');
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
            ->with('project_type,investmentProjectLabel')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        foreach ($list as $k => &$v) {
            $v['investment_project_label_name'] = '';
            if ($v['investment_project_label']) {
                $v['investment_project_label_name'] = implode('，', array_column($v['investment_project_label'], 'label_name'));
            }
            $v['give_coupons_can_use_project_type_name'] = '';
            if ($v['give_coupons_can_use_project_type']) {
                $v['give_coupons_can_use_project_type_name'] = (new ProjectType())->where('id', $v['give_coupons_can_use_project_type'])->value('type_name');
            }
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
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
            //限制月返参数
            if ($params['rebate_method'] == 2) {
                if (!$params['month_rebate_daily']) {
                    throw new Exception('月返需要指定发放日期');
                }
                if ($params['month_rebate_daily'] <= 0 || $params['month_rebate_daily'] > 31) {
                    throw new Exception('月返指定发放日期数值错误');
                }
            }

            if ($params['rebate_method'] == 3 && !$params['recurring_rebate_interval_date']) {
                throw new Exception('定期返需要设定返利间隔时间');
            }
            if ($params['rebate_method'] == 3 && $params['dividend_method'] != 3) {
                throw new Exception('定期返利的分红方式也需要设定为本息定期同返');
            }
            //是否发放优惠券
            if ($params['is_give_coupons'] == 1) {
                if (!$params['give_coupons_times'] || $params['give_coupons_times'] <= 0) {
                    throw new Exception('请输入赠送优惠券次数');
                }
                if (empty($params['give_coupons_amount']) || $params['give_coupons_amount'] <= 0) {
                    throw new Exception('请输入赠送优惠券金额');
                }
                if (empty($params['give_coupons_can_use_project_type'])) {
                    throw new Exception('请选择赠送优惠券可使用项目板块');
                }
                if (empty($params['give_coupons_use_limit']) || $params['give_coupons_use_limit'] <= 0) {
                    throw new Exception('请输入赠送优惠券可使用最低投资金额');
                }
                if (empty($params['give_coupons_valid_days']) || $params['give_coupons_valid_days'] <= 0) {
                    throw new Exception('请输入优惠券有效天数');
                }
            } else {
                $params['give_coupons_times'] = null;
                $params['give_coupons_amount'] = null;
                $params['give_coupons_can_use_project_type'] = null;
                $params['give_coupons_use_limit'] = null;
                $params['give_coupons_valid_days'] = null;
            }
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $params['funds_raised'] = 0;
            $result = $this->model->allowField(true)->save($params);
            $projectId = $this->model->getLastInsID();
            if ($params['label_ids']) {
                $labelIds = explode(',', $params['label_ids']);
                $labelPars = [];
                foreach ($labelIds as $k => $v) {
                    $labelPars[] = [
                        'label_id' => $v,
                        'project_id' => $projectId,
                    ];
                }
                if (!(new InvestmentProjectLabel())->saveAll($labelPars)) {
                    throw new Exception('项目标签绑定错误');
                }
            }
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
            //获取标签数据
            $investmentProjectLabelData = (new InvestmentProjectLabel())->where('project_id', $row['project_id'])->select();
            $labelIds = '';
            if ($investmentProjectLabelData) {
                $labelIds = implode(',', array_column(collection($investmentProjectLabelData)->toArray(), 'label_id'));
            }
            $this->view->assign('labelIds', $labelIds);

            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        $InvestmentProjectLabelModel = new InvestmentProjectLabel();
        Db::startTrans();
        try {
            //限制月返参数
            if ($params['rebate_method'] == 2) {
                if (!$params['month_rebate_daily']) {
                    throw new Exception('月返需要指定发放日期');
                }
                if ($params['month_rebate_daily'] <= 0 || $params['month_rebate_daily'] > 31) {
                    throw new Exception('月返指定发放日期数值错误');
                }
            }

            if ($params['rebate_method'] == 3 && !$params['recurring_rebate_interval_date']) {
                throw new Exception('定期返需要设定返利间隔时间');
            }
            if ($params['rebate_method'] == 3 && $params['dividend_method'] != 3) {
                throw new Exception('定期返利的分红方式也需要设定为本息定期同返');
            }
            //是否发放优惠券
            if ($params['is_give_coupons'] == 1) {
                if (!$params['give_coupons_times'] || $params['give_coupons_times'] <= 0) {
                    throw new Exception('请输入赠送优惠券次数');
                }
                if (empty($params['give_coupons_amount']) || $params['give_coupons_amount'] <= 0) {
                    throw new Exception('请输入赠送优惠券金额');
                }
                if (empty($params['give_coupons_can_use_project_type'])) {
                    throw new Exception('请选择赠送优惠券可使用项目板块');
                }
                if (empty($params['give_coupons_use_limit']) || $params['give_coupons_use_limit'] <= 0) {
                    throw new Exception('请输入赠送优惠券可使用最低投资金额');
                }
                if (empty($params['give_coupons_valid_days']) || $params['give_coupons_valid_days'] <= 0) {
                    throw new Exception('请输入优惠券有效天数');
                }
            } else {
                $params['give_coupons_times'] = null;
                $params['give_coupons_amount'] = null;
                $params['give_coupons_can_use_project_type'] = null;
                $params['give_coupons_use_limit'] = null;
                $params['give_coupons_valid_days'] = null;
            }
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);

            //删除旧项目标签
            $InvestmentProjectLabelModel->where('project_id', $row['project_id'])->delete();
            //有新的项目标签
            if ($params['label_ids']) {
                $labelIds = explode(',', $params['label_ids']);
                $labelPars = [];
                foreach ($labelIds as $k => $v) {
                    $labelPars[] = [
                        'label_id' => $v,
                        'project_id' => $row['project_id'],
                    ];
                }
                if (!(new InvestmentProjectLabel())->saveAll($labelPars)) {
                    throw new Exception('项目标签绑定错误');
                }
            }

            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    //操作日化利率
    public function change_rebates_rate()
    {
        $Id = $this->request->request('id');
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($Id);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        //获取现在的日化利率
        $nowRebatesRate = (new OrderRelease())->getActualRebatesRate($row['project_id'], time(), $row['daily_rebates_rate']);
        $this->view->assign('now_rebates_rate', $nowRebatesRate);
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
            $params['project_id'] = $row['project_id'];
            if ($params['new_rebates_rate'] <= 0) {
                throw new Exception('日化利率不得小于0');
            }
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
            if ($params['end_time'] < time()) {
                throw new Exception('结束时间请大于现在时间');
            }
            if ($params['end_time'] <= $params['start_time']) {
                throw new Exception('结束时间请大于开始时间');
            }
            $params['daily_rebates_rate'] = $params['new_rebates_rate'];

            $result = (new ProjectRebatesRateRecord())->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function selectpage()
    {
        return parent::selectpage(); // TODO: Change the autogenerated stub
    }
}