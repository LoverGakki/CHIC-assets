<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * banner相关
 * Class Banner
 * @package app\api\controller
 */
class Banner extends Api
{
    protected $noNeedLogin = ['getBannerList', 'getBannerDetail'];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('Banner', 'logic');
    }

    /**
     * 获取banner列表
     */
    public function getBannerList()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getBannerList());
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取banner详情
     */
    public function getBannerDetail()
    {
        $bannerId = $this->request->request('banner_id');
        $this->success('success', $this->logic->getBannerDetail($bannerId));
    }

}