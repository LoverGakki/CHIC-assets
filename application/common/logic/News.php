<?php

namespace app\common\logic;

use think\Model;

/**
 * 新闻相关
 * Class Banner
 * @package app\common\logic
 */
class News extends Model
{
    public $model = null;

    public $return = [
        'code' => 0,
        'msg' => '',
        'data' => null
    ];

    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
        $this->model = \think\Loader::model('News');
    }

    /**
     * 获取新闻类型
     * @return array
     */
    public function getNewsType(): array
    {
        $data = $this->model->getNewsType();
        $rows = [];
        foreach ($data as $k => $v) {
            $rows[] = [
                'type_number' => $k,
                'type_name' => $v
            ];
        }
        return $rows;
    }

    /**
     * 获取新闻列表
     * @param $page
     * @param $pageSize
     * @param $newsType
     * @param $isHeadlines
     * @return array
     */
    public function getNewsList($page, $pageSize, $newsType, $isHeadlines): array
    {
        $limit = ($page - 1) * $pageSize;
        $where = [
            'status' => 1,
            //'news_type' => $newsType
        ];
        if ($newsType) {
            $where['news_type'] = $newsType;
        }
        if ($isHeadlines) {
            $where['is_headlines'] = $isHeadlines;
        }
        $total = $this->model
            ->where($where)
            ->count();
        $list = $this->model
            ->where($where)
            ->order('create_time', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['news_id'];
                $row[$k]['news_type'] = $v['news_type'];
                $row[$k]['name'] = $v['name'];
                $row[$k]['brief_introduction'] = $v['brief_introduction'];
                $row[$k]['cover_image'] = $v['cover_image'];
                $row[$k]['video'] = $v['video'];
                $row[$k]['content_type'] = $v['content_type'];
                $row[$k]['is_headlines'] = $v['is_headlines'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    /**
     * 获取新闻详情
     * @param $newsId
     * @return array
     */
    public function getNewsDetail($newsId): array
    {
        $data = $this->model->where([
            'news_id' => $newsId,
            'status' => 1,
        ])->find();
        $row = [];
        if ($data) {
            $row['id'] = $data['news_id'];
            $row['news_type'] = $data['news_type'];
            $row['name'] = $data['name'];
            $row['brief_introduction'] = $data['brief_introduction'];
            $row['cover_image'] = $data['cover_image'];
            $row['video'] = $data['video'];
            $row['content'] = $data['content'];
            $row['content_type'] = $data['content_type'];
            $row['is_headlines'] = $data['is_headlines'];
            $row['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
        }
        return $row;
    }

}