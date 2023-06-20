<?php

namespace app\common\logic;

use think\Model;
use app\common\model\ProjectType;

/**
 * 用户优惠券相关
 * Class UserCoupons
 * @package app\common\logic
 */
class UserCoupons extends Model
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
        $this->model = \think\Loader::model('UserCoupons');
    }

    /**
     * 获取用户优惠券列表
     * @param $userId
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getUserCouponsList($userId, $page, $pageSize): array
    {
        $limit = ($page - 1) * $pageSize;
        $total = $this->model
            ->where([
                'user_id' => $userId,
                'status' => 1
            ])
            ->count();
        $list = $this->model
            ->where([
                'user_id' => $userId,
                'status' => 1
            ])
            ->order('create_time', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['user_coupons_id'];
                $row[$k]['name'] = $v['name'];
                $row[$k]['coupons_amount'] = $v['coupons_number'];
                $row[$k]['project_type_ids'] = explode(',', $v['project_type_ids']);
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    /**
     * 获取可使用的优惠券列表（支付订单用）
     * @param $userId
     * @param $projectTypeId
     * @param $investmentAmount
     * @return array[]
     */
    public function getCanUseCouponsList($userId, $projectTypeId, $investmentAmount): array
    {
        //获取项目类型数据
        $ProjectTypeData = (new ProjectType())->where([
            'id' => $projectTypeId,
            'status' => 1,
        ])->find();
        if (!$ProjectTypeData) {
            return [
                'rows' => []
            ];
        }
        $list = $this->model->where([
            'user_id' => $userId,
            'status' => 1,
            //购买金额超过可使用最低投资金额
            'coupons_use_limit' => ['<=', $investmentAmount],
            //过期时间大于当前时间
            'expiration_time' => ['>', time()],
            ['exp', db()->raw("FIND_IN_SET(" . $ProjectTypeData['id'] . ",project_type_ids)")]
        ])->order('create_time', 'desc')->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['user_coupons_id'];
                $row[$k]['name'] = $v['name'];
                $row[$k]['coupons_amount'] = $v['coupons_number'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'rows' => $row
        ];
    }

}