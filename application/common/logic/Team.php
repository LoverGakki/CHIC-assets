<?php

namespace app\common\logic;

use think\Model;

/**
 * 团队相关
 * Class Team
 * @package app\common\logic
 */
class Team extends Model
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
        $this->model = \think\Loader::model('User');
    }

    /**
     * 获取下级直推用户列表
     * @param $userId
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getDirectReferralList($userId, $page, $pageSize): array
    {
        $limit = ($page - 1) * $pageSize;
        $where = [
            'superior_user_id' => $userId,
            'status' => 'normal'
        ];
        $total = $this->model
            ->where($where)
            ->count();
        $list = $this->model
            ->where($where)
            ->order('jointime', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $rows = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $rows[$k]['id'] = $v['id'];
                $rows[$k]['mobile'] = $v['mobile'];
                $rows[$k]['jointime'] = date('Y-m-d', $v['jointime']);
            }
        }
        return [
            'total' => $total,
            'rows' => $rows
        ];
    }


}