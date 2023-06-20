<?php

namespace app\admin\controller\commands;

use think\Db;
use think\Queue;
use think\Validate;
use think\Exception;
use app\common\library\Log;
use app\common\controller\Backend;

use app\common\model\User;
use app\common\logic\Common;
use app\common\model\Order as OrderModel;
use app\common\model\UserLevelConfig;
use app\common\model\OrderReleaseRecord;
use app\common\model\ProjectRebatesRateRecord;

//0点释放投资单队列消费端
use app\common\job\OrderRelease;

//投资单操作
class Order extends Backend
{
    protected $noNeedLogin = '*';

    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 验证是否为日期
     * @param $date
     * @return bool
     */
    private function validateDate($date): bool
    {
        $validate = new Validate([
            'date' => 'require|date',
        ]);
        return $validate->check([
            'date' => $date
        ]);
    }

    //投资订单释放
    public function orderRelease()
    {
        //指定释放日期
        $date = $this->request->get('date');
        $time = $this->validateDate($date) ? strtotime(date('Y-m-d', strtotime($date))) + 1 : time();
        $where = [
            //收益中
            'status' => 1,
            //未到期
            'is_expired' => 0,
            //到期时间大于当前时间
            'expired_time' => ['>', $time],
            //下次施放时间小于当前时间
            'next_release_time' => ['<=', $time],
            //'rebate_method' => 3,
        ];
        $orderTotal = (new OrderModel())->where($where)->count();
        //已写入队列总条数
        $total = 0;
        $page = 1;
        $pageSize = 2;
        do {
            $count = $this->orderReleaseOperate((new OrderModel())
                ->where($where)
                ->order('create_time', 'asc')
                ->limit(($page - 1) * $pageSize, $pageSize)
                ->select(), $time);
            $total += $count;
            $page++;
            if ($total >= $orderTotal) {
                break;
            }
        } while ($count > 0);
        return json(['code' => 1, 'msg' => '操作成功']);
    }

    //订单释放写入队列
    private function orderReleaseOperate($orderData, $time)
    {
        $count = 0;
        if (empty($orderData)) {
            return $count;
        }
        Db::startTrans();
        try {
            foreach ($orderData as $k => $v) {
                $vData = $v->toArray();
                $vData['release_operate_time'] = $time;
                //操作条数累加
                $count++;
                //加入队列
                Queue::push(OrderRelease::class, $vData, 'OrderReleaseQueue');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            (new Log())->error('orderReleaseOperate：' . $e->getMessage());
            return 0;
        }
        return $count;
    }

    //余额宝日收益实现
    public function balanceDailyReturn()
    {
        $result = ['code' => 0, 'msg' => '操作失败'];
        $time = time();
        Db::startTrans();
        try {
            $levelConfigData = (new UserLevelConfig())->where('status', 1)->select();
            if (!$levelConfigData) {
                throw new Exception("用户等级配置数据不存在");
            }
            $levelConfigData = array_column(collection($levelConfigData)->toArray(), 'balance_daily_return_rate', 'level_number');
            $userData = (new User())->where([
                'is_audit' => 1,
                'is_valid' => 1,
                'status' => 'normal',
                'yuebao_token_value' => ['>', 0]
            ])->select();
            if (empty($userData)) {
                throw new Exception("符合条件的用户数据不存在", 1);
            }
            foreach ($userData as $k => $v) {
                if (array_key_exists($v['role_level'], $levelConfigData) && $levelConfigData[$v['role_level']] > 0) {
                    Common::changeUserYuebaoTokenValue($v, 1, 2, ($v['yuebao_token_value'] * $levelConfigData[$v['role_level']] / 100), $levelConfigData[$v['role_level']]);
                    //Common::changeUserTokenValue($v, 'token_value', 1, 7, ($v['token_value'] * $levelConfigData[$v['role_level']]));
                }
            }
            $result['code'] = 1;
            $result['msg'] = '操作成功';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $result['msg'] = $e->getMessage();
            } else {
                (new Log())->error('balanceDailyReturn：' . $e->getMessage());
            }
        }
        return json($result);
    }


