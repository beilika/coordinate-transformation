# coordinatetransformation

百度，腾讯，高德地图坐标转换，即bd09，gcj02坐标转换类

## 使用方法

$coordinate = new CoordinateTransformation();

//将百度坐标转为腾讯坐标或高德坐标

$coordinate->bd09_to_gcj02($lat, $lng);

//将腾讯坐标或高德坐标转为百度坐标

$coordinate->gcj02_to_bd09($lat, $lng);

//将国际坐标转为腾讯坐标或高德坐标

$coordinate->wgs84_to_gcj02($lat, $lng);

//将腾讯坐标或高德坐标转为国际坐标

$coordinate->gcj02_to_wgs84($lat, $lng);

//计算两个坐标之间的距离

$coordinate->distance($lat_a = 0, $lng_a = 0, $lat_b = 0, $lng_b = 0);

## 注意事项

由于百度，腾讯等公司的坐标偏移算法是内部保密的，故该坐标转换类只能大致转换，具有一定偏差，请考虑实际情况使用。