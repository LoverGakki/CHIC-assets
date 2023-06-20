<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 前端版本控制
 * Class Version
 * @package app\api\controller
 */
class Version extends Api
{
    protected $noNeedLogin = ['getNowVersion'];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Version', 'logic');
    }

    /**
     * 获取当前前端版本
     */
    public function getNowVersion()
    {
        if ($this->request->isPost()) {
            $osName = $this->request->post('osName');
            if (!$osName || !in_array($osName, ['android', 'ios'])) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getNowVersion($osName));
        }
        $this->error(__('Incorrect request mode'));
    }

}