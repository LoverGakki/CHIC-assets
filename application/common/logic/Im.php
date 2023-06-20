<?php

namespace app\common\logic;

use fast\Http;
use think\Model;
use think\Exception;
use app\common\library\Log;

use phpseclib\Crypt\RSA;

//use phpseclib3\Crypt\RSA;
//use phpseclib3\Crypt\PublicKeyLoader;

/**
 * 客服相关
 * Class Banner
 * @package app\common\logic
 */
class Im extends Model
{
    public $model = null;

    private $imUrl = 'http://120.79.93.219:9099';

    private $publicKey = '30819f300d06092a864886f70d010101050003818d0030818902818100c2edf64ea5dd95b30b382f484f5c157a46c61276f55af0ba8e8f80fafa7d20dc75477bbfb51ce1cca16a9869abfdd80e083fc7b7be83879077fada6d8868bd4ea66b5b8108e71a2f4b0c62c4e63a414b47c44ac8baffd959a389a4fd657cbeabe04a723971b3a4867d5e15fe3d2f8849a82d5a110814b16fa6f4ba0bbd8574350203010001';

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
     * 聊天注册
     * @param $userData
     * @return array
     */
    public function register($userData): array
    {
        $return = $this->return;
        $url = '/inner/account/register';
        try {
            $registerData = [
                'code' => $userData['mobile'],
                'gender' => '1',
                'motto' => '',
                'name' => $userData['username'],
                'password' => $userData['password'],
                'telephone' => $userData['mobile'],
            ];
            $request = CurlPost($this->imUrl . $url, $registerData, true, 30, [
                'sign' => $this->encryptWithPublicKey($url . '@' . time(), $this->publicKey)
            ]);
            $requestData = [];
            if ($request) {
                $requestData = json_decode($request, true);
                if (!in_array($requestData['code'], [200, 409])) {
                    throw new Exception('用户：' . $userData['id'] . '：注册IM错误-' . $requestData['message']);
                }
            }
            $return['code'] = 1;
            $return['msg'] = 'success';
            $return['data'] = $requestData['data'];
        } catch (Exception $e) {
            (new Log())->error('IM-register：' . $e->getMessage());
            $return['msg'] = 'Operation failed';
        }
        return $return;
    }

    /**
     * 获取im用户token
     * @param $imUserId
     * @param $userId
     * @return array
     */
    public function getUserToken($imUserId, $userId): array
    {
        $return = $this->return;
        $url = '/inner/account/token/' . $imUserId;
        try {
            //获取im用户数据
            $imUserData = Http::get($this->imUrl . $url, [], [CURLOPT_HTTPHEADER => [
                "Content-Type: application/json; charset=utf-8",
                'inner-access-sign: ' . $this->encryptWithPublicKey($url . '@' . time(), $this->publicKey)
            ]]);
            if ($imUserData) {
                $imUserData = json_decode($imUserData, true);
                if ($imUserData['code'] != 200) {
                    throw new Exception('用户：' . $userId . '：获取imToken错误-' . $imUserData['message']);
                }
            }
            $return['code'] = 1;
            $return['msg'] = 'success';
            $return['data'] = $imUserData['token'];
        } catch (Exception $e) {
            (new Log())->error('IM-register：' . $e->getMessage());
            $return['msg'] = 'Operation failed';
        }
        return $return;
    }

    /**
     * 使用公钥进行数据加密
     *
     * @param string $data 等待被加密的数据(字符串明文)
     * @param string $publicKey 公钥数据(Hex编码)
     * @return string 加密后的内容(已经通过Hex转码)
     */
    public function encryptWithPublicKey(string $data, string $publicKey): ?string
    {
        try {
            $dataBytes = utf8_encode($data);
            $publicKeyBytes = base64_encode(hex2bin($publicKey));
            $rsa = new RSA();
            $rsa->loadKey($publicKeyBytes);
            $rsa->setEncryptionMode(2);
            $encryptedData = $rsa->encrypt($dataBytes);
            if ($encryptedData === false) {
                throw new Exception("公钥加密失败====》明文：{$data}\n公钥：{$publicKey}\n失败原因:");
            } else {
                return bin2hex($encryptedData);
            }
        } catch (Exception $e) {
            $cause = $e->getPrevious();
            (new Log())->error("公钥加密失败====》明文：{$data}\n公钥：{$publicKey}\n失败原因:" . $e->getMessage() . "/" . ($cause == null ? null : get_class($cause) . ":" . $cause->getMessage()));
        }
        return null;
    }

}