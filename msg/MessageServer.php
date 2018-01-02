<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;

/**
 * 消息服务器，用户处理来自微信的消息
 *
 * @author Chenxy
 */
class MessageServer
{
    /**
     * 消息处理器
     * @var MessageProcessor
     */
    private $_msgProcessor;
    
    /**
     * 消息上下文
     * @var  \app\framework\weixin\msg\HttpMsgContext
     */
    private $_httpMsgContext = null;
    
    /**
     * 消息Module链
     * @var array
     */
    private $_httpMsgModules = [];
    
    /**
     * 开始执行请求事件链
     * @var array
     */
    public $beginRequestEvents = [];
    
    /**
     * 结束执行请求事件链
     * @var array
     */
    public $endRequestEvents = [];
    
    /**
     * 准备执行请求的handle事件链
     * @var array
     */
    public $beforeExecHandleEvents = [];
    
    /**
     * 执行完请求handle事件链
     * @var array
     */
    public $afterExecHandleEvents = [];
    
    // 管道对请求处理状态
    const STATE_INIT_REQUEST = "init_request";
    
    const STATE_BEGIN_REQUEST = "begin_request";
    
    const STATE_BEGIN_HANDLE = "begin_exec_handle";
    
    const STATE_END_HANDLE = "end_exec_handle";
    
    const STATE_END_REQUEST = "end_request";
    
    private $_state;

    private function __construct($messageProcessor)
    {
        $this->_msgProcessor = $messageProcessor;
    }
    
    /**
     * 创建一个微信消息处理服务
     * @param MsgProcessor $msgProcessor 消息处理器
     * @return MessageServicer
     */
    public static function getApp($msgProcessor)
    {
        return new MessageServer($msgProcessor);
    }
    
    /**
     * 注册消息module
     * @param string $httpMsgModuleClassName 类名
     * @param array $args 构造方法参数
     */
    public function regist($httpMsgModuleClassName, $args = [])
    {
        $this->_httpMsgModules[$httpMsgModuleClassName] = ['class' => $httpMsgModuleClassName, 'args' => $args];
    }

    /**
     * 获取上下文对象
     * @return \app\framework\weixin\msg\HttpMsgContext
     */
    public function getContext()
    {
        return $this->_httpMsgContext;
    }

    /**
     * 处理请求
     */
    public function processRequest($postXml = '')
    {
        // 微信接入验证
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            key_exists('echostr', $_GET) ? exit($_GET['echostr']) : exit();
        }
        
