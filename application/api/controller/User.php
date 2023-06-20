<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\exception\UploadException;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\library\Upload;
use fast\Random;
use think\captcha\Captcha;
use think\Config;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'getImageCaptcha', 'register', 'resetpwd'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 上传图片
     * @return mixed
     */
    public function uploadImage()
    {
        //return action('api/common/upload');
        Config::set('default_return_type', 'json');
        //必须设定cdnurl为空,否则cdnurl函数计算错误
        Config::set('upload.cdnurl', '');

        $attachment = null;
        //默认普通上传文件
        $file = $this->request->file('file');
        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
        } catch (UploadException $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'full_url' => cdnurl($attachment->url, true)]);
    }

    /**
     * 获取用户数据
     */
    public function getUserData()
    {
        if (!$this->request->isGet()) {
            $this->error(__('Incorrect request mode'));
        }
        $user = $this->auth->getUserinfo();
        $this->success('success', $user);
    }

    /**
     * 获取im聊天Token
     */
    public function getImToken()
    {
        if (!$this->request->isGet()) {
            $this->error(__('Incorrect request mode'));
        }
        $this->success('success', $this->auth->getImToken());
    }

    /**
     * 获取图形验证码
     */
    public function getImageCaptcha()
    {
        if ($this->request->isGet()) {
            $captcha = new Captcha();
            $captcha->length = 4;
            return $captcha->entry();
        }
        return '';
    }

    /**
     * 注册会员
     */
    public function register()
    {
        //手机号
        $mobile = $this->request->post('mobile');
        //用户名
        $username = $this->request->post('username');
        //密码
        $password = $this->request->post('password');
        //确认密码
        $confirmPassword = $this->request->post('confirm_password');
        //图形验证码
        //$graphicCaptcha = $this->request->post('graphic_captcha');
        //短信验证码
        $captchaCode = $this->request->post('captcha');
        //安全密码
        $safePassword = $this->request->post('safe_password');
        //确认安全密码
        $confirmSafePassword = $this->request->post('confirm_safe_password');
        //邀请码
        $inviteCode = $this->request->post('invite_code');
        //验证参数
        if (!$mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $rule = [
            'username' => 'require|length:2,50',
            'password' => 'require|length:6,30',
            'confirm_password' => 'require',
            //'graphic_captcha|图形验证码' => 'require|captcha',
            'captcha' => 'require|length:4',
            'safe_password' => 'require|length:6,30',
            'invite_code' => 'require|length:6,8',
        ];
        $msg = [
            'username.require' => 'Please enter a nickname',
            'username.length' => 'The nickname length is 2~50 characters',
            'password.require' => 'Please enter a password',
            'password.length' => 'The password length is 6~30 characters',
            'confirm_password.require' => 'Please enter a confirmation password',
            'captcha.require' => 'Please enter the SMS verification code',
            'captcha.length' => 'The SMS verification code is incorrect',
            'safe_password.require' => 'Please enter a security password',
            'safe_password.length' => 'The security password length is 6~30 characters',
            'invite_code.require' => 'Please enter the invitation code',
            'invite_code.length' => 'The invitation code is incorrect',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check([
            'username' => $username,
            'password' => $password,
            'confirm_password' => $confirmPassword,
            //'graphic_captcha' => $graphicCaptcha,
            'captcha' => $captchaCode,
            'safe_password' => $safePassword,
            'invite_code' => $inviteCode,
        ])) {
            $this->error(__((string)$validate->getError()));
        }
        if ($confirmPassword != $password) {
            $this->error(__('The password and confirmation password must match'));
        }
        if ($confirmSafePassword != $safePassword) {
            $this->error(__('The safe password and confirmation safe password must match'));
        }
        //检验验证码
        $ret = Sms::check($mobile, $captchaCode, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, '', $mobile, ['invite_code' => $inviteCode, 'safe_password' => $safePassword]);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success('修改成功');
    }

    /**
     * 修改登录密码
     */
    public function changePwd()
    {
        $old_pwd = $this->request->post('old_pwd');
        $new_pwd = $this->request->post('new_pwd');
        $confirm_pwd = $this->request->post('confirm_pwd');
        if (!$old_pwd || !$new_pwd || !$confirm_pwd) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $new_pwd], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($new_pwd != $confirm_pwd) {
            $this->error(__('The password and confirmation password must match'));
        }
        $ret = $this->auth->changepwd($new_pwd, $old_pwd);
        if ($ret) {
            $this->success('修改成功');
        }
        $this->error('密码错误，请重试');
    }

    /**
     * 重置密码（忘记密码）
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     */
    public function resetpwd()
    {
        $type = $this->request->post("type", 'mobile');
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $confirmPassword = $this->request->post("confirmpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha || !$mobile) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($newpassword != $confirmPassword) {
            $this->error(__('The password and confirmation password must match'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 重置安全密码
     */
    public function resetSafePwd()
    {
        $mobile = $this->request->post("mobile");
        $new_safe_password = $this->request->post("new_safe_password");
        $confirm_safe_password = $this->request->post("confirm_safe_password");
        $captcha = $this->request->post("captcha");
        if (!$mobile || !$new_safe_password || !$confirm_safe_password || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if ($mobile != $this->auth->mobile) {
            $this->error('请使用注册手机号');
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $new_safe_password], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($new_safe_password != $confirm_safe_password) {
            $this->error(__('The password and confirmation password must match'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        $ret = Sms::check($mobile, $captcha, 'resetSafePwd');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        Sms::flush($mobile, 'resetSafePwd');
        $ret = $this->auth->changeSafePwd($new_safe_password);
        if ($ret) {
            $this->success(__('Reset safe password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 实名认证
     */
    public function realNameAuthenticated()
    {
        if ($this->request->isPost()) {
            $realName = $this->request->request('real_name');
            $idCardNumber = $this->request->request('id_card_number');
            $idCardFront = $this->request->request('id_card_front');
            $idCardBack = $this->request->request('id_card_back');
            if (!$realName) {
                $this->error(__('Invalid parameters'));
            }
            if (!$idCardNumber) {
                $this->error(__('Invalid parameters'));
            }
            if (!$idCardFront) {
                $this->error(__('身份证正面照片缺失'));
            }
            if (!$idCardBack) {
                $this->error(__('身份证反面照片缺失'));
            }
            $userModel = $this->auth->getUser();
            if ($userModel['is_audit'] == 1) {
                $this->success('已通过审核，请勿重复操作');
            }
            if (!$userModel->save([
                'real_name' => $realName,
                'id_card_number' => $idCardNumber,
                'is_audit' => 0,
                'id_card_front' => $idCardFront,
                'id_card_back' => $idCardBack,
                'apply_time' => time(),
            ])) {
                $this->success('操作失败');
            }
            $this->success('绑定成功');
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 用户签到
     */
    public function sign_in()
    {
        if ($this->request->isGet()) {
            if ($this->auth->is_audit != 1) {
                $this->error(__('账户未实名，请先申请实名认证'));
            }
            $sign = $this->auth->signIn();
            if ($sign && $sign > 0) {
                $this->success('签到成功!', ['add' => $sign]);
            }
            $this->error($this->auth->getError());
        }
        $this->error(__('Incorrect request mode'));
    }

    /**
     * 获取签到数据
     */
    public function getSignData()
    {
        if ($this->request->isGet()) {
            $this->success('success', $this->auth->getSignData());
        }
        $this->error(__('Incorrect request mode'));
    }
}
