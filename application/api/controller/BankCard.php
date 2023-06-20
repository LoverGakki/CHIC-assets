<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 用户银行卡相关
 * Class BankCard
 * @package app\api\controller
 */
class BankCard extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('BankCard', 'logic');
    }

    /**
     * 用户绑定银行卡
     */
    public function bindBankCard()
    {
        if ($this->request->isPost()) {
            $bankName = $this->request->request('bank_name');
            $cardNumber = $this->request->request('card_number');
            $accountBankSite = $this->request->request('account_bank_site');
            if (!$bankName) {
                $this->error(__('Invalid parameters'));
            }
            if (!$cardNumber) {
                $this->error(__('Invalid parameters'));
            }
            $userData = $this->auth->getUser();
            if ($userData['is_audit'] != 1) {
                $this->error(__('账户未实名，请先申请实名认证'));
            }
            $data = $this->logic->bindBankCard($this->auth->id, [
                'bank_name' => $bankName,
                'card_number' => $cardNumber,
                'account_bank_site' => $accountBankSite,
                'card_holder_name' => $userData['real_name'],
            ]);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 用户删除绑定银行卡
     */
    public function userDelBankCard()
    {
        if ($this->request->isPost()) {
            $userBankCardId = $this->request->request('id');
            if (!$userBankCardId || !is_numeric($userBankCardId)) {
                $this->error(__('Invalid parameters'));
            }
            $data = $this->logic->userDelBankCard($this->auth->id, $userBankCardId);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 用户获取银行卡列表
     */
    public function userBankCardList()
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
            $this->success('success', $this->logic->userBankCardList($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }
}