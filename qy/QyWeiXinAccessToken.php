<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 13:34
 */

namespace app\framework\weixin\qy;

use app\framework\biz\cache\OrganizationCacheManager;
use app\framework\weixin\qy\interfaces\QyWeiXinAccessTokenInterface;

/**
 * 企业微信AccessToken
 * Class QyWeiXinAccessToken
 * @package app\framework\weixin\qy
 */
class QyWeiXinAccessToken implements QyWeiXinAccessTokenInterface
{
    /**
     * 租户代码
     * @var string
     */
    public $tenantCode;

    public function __construct($tenantCode = '')
    {
        $this->tenantCode = $tenantCode;
    }

    /**
     * 获取企业微信AccessToken
     * @param $appCode
     * @param $opToken
     * @return bool|mixed
     * @throws \Exception
     */
    public function getAccessToken($appCode, $opToken = '')
    {
        $cacheKey = $this->getAccessTokenCacheKey($appCode.$opToken);
        //从缓存中获取AccessToken
        $accessToken = \Yii::$app->cache->get($cacheKey);
        if (empty($accessToken)) {
            //获取微信配置信息
            $config = $this->getConfig($appCode);
            if (isset($config) === false || $config === false || is_array($config) === false || empty($config['corp_id']) || (intval($config['is_suite_auth']) === 0 && empty($config['corp_secret']) || (intval($config['is_suite_auth']) === 1 && empty($config['suite_auth_id'])))) {
                \yii::error("读取微信企业号配置失败：appCode:{$appCode} config:".json_encode($config));
                return false;
            }

            $expiresIn = 7200;
            if ($opToken == 'SuiteToken') {
                $accessToken = $this->getWxAccessTokenBySuite($config['suite_auth_id'], $expiresIn);
            } elseif ($opToken == 'ProviderToken') {
                $accessToken = $this->getWxProviderAccessToken($expiresIn);
            } elseif ($opToken == 'DefaultToken') {
                $accessToken = $this->getWxAccessTokenDefault($config['corp_id'], $config['corp_secret'], $expiresIn);
            } elseif (intval($config['is_suite_auth']) === 1) {
                $accessToken = $this->getWxAccessTokenBySuite($config['suite_auth_id'], $expiresIn);
            } else {
                $accessToken = $this->getWxAccessTokenDefault($config['corp_id'], $config['corp_secret'], $expiresIn);
            }

            if (!empty($accessToken)) {
                //设置过期时间与微信过期时间相差600s
                \Yii::$app->cache->set($cacheKey, $accessToken, ($expiresIn - 600));
            }
        }
        return $accessToken;
    }

    /**
     * 获取企业微信AccessToken默认方式（通过corp_id，corp_secret）
     * @param $corpId
     * @param $corpSecret
     * @param $expiresIn
     * @return bool
     */
    private function getWxAccessTokenDefault($corpId, $corpSecret, &$expiresIn)
    {
        //调用企业微信接口进行获取
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=' . $corpId . '&corpsecret=' . $corpSecret;
        try {
            $res = json_decode(static::wxRequest('获取企业微信AccessToken', $url), true);
            if (isset($res) && empty($res['access_token']) === false) {
                $expiresIn = intval($res['expires_in']);
                return $res['access_token'];
            }
        } catch (\Exception $e) {
            \Yii::error($e);
            return false;
        }
        return false;
    }

