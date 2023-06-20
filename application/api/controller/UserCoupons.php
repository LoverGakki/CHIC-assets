<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 用户优惠券相关
 * Class UserCoupons
 * @package app\api\controller
 */
class UserCoupons extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('UserCoupons', 'logic');
    }

    /**
     * 获取用户优惠券列表
     */
    public function getUserCouponsList()
    {
        if ($this->request->isPost()) {
            $page = $this->request->post('page', 1, 'intval');
            $pageSize = $this->request->post('page_size', 10, 'intval');
            if (!$page || !is_numeric($page)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$pageSize || !is_numeric($pageSize)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getUserCouponsList($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取可使用的优惠券列表（支付订单用）
     */
    public function getCanUseCouponsList()
    {
        if ($this->request->isPost()) {
            $projectTypeId = $this->request->post('id');
            $investmentAmount = $this->request->post('investment_amount');
            if (!$projectTypeId || !is_numeric($projectTypeId)) {
                $this->error(__('Invalid parameters'));
            }
            if (empty($investmentAmount) || !is_numeric($projectTypeId)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getCanUseCouponsList($this->auth->id, $projectTypeId, $investmentAmount));
        }
        $this->error(__('Incorrect request mode'));
    }
}