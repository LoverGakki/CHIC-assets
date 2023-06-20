<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 资产相关
 * Class Banner
 * @package app\api\controller
 */
class Asset extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Asset', 'logic');
    }

    /**
     * 获取账户明细
     */
    public function getUserAssetList()
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
            $this->success('success', $this->logic->getUserAssetList($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取提现手续比例
     */
    public function getExtractServiceChargeRatio()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getExtractServiceChargeRatio());
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 提取申请
     */
    public function extractApply()
    {
        if ($this->request->isPost()) {
            $userBankCardId = $this->request->post('bank_card_id');
            //申请金额
            $applyAmount = $this->request->post('apply_amount');
            //安全密码
            $safePwd = $this->request->post('safe_pwd');
            if (!$userBankCardId || !is_numeric($userBankCardId)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$applyAmount || !is_numeric($applyAmount)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$safePwd) {
                $this->error(__('Invalid parameters'));
            }
            $userData = $this->auth->getUser();
            //判断是否能提现
            if ($userData['can_extract'] != 1) {
                $this->error(__('提现暂未开启'));
            }
            //判断是否已审核
            if ($userData['is_audit'] != 1) {
                $this->error(__('账户未实名，请先申请实名认证'));
            }
            //判断是否为有效用户
            if ($userData['is_valid'] != 1) {
                $this->error(__('请先参与项目投资'));
            }
            //判断是否有余额可提取
            if ($userData['token_value'] <= 0) {
                $this->error(__('The balance is insufficient to withdraw'));
            }
            if ($userData['token_value'] < $applyAmount) {
                $this->error(__('The balance is insufficient to withdraw'));
            }
            if ($applyAmount != intval($applyAmount)) {
                $this->error('仅允许整数位提现');
            }
            $data = $this->logic->extractApply($this->auth->getUser(), $userBankCardId, $applyAmount, $safePwd);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取提现申请记录
     */
    public function getExtractApplyList()
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
            $this->success('success', $this->logic->getExtractApplyList($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 余额转账
     */
    public function balanceTransfer()
    {
        if ($this->request->isPost()) {
            //收款手机号
            $acceptMobile = $this->request->post('accept_mobile');
            //转账金额
            $transferAmount = $this->request->post('transfer_amount');
            //安全密码
            $safePwd = $this->request->post('safe_pwd');
            if (!$acceptMobile) {
                $this->error(__('Invalid parameters'));
            }
            if (!$transferAmount || !is_numeric($transferAmount)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$safePwd) {
                $this->error(__('Invalid parameters'));
            }
            $userData = $this->auth->getUser();
            //判断是否可转账
            if ($userData['can_transfer'] != 1) {
                $this->error(__('转账暂未开启'));
            }
            if ($acceptMobile == $userData['mobile']) {
                $this->error(__('请勿给自己转账'));
            }
            //判断是否有余额可转账
            if ($userData['token_value'] <= 0) {
                $this->error('余额不足，无法转账');
            }
            if ($userData['token_value'] < $transferAmount) {
                $this->error('余额不足，无法转账');
            }
            if ($transferAmount != intval($transferAmount)) {
                $this->error('仅允许整数位转账');
            }
            $data = $this->logic->balanceTransfer($this->auth->getUser(), $acceptMobile, $transferAmount, $safePwd);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }

}