    /**
     * 获取应用提供商凭证ProviderToken
     * @param $expiresIn
     * @return bool
     */
    private function getWxProviderAccessToken(&$expiresIn)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token?';
        try {
            $params = ['corpid'=>'wx51b9a9581c992abf', 'provider_secret'=>'Rb6CSItGr9lVGEdxPDp8EckqAnyR8HJmuYqu_cY5JqnE3IiVdTpyHc_R1qYGdpP7'];
            $res = json_decode(static::wxRequest('获取应用提供商凭证ProviderToken', $url, 'POST', $params), true);
            if (isset($res) && empty($res['provider_access_token']) === false) {
                $expiresIn = intval($res['expires_in']);
                return $res['provider_access_token'];
            }
        } catch (\Exception $e) {
            \Yii::error($e);
            return false;
        }
        return false;
    }

    /**
     * 获取企业微信AccessToken (通过应用套件授权)
     * @param $suiteAuthId
     * @param $expiresIn
     * @return bool
     */
    private function getWxAccessTokenBySuite($suiteAuthId, &$expiresIn)
    {
        $configDb = \Yii::$app->getDb();//获取配置库Db
        $config = $configDb->createCommand('select a.corp_id,a.permanent_code,s.suite_id,s.suite_secret, s.suite_ticket from wx_suite_auth as a LEFT JOIN wx_suite as s on a.suite_id=s.id  where a.id=:auth_id and a.is_deleted =0 ', [':auth_id' => $suiteAuthId])->queryOne();
        if ($config) {
            $suiteToken = $this->getSuiteToken($config);
            if (empty($suiteToken)) {
                return false;
            }
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=' . $suiteToken;
            $data = [
                'suite_id' => $config['suite_id'],
                'auth_corpid' => $config['corp_id'],
                'permanent_code' => $config['permanent_code']
            ];
            $res = json_decode(static::wxRequest('获取企业号access_token', $url, 'POST', $data), true);
            if (isset($res) && empty($res['access_token']) === false) {
                $expiresIn = intval($res['expires_in']);
                return $res['access_token'];
            }
        }
        return false;
    }

    /**
     * 获取应用套件令牌
     * @param $config
     * @return bool|mixed
     */
    private function getSuiteToken($config)
    {
        if (isset($config) === false || empty($config['suite_id']) || empty($config['suite_secret']) || empty($config['suite_ticket'])) {
            return false;
        }
        $cacheKey = $this->getSuiteTokenCacheKey($config['suite_id']);
        $suiteToken = \Yii::$app->cache->get($cacheKey);
        if (empty($suiteToken)) {
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';
            $res = json_decode(static::wxRequest('获取应用套件令牌', $url, 'POST', ['suite_id' => $config['suite_id'], 'suite_secret' => $config['suite_secret'], 'suite_ticket' => $config['suite_ticket']]), true);
            if (isset($res) && empty($res['suite_access_token']) === false) {
                $suiteToken = $res['suite_access_token'];
                //设置过期时间与微信过期时间相差200s
                \Yii::$app->cache->set($cacheKey, $suiteToken, (intval($res['expires_in']) - 200));
            }
        }
        return $suiteToken;
    }

    /**
     * 获取套件Token缓存Key
     * @param $suiteId
     * @return string
     */
    private function getSuiteTokenCacheKey($suiteId)
    {
        return 'qy:wx:suite:token:' . $suiteId;
    }

    /**
     * 企业微信AccessToken缓存key
     * @param $appCode
     * @return string
     * @throws \Exception
     */
    public function getAccessTokenCacheKey($appCode)
    {
        return $this->getTenantCode() . ':qyWxAccessToken' . ':' . $appCode;
    }

    /**
     * 清除企业微信AccessToken缓存
     * @param $appCode
     * @return bool
     */
    public function clearAccessTokenCache($appCode)
    {
        return \Yii::$app->cache->delete($this->getAccessTokenCacheKey($appCode));
    }

    /**
     * 获取企业微信配置信息
     * @param string $appCode
     * @return array|bool|mixed
     */
    public function getConfig($appCode)
    {
        $cacheKey = $this->getConfigCacheKey($appCode);
        $config = \Yii::$app->cache->get($cacheKey);
        if (empty($config)) {
            $config = $this->getTenantDbConn()->createCommand('select corp_id,corp_secret,agent_id,token,encoding_aes_key,is_suite_auth,suite_auth_id from t_app_qyweixin where app_code=:appCode and is_deleted=0', [':appCode' => $appCode])->queryOne();
            if ($config !== false) {
                if (intval($config['is_suite_auth']) === 1 && !empty($config['suite_auth_id'])) {
                    //套件授权，token与encoding_aes_key则从套件处获取
                    $suite_config = \Yii::$app->getDb()->createCommand('SELECT a.suite_token AS token,a.suite_encoding_aes_key AS encoding_aes_key FROM wx_suite AS a INNER JOIN wx_suite_auth AS b ON a.id=b.suite_id AND b.is_deleted=0 AND b.id=:auth_id WHERE a.is_deleted=0', [':auth_id' => $config['suite_auth_id']])->queryOne();
                    if ($suite_config !== false) {
                        $config = array_merge($config, $suite_config);
                    }
                }
                \Yii::$app->cache->set($cacheKey, $config);
            }
        }
        return $config;
    }

    /**
     * 清除配置信息缓存
     * @param $appCode
     * @return bool
     */
    public function clearConfigCache($appCode)
    {
        return \Yii::$app->cache->delete($this->getConfigCacheKey($appCode));
    }

    /**
     * 获取企业微信Config配置信息
     * @param $appCode
     * @return string
     * @throws \Exception
     */
    public function getConfigCacheKey($appCode)
    {
        return $this->getTenantCode() . ':qyWxConfig' . ':' . $appCode;
    }

    /**
     * 获取租户DB配置信息
     * @return \yii\db\Connection
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function getTenantDbConn()
    {
        // 获取数据库连接
        return OrganizationCacheManager::getTenantDbConn($this->getTenantCode());

    }

    /**
     * 获取租户代码
     * @return mixed
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function getTenantCode()
    {
        if (!empty($this->tenantCode)) {
            return $this->tenantCode;
        }
        $tenantReader = \Yii::$container->get('app\framework\biz\tenant\TenantReaderInterface');
        if (!isset($tenantReader)) {
            throw new \Exception('未注入app\framework\biz\tenant\TenantReaderInterface实例');
        }
        return $tenantReader->getCurrentTenantCode();
    }

    /**
     * http请求
     * @param $remark
     * @param $url
     * @param $method
     * @param null $data
     * @return bool|mixed
     */
    private static function wxRequest($remark, $url, $method = 'GET', $data = null)
    {
        if (empty($url) || empty($method)) {
            return false;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                if (isset($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
                }
                break;
            default:
                break;
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        \Yii::trace('企业微信:' . $remark . ':' . $res);
        return $res;
    }
}
