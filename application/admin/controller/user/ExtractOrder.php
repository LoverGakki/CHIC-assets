<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\model\User;
use app\common\library\Log;
use app\common\logic\Common as CommonLogic;
use think\Db;
use think\Exception;
use think\Model;

class ExtractOrder extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'id,user_id';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/ExtractOrder');
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
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    //审核提币申请
    public function pass_verify()
    {
        if ($this->request->isAjax()) {
            $record_id = $this->request->post('record_id');
            $status = $this->request->post('status');
            $audit_status = $this->request->post('audit_status');
            $remark = $this->request->post('remark');
            $recordData = $this->model->where([
                'extract_order_id' => $record_id,
                'status' => $status
            ])->find();
            if (!$recordData) {
                $this->error('暂无该条记录');
            }
            $userData = (new User())->where('id', $recordData['user_id'])->find();
            if (!$userData) {
                $this->error('用户数据错误');
            }
            if (!in_array($audit_status, [1, 2])) {
                $this->error('参数错误');
            }
            $return = ['code' => 0, 'msg' => '校验错误'];
            Db::startTrans();
            try {
                $editPars = [
                    'audit_status' => $audit_status,
                    'processing_time' => time(),
                ];
                //审核不通过
                if ($audit_status == 2) {
                    $editPars['status'] = 4;
                    if ($remark) {
                        $editPars['remark'] = $remark;
                    }
                    //退回余额
                    CommonLogic::changeUserTokenValue($userData, 'token_value', 1, 11, $recordData['extract_amount']);
                } else {
                    $editPars['status'] = 2;
                    $editPars['remark'] = '等待转账中';
                }
                if (!$recordData->save($editPars)) {
                    throw new Exception("提币记录修改错误");
                }

                $return['code'] = 1;
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                if ($e->getCode() == 1) {
                    $this->error($e->getMessage());
                } else {
                    (new Log())->error('pass_verify：' . $e->getMessage());
                    $this->error('审核错误' . $e->getMessage());
                }
            }
            if ($return['code'] == 1) {
                $this->success('审核成功');
            } else {
                $this->error($return['msg']);
            }
        }
        $this->error('请求方式错误');
    }

}