        // 消息处理
        try {
            // 1、初始化请求：初始化上下文对象和request
            $this->_state = self::STATE_INIT_REQUEST;
            $this->initRequest($postXml);

            // 2、开始处理请求：初始化处理数据数组
            $this->_state = self::STATE_BEGIN_REQUEST;
            $this->trigger($this->beginRequestEvents);
            $this->beginRequest();

            // 3、调用相应的handle处理请求：处理请求并初始化response
            $this->_state = self::STATE_BEGIN_HANDLE;
            $this->trigger($this->beforeExecHandleEvents);
            $this->handleRequest();
            $this->_state = self::STATE_END_HANDLE;
            $this->trigger($this->afterExecHandleEvents);

            // 4、结束请求
            $this->_state = self::STATE_END_REQUEST;
            $this->trigger($this->endRequestEvents);
            $this->endRequest();
        } catch (\Exception $ex) {
            // 记录日志;
            \Yii::error("微信消息处理错误(processRequest)：" . $ex->getMessage() . "\n执行状态:" . $this->_state . "\n消息内容：" . $this->_httpMsgContext->request->requestXml);
            exit("success");
        }
    }

    /**
     * 获取输入的内容
     * @param string $postXml
     * @return bool|string
     */
    public function getRequestMsg($postXml = ''){
        // 微信接入验证
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            key_exists('echostr', $_GET) ? exit($_GET['echostr']) : exit();
        }

        // 消息处理
        try {
            // 1、初始化请求：初始化上下文对象和request
            $this->_state = self::STATE_INIT_REQUEST;
            $this->initRequest($postXml);

            // 2、开始处理请求：初始化处理数据数组
            $this->_state = self::STATE_BEGIN_REQUEST;
            $this->trigger($this->beginRequestEvents);

            $theTenantDb = \app\framework\biz\cache\OrganizationCacheManager::getTenantDbConn('mysoft');
            $theTenantDb->createCommand()->insert('t_data_sync_log', ['id' => \app\framework\utils\StringHelper::uuid(), 'data_type' => 'pc_wechat_member_queue_old_xml', 'data_id' => '123', 'data' => print_r($this->_httpMsgContext->request->requestXml, true)])->execute();

            $xml = $this->_httpMsgContext->request->requestXml;
            $this->replaceCannotRecognizeChar($xml);
            $xmldata = new \SimpleXMLElement($xml);

            // 转换成数组
            $data = [];
            foreach ($xmldata as $key => $value) {
                $data[$key] = strval($value);
            }

            $theTenantDb->createCommand()->insert('t_data_sync_log', ['id' => \app\framework\utils\StringHelper::uuid(), 'data_type' => 'pc_wechat_member_queue_xml_array', 'data_id' => '123', 'data' => print_r($data, true)])->execute();

            return $data;

        } catch (\Exception $ex) {;
            $requestXml = $this->_httpMsgContext->request->requestXml;
            \Yii::error($ex);
            \Yii::error("获取输入内容失败(getRequestMsg)：" . $ex->getMessage() . "\n执行状态:" . $this->_state . "\n消息内容：" . $requestXml);

            $theTenantDb = \app\framework\biz\cache\OrganizationCacheManager::getTenantDbConn('mysoft');
            $theTenantDb->createCommand()->insert('t_data_sync_log', ['id' => \app\framework\utils\StringHelper::uuid(), 'data_type' => 'pc_wechat_member_queue_getRequestMsg_error', 'data_id' => '123', 'data' => $ex->getMessage()])->execute();
            return false;
        }
    }
    
    /**
     * 初始化请求
     */
    private function initRequest($postXml = '')
    {
        // 1、初始化消息上下文对象
        if(empty($postXml)){
            $postXml = file_get_contents("php://input");
        }

        $request = new HttpMsgRequest($postXml);
        $this->_httpMsgContext = new HttpMsgContext($request);

        // 2、初始化注册的消息module
        foreach ($this->_httpMsgModules as $msgModule) {
            $className = $msgModule['class'];
            $args = $msgModule['args'];
            $reflect = new \ReflectionClass($className);
            $instance = $args ? $reflect->newInstanceArgs($args) : $reflect->newInstanceWithoutConstructor();
            $instance->init($this);
        }

    }
    
    /**
     * 替换用户输入不可识别的字符，该字符会导致XML解析失败，如输入一个气球表情
     * @param string $xml
     */
    private function replaceCannotRecognizeChar(&$xml)
    {
        $xml = str_replace(' ', '', $xml);
        $xml = str_replace('', '', $xml);
        $xml = str_replace('', '', $xml);
    }
    
    /**
     * 开始处理请求
     */
    private function beginRequest()
    {
        $xml = $this->_httpMsgContext->request->requestXml;
        $this->replaceCannotRecognizeChar($xml);
        $xmldata = new \SimpleXMLElement($xml);

        // 转换成数组
        $data = [];
        foreach ($xmldata as $key => $value) {
            $data[$key] = strval($value);
        }
        
        $this->_httpMsgContext->request->requestData = $data;
        // 写日志失败时忽略
        try {
            $this->logRequest();
        } catch (\Exception $ex) {
            \Yii::error($ex);
            \Yii::error("写微信消息日志失败：" . $ex->getMessage() . "。消息内容" . $this->_httpMsgContext->request->requestXml);
        }
    }
    
    private function logRequest()
    {
        $requestTime = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);
        $xml = $this->_httpMsgContext->request->requestXml;
        $data = $this->_httpMsgContext->request->requestData;
        $fromUser = array_key_exists('FromUserName', $data) ? $data['FromUserName'] : '';
        $toUser = array_key_exists('ToUserName', $data) ? $data['ToUserName'] : '';
        $msgTime = array_key_exists('CreateTime', $data) ? intval($data['ToUserName']) : 0;
        $oMsgType = array_key_exists('MsgType', $data) ? $data['MsgType'] : '';
        // 全网发布用full-web-publishing，开放平台推送用component,未识别的用unknown
        $msgType = $this->_httpMsgContext->request->isFullWebPublishing
                ? "publish"
                : ($oMsgType?:(array_key_exists('InfoType', $data) ? 'ticket' : 'unknown'));
        
        // 微信开发平台通知
        if ($msgType == 'ticket') {
            $fromUser = array_key_exists('AuthorizerAppid', $data) ? $data['AuthorizerAppid'] : '';
            $toUser = array_key_exists('Appid', $data) ? $data['Appid'] : '';
        }
        
        // 记录消息日志（包括微信消息，第三方平台消息，非微信转发来的消息，全网发布消息－暂时记录到Mysoft,后面考虑记日志站点）
        \app\framework\weixin\log\MsgLogging::log($fromUser, $toUser, $_SERVER['REQUEST_URI'], $requestTime, $msgTime, $msgType, $xml);
        
