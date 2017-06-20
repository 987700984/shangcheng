<?php
/**
 * wechat php test
 */



class ChatCallback
{
    public function valid($token)
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature($token)){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {

//        $this->picTextReturn($arr);

//        测试地理位置接口
//        $this->locationReturn();
//    验证不成功

//        测试图灵机器人
//        $this->autoReturn();
//    }验证不成功
    }





//           首次验证
    private function checkSignature($token)
    {
        // you must define TOKEN by yourself
        if (!$token) {
            throw new Exception('TOKEN is not defined!');
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

//          回复文本消息
    private function textReturn($contentStr = "呵呵"){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $msgType = $postObj->MsgType;
        $time = time();
        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
        if (!empty($keyword)) {
            $msgType = "text";
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        } else {
            echo "Input something...";
        }
    }

//          回复音乐消息
    private function musicReturn($url,$HPurl,$title = '音乐',$desc =  "美妙的音乐"){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Music>
							<Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            <MusicUrl><![CDATA[%s]]></MusicUrl>
                            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                    </xml>";
        $msgType = "music";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $title,$desc,$url,$HPurl);
        echo $resultStr;
    }

//          图文回复消息
    private function picTextReturn($arr){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $str = "<Articles>";
        $count = count($arr);
        foreach( $arr as $k=>$v){
            $str .= "<item>
             <Title><![CDATA[adf{$v['title']}]]></Title>
             <Description><![CDATA[adf{$v['title']}]]></Description>
             <PicUrl><![CDATA[{$v['picUrl']}]]></PicUrl>
             <Url><![CDATA[{$v['url']}]]></Url>
        </item>";
        }
        $str .= " </Articles>";
        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
                            <ArticleCount>%s</ArticleCount>
                            %s
                    </xml>";
        $msgType = "news";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $count,$str);
        echo $resultStr;
    }


private function locationReturn(){
    $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $fromUsername = $postObj->FromUserName;
    $toUsername = $postObj->ToUserName;
    $keyword = trim($postObj->Content);
    $msgType = $postObj->MsgType;
    $x = $postObj->Location_X;
    $y = $postObj->Location_Y;
    $contentStr = "您发送的地理位置是".$x;
    $time = time();
    $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
    if (!empty($keyword)) {
        $msgType = "text";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
    } else {
        echo "Input something...";
    }
}


////        自动回复机器人
//    private function autoReturn(){
//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
//        $fromUsername = $postObj->FromUserName;
//        $toUsername = $postObj->ToUserName;
//        $keyword = trim($postObj->Content);
//        $msgType = $postObj->MsgType;
//        $x = $postObj->Location_X;
//        $y = $postObj->Location_Y;
//        $contentStr = "您发送的地理位置是".$x;
//        $time = time();
//        $url = "http://www.tuling123.com/openapi/api?key=c7ba2ded78ebb79361f48b6fea034274&secret=7941b0f3b79348cf&info='你漂亮么'";
//        $str = file_get_contents($url);
//        $json = json_decode($str);
//        $contentStr = $json->text;
//        $textTpl = "<xml>
//							<ToUserName><![CDATA[%s]]></ToUserName>
//							<FromUserName><![CDATA[%s]]></FromUserName>
//							<CreateTime>%s</CreateTime>
//							<MsgType><![CDATA[%s]]></MsgType>
//							<Content><![CDATA[%s]]></Content>
//							<FuncFlag>0</FuncFlag>
//							</xml>";
//        if (!empty($keyword)) {
//            $msgType = "text";
//            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//            echo $resultStr;
//        } else {
//            echo "Input something...";
//        }
//    }
}
?>