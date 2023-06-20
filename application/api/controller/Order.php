<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 订单相关
 * Class Order
 * @package app\api\controller
 */
class Order extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Order', 'logic');
    }

    /**
     * 创建订单并直接支付
     */
    public function createOrder()
    {
        if ($this->request->isPost()) {
            //项目id
            $projectId = $this->request->post('id');
            //投资金额
            $investmentAmount = $this->request->post('investment_amount');
            //用户优惠券id
            $userCouponsId = $this->request->post('user_coupons_id');
            if (!$projectId || !is_numeric($projectId)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$investmentAmount || !is_numeric($investmentAmount) || $investmentAmount <= 0) {
                $this->error(__('Invalid parameters'));
            }
            if ($userCouponsId && !is_numeric($userCouponsId)) {
                $this->error(__('Invalid parameters'));
            }
            $data = $this->logic->createOrder($this->auth->getUser(), $projectId, $investmentAmount, $userCouponsId);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取已投资订单
     */
    public function getOrderList()
    {
        if ($this->request->isPost()) {
            $status = $this->request->post('status');
            $page = $this->request->post('page', 1, 'intval');
            $pageSize = $this->request->post('page_size', 10, 'intval');
            //1=收益中，2=已结束，3=已失败
            if (!$status || !in_array($status, [1, 2, 3])) {
                $this->error(__('Invalid parameters'));
            }
            if (!$page || !is_numeric($page)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$pageSize || !is_numeric($pageSize)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getOrderList($this->auth->id, $status, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取利息相关统计
     */
    public function getOrderInterestStatistics()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getOrderInterestStatistics($this->auth->getUser()));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取订单释放记录
     */
    public function getOrderReleaseRecord(){
        if ($this->request->isPost()) {
            $page = $this->request->post('page', 1, 'intval');
            $pageSize = $this->request->post('page_size', 10, 'intval');
            if (!$page || !is_numeric($page)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$pageSize || !is_numeric($pageSize)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getOrderReleaseRecord($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }
}