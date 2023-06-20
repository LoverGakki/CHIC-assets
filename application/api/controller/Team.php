<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 团队相关
 * Class Team
 * @package app\api\controller
 */
class Team extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Team', 'logic');
    }

    /**
     * 获取团队相关数据
     */
    public function getTeamRelatedData()
    {
        if ($this->request->isGet()) {
            $userData = $this->auth->getUser();
            $this->success('success', [
                //团队业绩
                'team_performance' => $userData['team_performance'],
                //推荐人数
                'direct_drive_number' => $userData['direct_drive_number'],
                //团队人数
                'team_number' => $userData['team_performance'],
            ]);
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取下级直推用户列表
     */
    public function getDirectReferralList()
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
            $this->success('success', $this->logic->getDirectReferralList($this->auth->id, $page, $pageSize));
        }
        $this->error(__('Incorrect request mode'));
    }

}