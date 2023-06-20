<?php

namespace app\admin\controller;

use think\Db;
use fast\Date;
use app\common\controller\Backend;
use app\admin\model\Admin;
use app\admin\model\User;
use app\common\model\Order;
use app\common\model\Notice;
use app\common\model\UserToken;
use app\common\model\Attachment;
use app\common\model\UserSignRecord;
use app\common\model\UserRechargeRecord;
use app\common\model\ExtractOrder;
use app\common\model\InvestmentProject;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $column = [];
        $whereArr = [];
        if ($upperUserData = (new User())->where('mobile', $this->auth->mobile)->find()) {
            $whereArr['referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
        } elseif ($upperUserData = (new User())->where('mobile', $this->auth->username)->find()) {
            $whereArr['referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
        }
        $starttime = Date::unixtime('day', -6);
        $endtime = Date::unixtime('day', 0, 'end');
        $joinlist = Db("user")->where('jointime', 'between time', [$starttime, $endtime])->where($whereArr)
            ->field('jointime, status, COUNT(*) AS nums, DATE_FORMAT(FROM_UNIXTIME(jointime), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->select();
        for ($time = $starttime; $time <= $endtime;) {
            $column[] = date("Y-m-d", $time);
            $time += 86400;
        }
        $userlist = array_fill_keys($column, 0);
        foreach ($joinlist as $k => $v) {
            $userlist[$v['join_date']] = $v['nums'];
        }

        $dbTableList = Db::query("SHOW TABLE STATUS");
        $addonList = get_addon_list();
        $totalworkingaddon = 0;
        $totaladdon = count($addonList);
        foreach ($addonList as $index => $item) {
            if ($item['state']) {
                $totalworkingaddon += 1;
            }
        }
        //获取最新公告
        $noticeData = (new Notice())->where([
            'status' => 1,
            'start_time' => ['<=', time()],
            'end_time' => ['>', time()],
        ])->order('create_time', 'desc')->find();

        $todaySTime = strtotime(date('Y-m-d', time()));
        $todayETime = $todaySTime + 86399;
        //获取今日签到
        $todaySignNumber = (new UserSignRecord())->where('sign_time', 'between', [$todaySTime, $todayETime])->count();
        //获取今日注册
        $registerMemberNumber = (new User())->where('jointime', 'between', [$todaySTime, $todayETime])->count();
        //获取在线会员
        $onlineMemberNumber = (new UserToken())->where('expiretime', '>', time())->count();
        //今日已投
        $todayInvestedNumber = (new  Order())->where('token_pay_time', 'between', [$todaySTime, $todayETime])->count();
        //充值订单数
        $rechargeOrderNumber = (new UserRechargeRecord())->where('create_time', 'between', [$todaySTime, $todayETime])->where('status', 'in', '1,2')->count();
        //提现单数
        $withdrawOrderNumber = (new ExtractOrder())->where('create_time', 'between', [$todaySTime, $todayETime])->where('status', 'in', '1,2,3')->count();

        //加入项目数
        $joinProjectCount = (new InvestmentProject())->count();
        //注册会员数
        $totalRegisterMember = (new User())->count();
        //在线项目数
        $onlineProjectCount = (new InvestmentProject())->where('status', 1)->count();
        //充值订单数
        $totalRechargeOrderNumber = (new UserRechargeRecord())->where('status', 'in', '1,2')->count();
        //提现订单数
        $totalWithdrawOrderNumber = (new ExtractOrder())->where('status', 'in', '1,2,3')->count();
        //已投项目金额
        $investProjectAmount = (new Order())->where('status', 'in', '1,2')->sum('investment_amount');

        $todaydepositsamount = (new UserRechargeRecord())->where('create_time', 'between', [$todaySTime, $todayETime])->where('status', 'in', '1,2')->sum('recharge_amount');
        $todaycontributionsamount = (new ExtractOrder())->where('create_time', 'between', [$todaySTime, $todayETime])->where('status', 'in', '1,2,3')->sum('extract_amount');
        $totaldepositsamount = (new UserRechargeRecord())->where('status', 'in', '1,2')->sum('recharge_amount');
        $totalcontributionsamount = (new ExtractOrder())->where('status', 'in', '1,2,3')->sum('extract_amount');

        $this->view->assign([
            'totaluser' => User::count(),
            'totaladdon' => $totaladdon,
            'totaladmin' => Admin::count(),
            'totalcategory' => \app\common\model\Category::count(),
            'todayuserlogin' => User::whereTime('logintime', 'today')->where($whereArr)->count(),
            'todayusersignup' => User::whereTime('jointime', 'today')->where($whereArr)->count(),
            //今日新增
            'todayuservalid' => User::whereTime('valid_time', 'today')->where($whereArr)->count(),
            //今日激活
            'todayuseractivated' => User::whereTime('activation_time', 'today')->where($whereArr)->count(),
            //三日注册
            'threednu' => User::whereTime('jointime', '-3 days')->where($whereArr)->count(),
            //三日新增
            'threeuservalid' => User::whereTime('valid_time', '-3 days')->where($whereArr)->count(),
            //三日激活
            'threeuseractivated' => User::whereTime('activation_time', '-3 days')->where($whereArr)->count(),
            //'sevendau' => User::whereTime('jointime|logintime|prevtime', '-7 days')->where($whereArr)->count(),
            //'thirtydau' => User::whereTime('jointime|logintime|prevtime', '-30 days')->where($whereArr)->count(),
            //七日注册
            'sevendnu' => User::whereTime('jointime', '-7 days')->where($whereArr)->count(),
            //七日新增
            'sevenuservalid' => User::whereTime('valid_time', '-7 days')->where($whereArr)->count(),
            //七日激活
            'sevenuseractivated' => User::whereTime('activation_time', '-7 days')->where($whereArr)->count(),
            //月度注册
            'thirtydnu' => User::whereTime('jointime', '-30 days')->where($whereArr)->count(),
            //月度新增
            'thirtyuservalid' => User::whereTime('valid_time', '-30 days')->where($whereArr)->count(),
            //月度激活
            'thirtyuseractivated' => User::whereTime('activation_time', '-30 days')->where($whereArr)->count(),
            //总新增
            'totaluservalid' => User::where('is_valid', '1')->where($whereArr)->count(),
            //总激活
            'totaluseractivated' => User::where('is_activated', '1')->where($whereArr)->count(),
            'dbtablenums' => count($dbTableList),
            'dbsize' => array_sum(array_map(function ($item) {
                return $item['Data_length'] + $item['Index_length'];
            }, $dbTableList)),
            'totalworkingaddon' => $totalworkingaddon,
            'attachmentnums' => Attachment::count(),
            'attachmentsize' => Attachment::sum('filesize'),
            'picturenums' => Attachment::where('mimetype', 'like', 'image/%')->count(),
            'picturesize' => Attachment::where('mimetype', 'like', 'image/%')->sum('filesize'),
            'admin' => $this->auth->getUserInfo(),
            'notice' => $noticeData ? $noticeData['content'] : '',
            'today' => [
                'today_sign' => $todaySignNumber,
                'register_member' => $registerMemberNumber,
                'online_member' => $onlineMemberNumber,
                'today_invested' => $todayInvestedNumber,
                'recharge_order_number' => $rechargeOrderNumber,
                'withdraw_order_number' => $withdrawOrderNumber,
            ],
            'history' => [
                'join_project_count' => $joinProjectCount,
                'total_register_member' => $totalRegisterMember,
                'online_project_count' => $onlineProjectCount,
                'total_recharge_order_number' => $totalRechargeOrderNumber,
                'total_withdraw_order_number' => $totalWithdrawOrderNumber,
                'invest_project_amount' => $investProjectAmount,
            ],
            'todayuserrecharge' => (new UserRechargeRecord())->where('create_time', 'between', [$todaySTime, $todayETime])->where('status', 'in', '1,2')->group('user_id')->count(),
            'totaluserrecharge' => (new UserRechargeRecord())->where('status', 'in', '1,2')->group('user_id')->count(),
            'todaydepositsamount' => $todaydepositsamount,
            'totaldepositsamount' => $totaldepositsamount,
            //提现人数
            'todayusercontributions' => (new ExtractOrder())->where('create_time', 'between', [$todaySTime, $todayETime])->where('status', 'in', '1,2,3')->group('user_id')->count(),
            'totalusercontributions' => (new ExtractOrder())->where('status', 'in', '1,2,3')->group('user_id')->count(),
            'todaycontributionsamount' => $todaycontributionsamount,
            'totalcontributionsamount' => $totalcontributionsamount,
            'todaybalance' => $todaydepositsamount - $todaycontributionsamount,
            'totalbalance' => $totaldepositsamount - $totalcontributionsamount,
        ]);

        $this->assignconfig('column', array_keys($userlist));
        $this->assignconfig('userdata', array_values($userlist));

        return $this->view->fetch();
    }

}
