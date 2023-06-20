<?php

namespace app\api\behavior;

use app\common\library\Log;

class Sms
{
    //appid参数
    protected $appid = '58247';
    //appkey参数
    protected $appKey = 'a0078bfd6a9e7c053e2100cca3982c7c';

    /**
     * 发送验证码
     * @param $params
     * @return bool
     */
    public function smsSend(&$params): bool
    {
        //收信人 手机号码
        $to = $params['mobile'];
        //内容
        $content = '【测试环境】您的手机验证码为：' . $params['code'] . '，该验证码5分钟内有效。';
        //通过接口获取时间戳
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://api-v4.mysubmail.com/service/timestamp.json',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 0,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        $timestamp = $output['timestamp'];

        $post_data = array(
            "appid" => $this->appid,
            "to" => $to,
            "timestamp" => $timestamp,
            "sign_type" => 'md5',
            "sign_version" => 2,
            "content" => $content,
        );
        //整理生成签名所需参数
        $temp = $post_data;
        unset($temp['content']);
        ksort($temp);
        reset($temp);
        $tempStr = "";
        foreach ($temp as $key => $value) {
            $tempStr .= $key . "=" . $value . "&";
        }
        $tempStr = substr($tempStr, 0, -1);
        //生成签名
        $post_data['signature'] = md5($this->appid . $this->appKey . $tempStr . $this->appid . $this->appKey);
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://api-v4.mysubmail.com/sms/send.json',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $post_data,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        $requestData = json_decode($output, true);
        if (array_key_exists('status', $requestData) && $requestData['status'] == 'success') {
            return true;
        }
        (new Log())->error('smsSend发送验证码：手机号：' . $params['mobile'] . '-' . $params['event'] . ' 发送验证码错误：' . $requestData['msg']);
        return false;
    }

    /**
     * 检验验证码（判断正确后直接删除记录）
     * @param $params
     * @return mixed
     */
    public function smsCheck(&$params)
    {
        return $params->delete();
    }
}