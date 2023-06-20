<?php

namespace app\common\logic;

use think\Model;

/**
 * 公告相关
 * Class Banner
 * @package app\common\logic
 */
class Notice extends Model
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
        $this->model = \think\Loader::model('Notice');
    }

    /**
     * 获取首页公告列表
     * @return array[]
     */
    public function getIndexNoticeList(): array
    {
        $time = time();
        $list = $this->model
            ->where([
                'status' => 1,
                'is_index' => 1,
                'start_time' => ['<', $time],
                'end_time' => ['>=', $time],
            ])
            ->order('create_time', 'desc')
            ->select();
        $rows = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $rows[$k]['id'] = $v['notice_id'];
                $rows[$k]['title'] = $v['title'];
                $rows[$k]['content'] = $v['content'];
                $rows[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'rows' => $rows
        ];

    }

    /**
     * 获取公告列表
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getNoticeList($page, $pageSize): array
    {
        $where = [
            'status' => 1
        ];
        $total = $this->model
            ->where($where)
            ->count();
        $list = $this->model
            ->where($where)
            ->order('create_time', 'desc')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $rows = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $rows[$k]['id'] = $v['notice_id'];
                $rows[$k]['title'] = $v['title'];
                $rows[$k]['content'] = $v['content'];
                $rows[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $rows
        ];
    }

}