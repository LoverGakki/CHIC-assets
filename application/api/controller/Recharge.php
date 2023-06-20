<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 用户充值相关
 * Class Recharge
 * @package app\api\controller
 */
class Recharge extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Recharge', 'logic');
    }

    /**
     * 用户充值申请
     */
    public function userRechargeApply()
    {
        $this->error('暂未开放');
        if ($this->request->isPost()) {
            $rechargeAmount = $this->request->post('recharge_amount');
            $realName = $this->request->post('real_name');
            $bankName = $this->request->post('bank_name');
            $cardNumber = $this->request->post('card_number');
            if (empty($rechargeAmount) || $rechargeAmount <= 0) {
                $this->error(__('Invalid parameters'));
            }
            if (!$realName) {
                $this->error(__('Invalid parameters'));
            }
            if (!$bankName) {
                $this->error(__('Invalid parameters'));
            }
            if (!$cardNumber) {
                $this->error(__('Invalid parameters'));
            }
            $data = $this->logic->userRechargeApply($this->auth->id, [
                'recharge_amount' => $rechargeAmount,
                'real_name' => $realName,
                'bank_name' => $bankName,
                'card_number' => $cardNumber,
                'type' => 1,
                'operator' => $this->auth->id,
            ]);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }

    //获取充值记录（只获取充值成功的）
    public function getRechargeList()
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
            $data = [
                'total' => 0,
                'rows' => []/*[
                    [
                        'id' => 1,
                        'recharge_amount' => 100,
                        'status' => 1,
                        'create_time' => '2023-05-10 12:20:11',
                    ],
                    [
                        'id' => 2,
                        'recharge_amount' => 200,
                        'status' => 1,
                        'create_time' => '2023-05-10 12:20:11',
                    ],
                    [
                        'id' => 3,
                        'recharge_amount' => 300,
                        'status' => 1,
                        'create_time' => '2023-05-10 12:20:11',
                    ],
                ]*/
            ];
            $this->success('success', $data);
            //$this->success('success', $this->logic->getRechargeList($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

}