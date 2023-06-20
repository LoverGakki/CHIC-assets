<?php

namespace app\admin\controller\commands;

use think\Db;
use think\Exception;
use app\common\library\Log;
use app\common\controller\Backend;

use app\common\model\UserCoupons;

//优惠券操作
class Coupons extends Backend
{
    protected $noNeedLogin = '*';

    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 用户优惠券到期验证
     * @return \think\response\Json
     */
    public function userCouponsExpiryVerification()
    {
        $result = ['code' => 0, 'msg' => '操作失败'];
        Db::startTrans();
        try {
            $userCouponsData = (new UserCoupons())
                ->where([
                    'status' => 1,
                    'expiration_time' => ['<', time()]
                ])
                ->order('create_time', 'asc')
                ->limit(0, 50)
                ->select();
            if ($userCouponsData) {
                foreach ($userCouponsData as $v) {
                    if (!$v->save(['status' => 3])) {
                        throw new Exception("修改优惠券状态错误", 1);
                    }
                }
                $result['code'] = 1;
                $result['msg'] = '操作成功';
            } else {
                $result['code'] = 1;
                $result['msg'] = '未有到期优惠券';
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $result['msg'] = $e->getMessage();
            } else {
                (new Log())->error('userCouponsExpiryVerification：' . $e->getMessage());
            }
        }
        return json($result);
    }
}