<?php

namespace app\common\library;

use think\Db;
use think\Hook;
use think\Model;
use think\Config;
use think\Request;
use think\Validate;
use think\Exception;
use fast\Random;
use app\common\logic\Im;
use app\common\logic\Common;
use app\common\model\User;
use app\common\model\UserRule;
use app\common\model\AppConfig;
use app\common\model\UserLoginLog;
use app\common\model\UserSignRecord;

class Auth
{
    protected static $instance = null;
    protected $_error = '';
    protected $_logined = false;
    protected $_user = null;
    protected $_token = '';
    //Token默认有效时长
    protected $keeptime = 2592000;
    protected $requestUri = '';
    protected $rules = [];
    //默认配置
    protected $config = [];
    protected $options = [];
    protected $allowFields = ['id', 'superior_user_id', 'invite_code', 'username', 'mobile', 'role_level', 'is_audit', 'audit_remark', 'is_valid', 'token_value', 'real_name', 'id_card_number', 'id_card_number', 'sign_time', 'im_token'];

    public function __construct($options = [])
    {
        if ($config = Config::get('user')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->options = array_merge($this->config, $options);
    }

    /**
     *
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 获取User模型
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 兼容调用user模型的属性
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_user ? $this->_user->$name : null;
    }

    /**
     * 兼容调用user模型的属性
     */
    public function __isset($name)
    {
        return isset($this->_user) ? isset($this->_user->$name) : false;
    }

    /**
     * 根据Token初始化
     *
     * @param string $token Token
     * @return boolean
     */
    public function init($token)
    {
        if ($this->_logined) {
            return true;
        }
        if ($this->_error) {
            return false;
        }
        $data = Token::get($token);
        if (!$data) {
            return false;
        }
        $user_id = intval($data['user_id']);
        if ($user_id > 0) {
            $user = User::get($user_id);
            if (!$user) {
                $this->setError('Account not exist');
                return false;
            }
            if ($user['status'] != 'normal') {
                $this->setError('Account is locked');
                return false;
            }
            $this->_user = $user;
            $this->_logined = true;
            $this->_token = $token;

            //初始化成功的事件
            Hook::listen("user_init_successed", $this->_user);

            return true;
        } else {
            $this->setError('You are not logged in');
            return false;
        }
    }

    /**
     * 获取新用户注册邀请码
     * @return string
     */
    private function getRegisterInviteCode(): string
    {
        $code = Random::alnum(8);
        //判断是否已有验证码
        $userData = User::getByInviteCode($code);
        if ($userData) {
            $this->getRegisterInviteCode();
        }
        return $code;
    }

    /**
     * 注册用户
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     * @param array $extend 扩展参数
     * @return boolean
     */
    public function register($username, $password, $email = '', $mobile = '', $extend = [])
    {
        // 检测用户名、昵称、邮箱、手机号是否存在
        if (User::getByUsername($username)) {
            $this->setError('Username already exist');
            return false;
        }
        if ($mobile && User::getByMobile($mobile)) {
            $this->setError('Mobile already exist');
            return false;
        }
        /*if (User::getByNickname($username)) {
            $this->setError('Nickname already exist');
            return false;
        }
        if ($email && User::getByEmail($email)) {
            $this->setError('Email already exist');
            return false;
        }*/
        if (!array_key_exists('safe_password', $extend) || !$extend['safe_password']) {
            $this->setError('The security password does not exist');
            return false;
        }
        //检测邀请码是否存在
        if (!array_key_exists('invite_code', $extend) || !$extend['invite_code']) {
            $this->setError('The invitation code does not exist');
            return false;
        }
        //邀请用户数据（暂时不校验是否已审核）
        $invitedUserData = User::getByInviteCode($extend['invite_code']);
        if (!$invitedUserData) {
            $this->setError('The inviting user does not exist');
            return false;
        }
        /*//判断邀请用户是否已审核
        if ($invitedUserData['is_audit'] != 1) {
            $this->setError('The inviting user failed the review');
            return false;
        }*/

        $ip = request()->ip();
        $time = time();

        $data = [
            'username' => $username,
            'password' => $password,
            'password_text' => $password,
            'email' => $email,
            'mobile' => $mobile,
            'level' => 1,
            'score' => 0,
            'avatar' => '',

            'superior_user_id' => $invitedUserData['id'],
            'is_audit' => 0,
            //注册用户的邀请码
            'invite_code' => $this->getRegisterInviteCode(),
            //角色等级
            'role_level' => 0,
            //是否有效用户
            'is_valid' => 0,
            //是否激活用户
            'is_activated' => 0,
            //代币余额
            'token_value' => 0,
            //余额宝余额
            'yuebao_token_value' => 0,
            //总投资额（总入）
            'invest_total_amount' => 0,
            //累计静态收益数量
            'cumulative_earnings_value' => 0,
            //累计直推奖励数量
            'cumulative_direct_reward_value' => 0,
            //累计动态奖励数量
            'cumulative_dynamic_reward_value' => 0,
            //累计充值金额
            'cumulative_recharge_amount' => 0,
            //累计提现金额
            'cumulative_withdrawals_amount' => 0,
            //累计余额宝收益
            'cumulative_yuebao_earnings_amount' => 0,

            //直推人数
            'direct_drive_number' => 0,
            //有效直推人数
            'valid_direct_drive_number' => 0,
            //激活直推人数
            'activated_direct_drive_number' => 0,
            //团队人数
            'team_number' => 1,
            //团队有效人数
            'team_effective_number' => 0,
            //团队激活人数
            'team_activated_number' => 0,
            //个人业绩
            'myself_performance' => 0,
            //大区业绩
            'daqu_performance' => 0,
            //小区业绩
            'plot_team_performance' => 0,
            //团队业绩
            'team_performance' => 0,
            //团队累计收益数量
            'team_cumulative_earnings_value' => 0,
            //团队累计动态奖励
            'team_cumulative_dynamic_reward_value' => 0,
            //邀请链路
            'referrer_link' => $invitedUserData['referrer_link'] . $invitedUserData['invite_code'] . ',',

            //能否转账
            'can_transfer' => 0,
            //能否提现
            'can_extract' => 0,
            //能否获取返利
            'can_get_rebates' => 1,
            //能否获取分红
            'can_get_dividends' => 1,
            //能否登录
            'can_login' => 1,
        ];
        $params = array_merge($data, [
            'nickname' => preg_match("/^1[3-9]{1}\d{9}$/", $username) ? substr_replace($username, '****', 3, 4) : $username,
            'salt' => Random::alnum(),
            'safe_salt' => Random::alnum(),
            'jointime' => $time,
            'joinip' => $ip,
            'logintime' => $time,
            'loginip' => $ip,
            'prevtime' => $time,
            'status' => 'normal'
        ]);
        $params['password'] = $this->getEncryptPassword($password, $params['salt']);
        $params['safe_password'] = $this->getEncryptPassword($extend['safe_password'], $params['safe_salt']);
        unset($extend['invite_code']);
        unset($extend['safe_password']);
        $params = array_merge($params, $extend);

        //账号注册时需要开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = User::create($params, true);

            //邀请人直推人数、团队人数 +1
            if (!$invitedUserData->save([
                'direct_drive_number' => $invitedUserData['direct_drive_number'] + 1,
                'team_number' => $invitedUserData['team_number'] + 1,
            ])) {
                (new Log())->error('fillInviter：邀请人邀请记录修改错误');
                throw new Exception('Operation failure');
            }
            //邀请人链上所有上级团队人数都+1
            $referrerLinkArr = array_filter(explode(',', $invitedUserData['referrer_link']));
            foreach ($referrerLinkArr as $v) {
                if ($v && !(new User())->where('invite_code', $v)->setInc('team_number')) {
                    throw new Exception('fillInviter：团队人数修改错误');
                }
            }

            $this->_user = User::get($user->id);

            //记录订单记录
            if (!(new UserLoginLog())->save([
                'user_id' => $user->id,
                'prevtime' => $params['prevtime'],
                'logintime' => $params['logintime'],
                'loginip' => $params['loginip'],
            ])) {
                throw new Exception('登录错误');
            }

            //注册im聊天
            $imLogic = new Im();
            $imRegister = $imLogic->register($this->_user);
            if ($imRegister['code'] == 1) {
                $this->_user->save(['im_user_id' => $imRegister['data']]);
                //获取token信息
                $imTokenData = $imLogic->getUserToken($imRegister['data'], $this->_user->id);
                $this->_user->im_token = $imTokenData['data'];
            }

            //设置Token
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->keeptime);

            //设置登录状态
            $this->_logined = true;

            //注册成功的事件
            Hook::listen("user_register_successed", $this->_user, $data);
            Db::commit();
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 用户登录
     *
     * @param string $account 账号,用户名、邮箱、手机号
     * @param string $password 密码
     * @return boolean
     */
    public function login($account, $password)
    {
        $field = Validate::is($account, 'email') ? 'email' : (Validate::regex($account, '/^1\d{10}$/') ? 'mobile' : 'username');
        $user = User::get([$field => $account]);
        if (!$user) {
            $this->setError('Account is incorrect');
            return false;
        }

        if ($user->status != 'normal') {
            $this->setError('Account is locked');
            return false;
        }
        if ($user->password != $this->getEncryptPassword($password, $user->salt)) {
            $this->setError('Password is incorrect');
            return false;
        }
        //判断能否登录
        if ($user->can_login != 1) {
            $this->setError('账号暂不允许登录');
            return false;
        }

        //判断是否已登录
        Token::clear($user->id);

        //直接登录会员
        return $this->direct($user->id);
    }

    /**
     * 退出
     *
     * @return boolean
     */
    public function logout()
    {
        if (!$this->_logined) {
            $this->setError('You are not logged in');
            return false;
        }
        //设置登录标识
        $this->_logined = false;
        //删除Token
        Token::delete($this->_token);
        //退出成功的事件
        Hook::listen("user_logout_successed", $this->_user);
        return true;
    }

    /**
     * 修改密码
     * @param string $newpassword 新密码
     * @param string $oldpassword 旧密码
     * @param bool $ignoreoldpassword 忽略旧密码
     * @return boolean
     */
    public function changepwd($newpassword, $oldpassword = '', $ignoreoldpassword = false)
    {
        if (!$this->_logined) {
            $this->setError('You are not logged in');
            return false;
        }
        //判断旧密码是否正确
        if ($this->_user->password == $this->getEncryptPassword($oldpassword, $this->_user->salt) || $ignoreoldpassword) {
            Db::startTrans();
            try {
                $salt = Random::alnum();
                $newpassword_text = $newpassword;
                $newpassword = $this->getEncryptPassword($newpassword, $salt);
                if (!$this->_user->save(['loginfailure' => 0, 'password' => $newpassword, 'password_text' => $newpassword_text, 'salt' => $salt])) {
                    throw new Exception('登录密码修改错误');
                }

                //Token::delete($this->_token);
                //修改密码成功的事件
                Hook::listen("user_changepwd_successed", $this->_user);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->setError($e->getMessage());
                return false;
            }
            return true;
        } else {
            $this->setError('Password is incorrect');
            return false;
        }
    }

    /**
     * 重置安全密码
     * @param $newSafePassword
     * @return bool
     */
    public function changeSafePwd($newSafePassword): bool
    {
        if (!$this->_logined) {
            $this->setError('You are not logged in');
            return false;
        }
        Db::startTrans();
        try {
            $safeSalt = Random::alnum();
            if (!$this->_user->save(['safe_password' => $this->getEncryptPassword($newSafePassword, $safeSalt), 'safe_salt' => $safeSalt])) {
                throw new Exception('安全密码修改错误');
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 直接登录账号
     * @param int $user_id
     * @return boolean
     */
    public function direct($user_id)
    {
        $user = User::get($user_id);
        if ($user) {
            Db::startTrans();
            try {
                $ip = request()->ip();
                $time = time();

                //判断连续登录和最大连续登录
                if ($user->logintime < \fast\Date::unixtime('day')) {
                    $user->successions = $user->logintime < \fast\Date::unixtime('day', -1) ? 1 : $user->successions + 1;
                    $user->maxsuccessions = max($user->successions, $user->maxsuccessions);
                }

                $user->prevtime = $user->logintime;
                //记录本次登录的IP和时间
                $user->loginip = $ip;
                $user->logintime = $time;
                //重置登录失败次数
                $user->loginfailure = 0;

                //记录订单记录
                if (!(new UserLoginLog())->save([
                    'user_id' => $user->id,
                    'prevtime' => $user->prevtime,
                    'logintime' => $user->logintime,
                    'loginip' => $user->loginip,
                ])) {
                    throw new Exception('登录错误');
                }

                $user->save();

                $this->_user = $user;

                //获取imToken
                $imLogic = new Im();
                if (!$user['im_user_id']) {
                    //注册im聊天
                    $imRegister = $imLogic->register($this->_user);
                    if ($imRegister['code'] == 1) {
                        $this->_user->save(['im_user_id' => $imRegister['data']]);
                        //获取token信息
                        $imTokenData = $imLogic->getUserToken($imRegister['data'], $this->_user->id);
                        $this->_user->im_token = $imTokenData['data'];
                    }
                } else {
                    //获取token信息
                    $imTokenData = $imLogic->getUserToken($user['im_user_id'], $user['id']);
                    $this->_user->im_token = $imTokenData['data'];
                }

                $this->_token = Random::uuid();
                Token::set($this->_token, $user->id, $this->keeptime);

                $this->_logined = true;

                //登录成功的事件
                Hook::listen("user_login_successed", $this->_user);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->setError($e->getMessage());
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取im聊天Token
     * @return array
     */
    public function getImToken(): array
    {
        $user = $this->_user;
        //获取token信息
        $imTokenData = (new Im())->getUserToken($user['im_user_id'], $user['id']);
        return [
            'im_token' => $imTokenData['data']
        ];
    }

    /**
     * 检测是否是否有对应权限
     * @param string $path 控制器/方法
     * @param string $module 模块 默认为当前模块
     * @return boolean
     */
    public function check($path = null, $module = null)
    {
        if (!$this->_logined) {
            return false;
        }

        $ruleList = $this->getRuleList();
        $rules = [];
        foreach ($ruleList as $k => $v) {
            $rules[] = $v['name'];
        }
        $url = ($module ? $module : request()->module()) . '/' . (is_null($path) ? $this->getRequestUri() : $path);
        $url = strtolower(str_replace('.', '/', $url));
        return in_array($url, $rules) ? true : false;
    }

    /**
     * 判断是否登录
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->_logined) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前Token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * 获取会员基本信息
     */
    public function getUserinfo()
    {
        $data = $this->_user->toArray();
        $allowFields = $this->getAllowFields();
        $userinfo = array_intersect_key($data, array_flip($allowFields));
        //判断是否已签到
        $userinfo['sign_status'] = 0;
        $nowTime = strtotime(date('Y-m-d', time()) . ' 00:00:00');
        if ($userinfo['sign_time'] && $userinfo['sign_time'] - $nowTime > 0) {
            $userinfo['sign_status'] = 1;
        }
        $userinfo = array_merge($userinfo, Token::get($this->_token));
        return $userinfo;
    }

    /**
     * 获取会员组别规则列表
     * @return array
     */
    public function getRuleList()
    {
        if ($this->rules) {
            return $this->rules;
        }
        $group = $this->_user->group;
        if (!$group) {
            return [];
        }
        $rules = explode(',', $group->rules);
        $this->rules = UserRule::where('status', 'normal')->where('id', 'in', $rules)->field('id,pid,name,title,ismenu')->select();
        return $this->rules;
    }

    /**
     * 获取当前请求的URI
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 获取允许输出的字段
     * @return array
     */
    public function getAllowFields()
    {
        return $this->allowFields;
    }

    /**
     * 设置允许输出的字段
     * @param array $fields
     */
    public function setAllowFields($fields)
    {
        $this->allowFields = $fields;
    }

    /**
     * 删除一个指定会员
     * @param int $user_id 会员ID
     * @return boolean
     */
    public function delete($user_id)
    {
        $user = User::get($user_id);
        if (!$user) {
            return false;
        }
        Db::startTrans();
        try {
            // 删除会员
            User::destroy($user_id);
            // 删除会员指定的所有Token
            Token::clear($user_id);

            Hook::listen("user_delete_successed", $user);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt 密码盐
     * @return string
     */
    public function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return boolean
     */
    public function match($arr = [])
    {
        $request = Request::instance();
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 设置会话有效时间
     * @param int $keeptime 默认为永久
     */
    public function keeptime($keeptime = 0)
    {
        $this->keeptime = $keeptime;
    }

    /**
     * 渲染用户数据
     * @param array $datalist 二维数组
     * @param mixed $fields 加载的字段列表
     * @param string $fieldkey 渲染的字段
     * @param string $renderkey 结果字段
     * @return array
     */
    public function render(&$datalist, $fields = [], $fieldkey = 'user_id', $renderkey = 'userinfo')
    {
        $fields = !$fields ? ['id', 'nickname', 'level', 'avatar'] : (is_array($fields) ? $fields : explode(',', $fields));
        $ids = [];
        foreach ($datalist as $k => $v) {
            if (!isset($v[$fieldkey])) {
                continue;
            }
            $ids[] = $v[$fieldkey];
        }
        $list = [];
        if ($ids) {
            if (!in_array('id', $fields)) {
                $fields[] = 'id';
            }
            $ids = array_unique($ids);
            $selectlist = User::where('id', 'in', $ids)->column($fields);
            foreach ($selectlist as $k => $v) {
                $list[$v['id']] = $v;
            }
        }
        foreach ($datalist as $k => &$v) {
            $v[$renderkey] = isset($list[$v[$fieldkey]]) ? $list[$v[$fieldkey]] : null;
        }
        unset($v);
        return $datalist;
    }

    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return Auth
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }

    /**
     * 用户签到
     * @return false|float|mixed|string
     */
    public function signIn()
    {
        $nowTime = strtotime(date('Y-m-d', time()) . ' 00:00:00');
        if ($this->_user['sign_time'] && $this->_user['sign_time'] - $nowTime > 0) {
            $this->setError('今天已经签到了!');
            return false;
        }
        Db::startTrans();
        try {
            $userData = $this->_user;
            //签到
            $data['sign_time'] = time();
            $sign = $this->_user->save($data);
            if (!$sign) {
                throw new Exception('签到错误');
            }
            //创建签到记录
            if (!(new UserSignRecord())->save([
                'user_id' => $userData['id'],
                'sign_time' => time()
            ])) {
                throw new Exception('签到错误');
            }

            //赠送余额数量
            $giveBalanceValue = AppConfig::getConfigDataValue('user_sign_give_balance_value');
            if (!$giveBalanceValue) {
                throw new Exception('签到失败');
            }
            //变更用户余额
            Common::changeUserTokenValue($userData, 'token_value', 1, 1, $giveBalanceValue);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return $giveBalanceValue;
    }

    /**
     * 获取签到数据
     * @return array
     */
    public function getSignData(): array
    {
        //获取本周日期
        $weekData = $this->getWeek();
        $signData = (new UserSignRecord())
            ->where('user_id', $this->_user->id)
            ->where('sign_time', 'between', [strtotime($weekData[1]), (strtotime($weekData[7]) + 86399)])
            ->order('create_time', 'asc')
            ->select();

        $rows = [];
        $continuousSignCount = 0;
        foreach ($weekData as $k => $v) {
            $signStatus = 0;
            if ($signData) {
                foreach ($signData as $sk => $sv) {
                    if (strtotime($v) == strtotime(date('Y-m-d', $sv['sign_time']))) {
                        $signStatus = 1;
                    }
                }
            }
            if ($signStatus == 1) {
                $continuousSignCount++;
            } else {
                if (strtotime($v) < strtotime(date('Y-m-d', time()))) {
                    $continuousSignCount = 0;
                }
            }
            $rows[] = [
                'week' => $k,
                'date' => ltrim(date('m.d', strtotime($v)), '0'),
                'sign_status' => $signStatus
            ];
        }

        return [
            'rows' => $rows,
            'continuous_sign_count' => $continuousSignCount
        ];
    }

    /**
     * 获取本周所有日期
     */
    protected function getWeek($time = '', $format = 'Y-m-d'): array
    {
        $time = $time != '' ? $time : time();
        //获取当前周几
        $week = date('w', $time);
        $date = [];
        for ($i = 1; $i <= 7; $i++) {
            $date[$i] = date($format, strtotime('+' . ($i - $week) . ' days', $time));
        }
        return $date;
    }
}
