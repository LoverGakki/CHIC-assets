<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 余额宝
 * Class Yuebao
 * @package app\api\controller
 */
class Yuebao extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Yuebao', 'logic');
    }

    /**
     * 获取余额宝统计
     */
    public function getYuebaoStatistics()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getYuebaoStatistics($this->auth->getUser()));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取转入余额宝最小金额
     */
    public function getIntoAmountMin()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getIntoAmountMin());
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 转入余额宝
     */
    public function yuebaoInto()
    {
        if ($this->request->isPost()) {
            $intoAmount = $this->request->post('into_amount');
            if (!$intoAmount || !is_numeric($intoAmount) || $intoAmount <= 0) {
                $this->error(__('Invalid parameters'));
            }
            $userData = $this->auth->getUser();
            if ($userData['token_value'] < $intoAmount) {
                $this->error('余额不足，无法转入');
            }
            $data = $this->logic->yuebaoInto($userData, $intoAmount);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取余额宝余额变动记录
     */
    public function getYuebaoBalanceList()
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
            $this->success('success', $this->logic->getYuebaoBalanceList($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 余额宝转出
     */
    public function yuebaoOut()
    {
        if ($this->request->isPost()) {
            $outAmount = $this->request->post('out_amount');
            if (!$outAmount || !is_numeric($outAmount) || $outAmount <= 0) {
                $this->error(__('Invalid parameters'));
            }
            $userData = $this->auth->getUser();
            if ($userData['yuebao_token_value'] < $outAmount) {
                $this->error('余额宝余额不足，无法转出');
            }
            $data = $this->logic->yuebaoOut($userData, $outAmount);
            if ($data['code'] == 1) {
                $this->success($data['msg'], $data['data']);
            }
            $this->error(__($data['msg']));
        }
        $this->error(__('Incorrect request mode'));
    }
}