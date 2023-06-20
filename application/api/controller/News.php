<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 新闻相关
 * Class Property
 * @package app\api\controller
 */
class News extends Api
{
    protected $noNeedLogin = ['getNewsType', 'getNewsList', 'getNewsDetail'];
    protected $noNeedRight = '*';

    private $logic = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->logic = \think\Loader::model('News', 'logic');
    }

    /**
     * 获取新闻类型
     */
    public function getNewsType()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->logic->getNewsType());
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取新闻列表
     */
    public function getNewsList()
    {
        if ($this->request->isPost()) {
            $page = $this->request->post('page', 1, 'intval');
            $pageSize = $this->request->post('page_size', 10, 'intval');
            $newsType = $this->request->post('news_type');
            $isHeadlines = $this->request->post('is_headlines');
            if (!$page || !is_numeric($page)) {
                $this->error(__('Invalid parameters'));
            }
            if (!$pageSize || !is_numeric($pageSize)) {
                $this->error(__('Invalid parameters'));
            }
            if ($newsType && !in_array($newsType, [1, 2, 3])) {
                $this->error(__('Invalid parameters'));
            }
            if ($isHeadlines && $isHeadlines != 1) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getNewsList($page, $pageSize, $newsType, $isHeadlines));
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取新闻详情
     */
    public function getNewsDetail()
    {
        if ($this->request->isPost()) {
            $newsId = $this->request->request('news_id');
            if (!$newsId || !is_numeric($newsId)) {
                $this->error(__('Invalid parameters'));
            }
            $this->success('success', $this->logic->getNewsDetail($newsId));
        }
        $this->error(__('Incorrect request mode'));
    }

}