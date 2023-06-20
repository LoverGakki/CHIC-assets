<?php

namespace app\common\logic;

use think\Model;

/**
 * 轮播图相关
 * Class Banner
 * @package app\common\logic
 */
class Banner extends Model
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
        $this->model = \think\Loader::model('Banner');
    }

    /**
     * 获取banner列表
     * @return array
     */
    public function getBannerList(): array
    {
        $total = $this->model
            ->where([
                'status' => 1
            ])
            ->count();
        $list = $this->model
            ->where([
                'status' => 1
            ])
            ->order('create_time', 'desc')
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['banner_id'];
                $row[$k]['name'] = $v['name'];
                $row[$k]['image'] = $v['image'];
                $row[$k]['url'] = $v['url'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    public function getBannerDetail($bannerId)
    {
        $data = $this->model->where([
            'banner_id' => $bannerId,
            'status' => 1,
        ])->find();
        $row = [];
        if ($data) {
            $row['id'] = $data['banner_id'];
            $row['name'] = $data['name'];
            $row['image'] = $data['image'];
            $row['description'] = $data['description'];
            $row['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
        }
        return $row;
    }

}