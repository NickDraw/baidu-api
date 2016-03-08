<?php
// 链接登录页面
namespace LianYue\BaiduApi;
require __DIR__ . '/config.php';

$map = new Map(MAP_AK, MAP_SK);
$response = '';


if (!empty($_REQUEST['method'])) {
    $params = array();
    if (!empty($_REQUEST['params'])) {
        parse_str($_REQUEST['params'], $params);
    } else {
        $params = array();
    }
    $response = $map->$_REQUEST['method']($params)->response()->getJson();
}

namespace LianYue\BaiduApi;
$map = new Map(MAP_AK, MAP_SK);
$json = $map->getPlaceSearch(array('query' => '百度公', 'region' => '北京'))->response()->getJson();
print_r($json);

$options = array(
    'getPlaceSearch',
    'getPlaceDetail',
    'getPlaceSuggestion',
    'getGeocoder',
    'getDirection',
    'getLocationIp',
    'getGeoConv',
    'getStaticImage',
);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8" />
</head>
<body>
    <pre><?=print_r($response)?></pre>
    <form>
        Method: <select name="method">
            <?php foreach($options as $value): ?>
                <option value="<?=$value?>"><?=$value?></option>
            <?php endforeach; ?>
        </select>
        Params: <input type="text" name="params" value="query=百度&region=北京" style="width: 50em;">
        <input type="submit" value="提交">
    </form>
</body>
</html>