    //用户入金每日释放（暂无用，使用队列）
    public function orderDailyRelease()
    {
        $result = ['code' => 0, 'msg' => '操作失败'];
        $time = time();
        Db::startTrans();
        try {
            //获取用户订单数据
            $orderData = (new OrderModel())
                ->where([
                    //收益中
                    'status' => 1,
                    /*//返利方式（每日返还）不需要
                    'rebate_method' => 1,*/
                    //未到期
                    'is_expired' => 0,
                    //到期时间大于当前时间
                    'expired_time' => ['>', $time],
                    //下次施放时间小于当前时间
//                    'next_release_time' => ['<=', $time]
                ])->order('create_time', 'asc')
                ->limit(0, 50)
                ->select();
            if ($orderData) {
                $orderPars = [];
                foreach ($orderData as $k => $v) {
//                    if ($v['already_periods'] < $v['period'] && $v['next_release_time'] && $v['next_release_time'] <= $time) {
                    if ($v['already_periods'] < $v['period'] && $v['next_release_time']) {
                        $orderPars[$k]['order_id'] = $v['order_id'];
                        //获取用户数据
                        $userData = (new User())->where([
                            'id' => $v['user_id'],
                            'status' => 'normal'
                        ])->find();
                        if (!$userData) {
                            $orderPars[$k]['status'] = 3;
                            $orderPars[$k]['cancel_time'] = $time;
                            $orderPars[$k]['remark'] = '该投资单用户数据不存在或用户已被拉黑';
                        } else {
                            //获取该期释放利率
                            $dailyRebatesRate = $v['daily_rebates_rate'];
                            //判断能否修改利率
                            if ($v['can_change_rebates_rate'] == 1) {
                                //获取该项目最新日化利率
                                $projectRebatesRateRecordData = (new ProjectRebatesRateRecord())->where([
                                    'project_id' => $v['project_id'],
                                    'start_time' => ['<', $time],
                                    'end_time' => ['>=', $time],
                                ])->order('create_time', 'desc')->find();
                                $dailyRebatesRate = $projectRebatesRateRecordData && $projectRebatesRateRecordData['daily_rebates_rate'] > 0 ? $projectRebatesRateRecordData['daily_rebates_rate'] : $dailyRebatesRate;
                            }
                            //当前释放利息数量
                            $nowReleaseAmount = $dailyRebatesRate / 100 * $v['investment_amount'];

                            //根据返利方式操作
                            switch ($v['rebate_method']) {
                                case 1: //每日返还
                                    //新产出数量
                                    $orderPars[$k]['output'] = $v['output'] + $nowReleaseAmount;
                                    //新已释放期数
                                    $newAlreadyPeriods = $v['already_periods'] + 1;
                                    //已释放期数
                                    $orderPars[$k]['already_periods'] = $newAlreadyPeriods;
                                    //新已释放期数==总释放期数（最后一期）
                                    if ($newAlreadyPeriods == $v['period']) {
                                        //改为已到期
                                        $orderPars[$k]['is_expired'] = 1;
                                        //下次释放时间改为空
                                        $orderPars[$k]['next_release_time'] = null;
                                        //到期时间改为当前时间
                                        $orderPars[$k]['expired_time'] = $time;
                                        //状态改为已结束
                                        $orderPars[$k]['status'] = 2;
                                        $orderPars[$k]['pick_up_principal'] = 1;
                                        $orderPars[$k]['pick_up_output'] = 1;
                                        $orderPars[$k]['pick_up_time'] = $time;
                                    } else {
                                        //下次释放时间
                                        $orderPars[$k]['next_release_time'] = strtotime(date('Y-m-d', $time)) + 86400;
                                    }

                                    //释放记录参数
                                    $releaseRecordPars = [
                                        'user_id' => $userData['id'],
                                        'order_id' => $v['order_id'],
                                        //释放的利息
                                        'release_number' => $nowReleaseAmount,
                                        'release_ratio' => $dailyRebatesRate,
                                        'release_periods' => $newAlreadyPeriods,
                                        'old_number' => $v['output'] ?: 0,
                                        'new_number' => $orderPars[$k]['output'],
                                        //返还的本金
                                        'return_principal' => 0,
                                        //是否返还利息
                                        'is_return_interest' => 0,
                                        //是否返还本金
                                        'is_return_principal' => 0,
                                        'type' => 1,
                                    ];

                                    //返还利息
                                    Common::changeUserTokenValue($userData, 'token_value', 1, 4, $nowReleaseAmount);
                                    //更新团队收益
                                    if (!$this->teamRelatedDataUpdate($userData, $nowReleaseAmount)) {
                                        throw new Exception('用户：' . $userData['id'] . '：修改团队收益数量错误');
                                    }
                                    $releaseRecordPars['is_return_interest'] = 1;
                                    //判断分红方式
                                    if (in_array($v['dividend_method'], [1, 3])) { //本息同返
                                        //返还的本金（判断是否为最后一期）
                                        $returnPrincipal = $newAlreadyPeriods == $v['period'] ? $v['actual_pay_investment_amount'] - $v['already_return_principal'] : $v['actual_pay_investment_amount'] / $v['period'];
                                        //返还当日本金
                                        Common::changeUserTokenValue((new User())->where(['id' => $v['user_id']])->find(), 'token_value', 1, 5, $returnPrincipal);
                                        //已返还本金
                                        $orderPars[$k]['already_return_principal'] = $v['already_return_principal'] + $returnPrincipal;
                                        $releaseRecordPars['return_principal'] = $returnPrincipal;
                                        $releaseRecordPars['is_return_principal'] = 1;
                                    } else { //先息后本
                                        //判断是否为最后一期
                                        if ($newAlreadyPeriods == $v['period']) {
                                            //返还所有本金
                                            Common::changeUserTokenValue((new User())->where(['id' => $v['user_id']])->find(), 'token_value', 1, 5, $v['actual_pay_investment_amount']);
                                            //已返还本金
                                            $orderPars[$k]['already_return_principal'] = $v['actual_pay_investment_amount'];
                                            $releaseRecordPars['return_principal'] = $v['actual_pay_investment_amount'];
                                            $releaseRecordPars['is_return_principal'] = 1;
                                        }
                                    }

                                    //创建释放记录
                                    if (!(new OrderReleaseRecord())->save($releaseRecordPars)) {
                                        throw new Exception("创建订单释放记录错误");
                                    }
                                    break;
                                case 2: //每月返还
                                    //判断是否释放完
                                    if ($v['already_periods'] == $v['period']) {

                                    } else {

                                        //新产出数量
                                        $orderPars[$k]['output'] = $v['output'] + $nowReleaseAmount;
                                        //新已释放期数
                                        $newAlreadyPeriods = $v['already_periods'] + 1;
                                        //已释放期数
                                        $orderPars[$k]['already_periods'] = $newAlreadyPeriods;


                                    }
                                    break;
                                default:
                                    break;
                            }


                        }
                    }
                }
                //修改状态
                $orderPars = array_values($orderPars);
                if ($orderPars && !(new OrderModel())->saveAll($orderPars)) {
                    throw new Exception("入金订单数据修改错误");
                }
                $result['code'] = 1;
                $result['msg'] = '操作成功';
            } else {
                $result['code'] = 1;
                $result['msg'] = '暂无需要操作入金订单记录';
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $result['msg'] = $e->getMessage();
            } else {
                (new Log())->error('orderDailyRelease：' . $e->getMessage());
            }
        }
        return json($result);
    }