//        if ($this->_httpMsgContext->request->isFullWebPublishing) {
//            $logMsg = "全网发布接入日志消息(非异常)：fromUser:{$fromUser},toUser:{$toUser},called_url:" . $_SERVER['REQUEST_URI'];
//            $logMsg .= ",receive_time:{$requestTime},msg_timestamp:{$msgTime},msg_type:{$msgType},xml:{$xml}";
//            \Yii::error($logMsg);
//        } else {
//            // 记录消息日志（包括微信消息，第三方平台消息，非微信转发来的消息，全网发布消息－暂时记录到Mysoft,后面考虑记日志站点）
//            \app\framework\weixin\log\MsgLogging::log($fromUser, $toUser, $_SERVER['REQUEST_URI'], $requestTime, $msgTime, $msgType, $xml);
//        }
    }
    
    /**
     * 处理请求
     */
    private function handleRequest()
    {
        $handleData = $this->_httpMsgContext->request->requestData;
        $handleAction = $this->getHandleAction($handleData);
        $resultData = $this->_msgProcessor->run($handleData, $handleAction);
        
        if (is_array($resultData)) {
            $contentType = $resultData['contentType'];
            $content = $resultData['content'];
            $reponseData = [
                            'ToUserName'   => $handleData['FromUserName'],
                            'FromUserName' => $handleData['ToUserName'],
                            'CreateTime'   => time(),
                            'MsgType'      => $contentType,
            ];
            
            $resultData = $this->$contentType($reponseData, $content);
        }
        
        $reponse = new HttpMsgResponse($resultData);
        $this->_httpMsgContext->response = $reponse;
    }
    
    private function endRequest()
    {
        $responseContent = $this->_httpMsgContext->response->responseXml;
        if ($responseContent === "") {
            $responseContent = "success";
        }
        
        exit($responseContent);
    }

     /**
     * 触发事件
     */
    private function trigger($events, $args = null)
    {
        foreach ($events as $class => $method) {
            call_user_func([$class, "{$method}"], $this->_httpMsgContext, $args);
        }
    }
    
    /**
     * 回复一个文本消息
     * @param string $content
     */
    private function text($data, $content)
    {
        $data['Content'] = $content;
        return $data;
    }

    /**
     * 转向多客服系统，$content为客服账号，多个以分号隔开
     * @param string $content
     */
    private function transfer_customer_service($data, $content)
    {
        $KfAccount = explode(";", $content);
        if (count($KfAccount)>0) {
            $accounts = [];
            foreach ($KfAccount as $account) {
                if (!empty($account)) {
                    $accounts["KfAccount"] = $account;
                }

            }
            if (count($accounts)>0) {
                $data['TransInfo'] = $accounts;
            }
        }

        return $data;
    }
    
    /**
     * 回复一个图片
     * @param array $data
     * @param string $mediaId
     * @return type
     */
    private function image($data, $mediaId)
    {
        $data['Image'] = ['MediaId'=>$mediaId];
        return $data;
    }
    
    /**
     * 回复一个音频
     * @param array $data
     * @param string $mediaId
     * @return type
     */
    private function voice($data, $mediaId)
    {
        $data['Voice'] = ['MediaId'=>$mediaId];
        return $data;
    }
    
    /**
     * 回复一个视频
     * @param array $data ['MediaId'=>MediaId,Title=>标题,Description=>描述]
     * @param type $video
     * @return type
     */
    private function video($data, $video)
    {
        $data['Video'] = $video;
        return $data;
    }
    
    /**
     * 回复一个音乐
     * @param array $data ['ThumbMediaId'=>,Title=>,Description=>,MusicUrl=>,HQMusicUrl=>]
     * @param type $music
     */
    private function music($data, $music)
    {
        $data['Music'] = $music;
        return $data;
    }
    
    /**
     * 回复一个图文消息，最多10条
     * @param type $data
     * @param array $news [['Title'=>,'Description'=>,'PicUrl'=>,'Url'=>]]
     */
    private function news($data, $news)
    {
        $articles = [];
        $i = 0;
        foreach ($news as $new) {
            //最多允许10条图文
            if ($i > 10) {
                break;
            }
            // 构建图文格式
            $new['Description'] = strip_tags(htmlspecialchars_decode($new['Description']));
            $articles[] = $new;
            $i++;
        }
        
        $data['ArticleCount'] = count($articles);
        $data['Articles'] = $articles;
        return $data;
    }
    
    /**
     * 获取handler action
     */
    private function getHandleAction($handleData)
    {
        // 全网发布验证handler
        if ($this->_httpMsgContext->request->isFullWebPublishing) {
            $handleAction = $this->getFullWebPublishingHandleAction($handleData);
            return $handleAction;
        }
        
        // 事件推送类 handler=event
        if (array_key_exists('Event', $handleData)) {
            $handleAction = strtolower($handleData['Event']);
        } elseif (array_key_exists('InfoType', $handleData)) {
            $handleAction = strtolower($handleData['InfoType']);
        } elseif (array_key_exists('MsgType', $handleData)) {
            $handleAction = strtolower($handleData['MsgType']);
        } else { // 用于扩展的自定义类型
            $handleAction = strtolower($handleData['CustomerType']);
        }
        
        return $handleAction;
    }
    
    private function getFullWebPublishingHandleAction($handleData)
    {
        if (array_key_exists('Event', $handleData)) {
            $handleAction = "testEvent";
        } elseif (array_key_exists('InfoType', $handleData)) {
            $handleAction = "testTicket";
        } else {
            $handleAction = ($handleData['Content'] == "TESTCOMPONENT_MSG_TYPE_TEXT")
                    ? "testMsg" : "testApi";
        }
        
        return $handleAction;
    }
}
