

# Composer 安装

    composer require lianyue/baidu-api





# 百度 OAuth2 应用

### 如何申请应用

应用列表
    http://developer.baidu.com/console#app/project

创建应用
    http://developer.baidu.com/console#app/create

回调地址
    请在 你创建的应用详情 找到 **其他API** 然后找到 **安全设置**  就可以设置回调地址了

Client Id
    就是你的  **Api Key**

Client Key
    就是你的  **Secret Key**





### 轻应用的 OAuth2 设置
应用列表
    http://qing.baidu.com/console

创建应用
    不知道跑哪去了好乱

回调地址
    请在 你创建的应用详情 找到 **能力** 然后找到 **账号** 右边 **设置**  点击设置 回调地址了


Client Id
    就是你的  **Api Key**

Client Key
    就是你的  **Secret Key**



### OAuth2 api 列表
http://developer.baidu.com/wiki/index.php?title=docs/oauth/rest/file_data_apis_list


### Oauth2使用方法

    namespace LianYue\BaiduApi;

    $oauth2 = new OAuth2(CLIENT_ID, CLIENT_KEY);
    $oauth2->setRedirectUri(CALLBACK_URI);
    try {
        // 设置 state
        if (!empty($_COOKIE['baidu_api_state'])) {
            $oauth2->setState($_COOKIE['baidu_api_state']);
        }

        // 取得令牌
        $accessToken = $oauth2->getAccessToken();

        // 访问令牌
        print_r($accessToken);

        // 用户信息
        print_r($oauth2->getUserInfo()->getJson(true));

        // 其他api调用
        print_r($this->api('GET', '/rest/2.0/passport/users/getInfo')->response()->getJson(false));
    } catch (BaiduApiException $e) {

        // 获取重定向链接
        $uri = $oauth2->getAuthorizeUri(['display' => 'pc']);

        // 储存 state
        setcookie('baidu_api_state', $oauth2->getState(), time() + 86400, '/');

        // 重定向
        header('Location: ' . $uri);
    }





# 百度Map 地图

Api 地址
http://lbsyun.baidu.com/apiconsole/key

创建地址
http://lbsyun.baidu.com/apiconsole/key/create

Map Api 列表
http://lbsyun.baidu.com/index.php?title=webapi


### 测试代码

    namespace LianYue\BaiduApi;
    $map = new Map(MAP_AK, MAP_SK);
    $json = $map->getPlaceSearch(array('query' => '百度公', 'region' => '北京'))->response()->getJson();
    print_r($json);