    /**
     * 更新团队累计收益相关数据（暂无用，使用队列）
     * @param $userData
     * @param $nowReleaseAmount
     * @return bool
     * @throws Exception
     */
    private function teamRelatedDataUpdate($userData, $nowReleaseAmount): bool
    {
        if ($referrerLinkArr = Common::getUserAscReferrerLink($userData)) {
            foreach ($referrerLinkArr as $k => $v) {
                $superiorEditPars = [];
                //团队上级数据
                $superiorUserData = (new User())->where('invite_code', $v)->find();
                if ($superiorUserData) {
                    //团队累计收益数量
                    $superiorEditPars['team_cumulative_earnings_value'] = $superiorUserData['team_cumulative_earnings_value'] + $nowReleaseAmount;
                }
                //三级分销返利（前三代直推且投资过才有）
                if ($userData['status'] == 'normal' && $userData['is_audit'] == 1 && $userData['is_valid'] == 1 && $k < 3) {
                    switch ($k) {
                        case 0:
                            //分销返利比例
                            $distributionRebateRatio = 16;
                            break;
                        case 1:
                            $distributionRebateRatio = 5;
                            break;
                        default:
                            $distributionRebateRatio = 1;
                            break;
                    }
                    Common::changeUserTokenValue($superiorUserData, 'token_value', 1, 9, ($distributionRebateRatio / 100 * $nowReleaseAmount));
                }
                if ($superiorEditPars && !$superiorUserData->save($superiorEditPars)) {
                    return false;
                }
            }
        }
        return true;
    }

}