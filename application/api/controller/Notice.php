<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 公告相关
 * Class Notice
 * @package app\api\controller
 */
class Notice extends Api
{
    protected $noNeedLogin = ['getIndexNoticeList', 'getNoticeList'];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Notice', 'logic');
    }

    /**
     * 获取首页公告列表
     */
    public function getIndexNoticeList()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getIndexNoticeList());
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取公告列表
     */
    public function getNoticeList()
    {
        if ($this->request->isPost()) {
            $page = $this->request->post('page', 1);
            $pageSize = $this->request->post('page_size', 10);
            if (!$page || !is_numeric($page)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$pageSize || !is_numeric($pageSize)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getNoticeList($page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

}