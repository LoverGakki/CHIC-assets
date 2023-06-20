<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 项目相关
 * Class Project
 * @package app\api\controller
 */
class Project extends Api
{
    protected $noNeedLogin = ['getProjectTypeList', 'getProjectList', 'getProjectDetail', 'getProjectCoupons'];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Project', 'logic');
    }

    /**
     * 获取项目类型
     */
    public function getProjectTypeList()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getProjectTypeList());
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取项目列表
     */
    public function getProjectList()
    {
        if ($this->request->isPost()) {
            $page = $this->request->post('page', 1, 'intval');
            $pageSize = $this->request->post('page_size', 10, 'intval');
            $projectTypeId = $this->request->post('project_type_id');
            if (!$page || !is_numeric($page)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$pageSize || !is_numeric($pageSize)) {
                $this->error(__('Invalid parameters'));
            }
            if ($projectTypeId && !is_numeric($projectTypeId)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getProjectList($page, $pageSize, $projectTypeId));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取项目详情
     */
    public function getProjectDetail()
    {
        if ($this->request->isPost()) {
            $projectId = $this->request->post('project_id');
            if (!$projectId || !is_numeric($projectId)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getProjectDetail($projectId));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取项目优惠券列表
     */
    public function getProjectCoupons()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getProjectCoupons());
        }
        $this->error(__('Incorrect request mode'));
    }
}