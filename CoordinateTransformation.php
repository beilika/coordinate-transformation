<?php

// +----------------------------------------------------------------------
// | Author: 贝莉卡 <beilika.com>
// +----------------------------------------------------------------------

/**
* 坐标转换类
*/
class CoordinateTransformation
{
    /**
     * bd09转gcj02
     * @param float $lat 百度坐标系的纬度
     * @param float $lng 百度坐标系的经度
     * @return array 转换后的gcj02坐标(高德坐标系)。格式array('lat'=>lat, 'lng'=>lng)
     */
    public static function bd09_to_gcj02($lat = 0, $lng = 0){
        $x = $lng - 0.0065;
        $y = $lat - 0.006;
        $z = sqrt($x*$x+$y*$y) - 0.00002 * sin($y * (M_PI * 3000.0 / 180.0));
        $theta = atan2($y, $x) -  0.000003 * cos($x * (M_PI * 3000.0 / 180.0));
        return array(
            'lat' => $z * sin($theta),
            'lng' => $z * cos($theta),
        );
    }

    /**
     * gcj02转bd09
     * @param float $lat gcj02坐标的纬度
     * @param float $lng gcj02坐标的经度
     * @return array 转换后的百度坐标系。格式array('lat'=>lat, 'lng'=>lng)
     */
    public static function gcj02_to_bd09($lat = 0, $lng = 0){
        $z = sqrt($lng * $lng + $lat * $lat) + 0.00002 * sin($lat * (M_PI * 3000.0 / 180.0));
        $theta = atan2($lat, $lng) + 0.000003 * cos($lng * (M_PI * 3000.0 / 180.0));

        return array(
            'lat' => $z * sin($theta) + 0.006,
            'lng' => $z * cos($theta) + 0.0065,
        );
    }

    /**
     * wgs84转gcj02
     * @param float $lat wgs84坐标的纬度
     * @param float $lng wgs84坐标的经度
     * @return array 转换后的gcj02坐标系。格式array('lat'=>lat, 'lng'=>lng)
     */
    public static function wgs84_to_gcj02($lat = 0, $lng = 0)
    {
        if (self::abroad($lat, $lng)) {
            return array(
                'lat' => $lat,
                'lng' => $lng,
            );
        }
        $res = self::delta($lat, $lng);
        return array(
            'lat' => $lat + $res['lat'],
            'lng' => $lng + $res['lng'],
        );
    }

    /**
     * gcj02转wgs84
     * @param float $lat gcj02坐标的纬度
     * @param float $lng gcj02坐标的经度
     * @return array 转换后的wgs84坐标系。格式array('lat'=>lat, 'lng'=>lng)
     */
    public static function gcj02_to_wgs84($lat = 0, $lng = 0)
    {
        if (self::abroad($lat, $lng)) {
            return array(
                'lat' => $lat,
                'lng' => $lng,
            );
        }
        $res = self::delta($lat, $lng);
        return array(
            'lat' => $lat - $res['lat'],
            'lng' => $lng - $res['lng'],
        );
    }

    /**
     * wgs84转墨卡托投影
     * @param float $lat wgs84坐标的纬度
     * @param float $lng wgs84坐标的经度
     */
    public static function wgs84_to_mercator($lat = 0, $lng = 0)
    {
        $x = $lng * 20037508.34 / 180.;
        $y = log(tan((90.0 + $lat) * M_PI / 360.0)) / (M_PI / 180.0);
        $y = $y * 20037508.34 / 180.0;
        return array(
            'lat' => $y,
            'lng' => $x,
        );
        /*
        if ((abs($lng) > 180 || abs($lat) > 90)){
            return null;
        }
        $x = 6378137.0 * $lng * 0.017453292519943295;
        $a = $lat * 0.017453292519943295;
        $y = 3189068.5 * log((1.0 + sin($a)) / (1.0 - sin($a)));
        return array(
            'lat' => $y,
            'lng' => $x,
        );
        */
    }

    /**
     * 墨卡托投影转wgs84
     * @param float $lat 墨卡托投影的纬度
     * @param float $lng 墨卡托投影的经度
     */
    public static function mercator_to_wgs84($lat = 0, $lng = 0)
    {
        $x = $lng / 20037508.34 * 180.0;
        $y = $lat / 20037508.34 * 180.0;
        $y = 180 / M_PI * (2 * atan(exp($y * M_PI / 180.0)) - M_PI / 2);
        return array(
            'lat' => $y,
            'lng' => $x,
        );
        /*
        if (abs($lng) < 180 && abs($lat) < 90){
            return null;
        }
        if ((abs($lng) > 20037508.3427892) || (abs($lat) > 20037508.3427892)){
            return null;
        }
        $a = $lng / 6378137.0 * 57.295779513082323;
        $x = $a - (floor((($a + 180.0) / 360.0)) * 360.0);
        $y = (1.5707963267948966 - (2.0 * atan(exp((-1.0 * $lat) / 6378137.0)))) * 57.295779513082323;
        return array(
            'lat' => $y,
            'lng' => $x,
        );
        */
    }

    /**
     * 获取两个坐标的距离
     * @param  float $lat_a 坐标a的lat
     * @param  float $lng_a 坐标a的lng
     * @param  float $lat_b 坐标b的lat
     * @param  float $lng_b 坐标a的lng
     * @return float
     */
    public static function distance($lat_a = 0, $lng_a = 0, $lat_b = 0, $lng_b = 0)
    {
        $earthR = 6371000.0;
        $x = cos($lat_a * M_PI / 180.0) * cos($lat_b * M_PI / 180.0) * cos(($lng_a - $lng_b) * M_PI / 180);
        $y = sin($lat_a * M_PI / 180.0) * sin($lat_b * M_PI / 180.0);
        $s = $x + $y;
        if ($s > 1) {
            $s = 1;
        }
        if ($s < -1) {
            $s = -1;
        }
        $alpha = acos($s);
        $distance = $alpha * $earthR;
        return $distance;
    }

    private static function delta($lat = 0, $lng = 0)
    {
        //a: 卫星椭球坐标投影到平面地图坐标系的投影因子。
        $a = 6378245.0;
        //ee: 椭球的偏心率。
        $ee = 0.00669342162296594323;
        $dLat = self::transformLat($lng - 105.0, $lat - 35.0);
        $dlng = self::transformlng($lng - 105.0, $lat - 35.0);
        $radLat = $lat / 180.0 * M_PI;
        $magic = sin($radLat);
        $magic = 1 - $ee * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat = ($dLat * 180.0) / (($a * (1 - $ee)) / ($magic * $sqrtMagic) * M_PI);
        $dlng = ($dlng * 180.0) / ($a / $sqrtMagic * cos($radLat) * M_PI);
        return array(
            'lat' => $dLat,
            'lng' => $dlng,
        );
    }

    /**
     * 判断坐标是否在国外
     * @param  float $lat
     * @param  float $lng
     * @return boolen
     */
    private static function abroad($lat = 0, $lng = 0)
    {
        if ($lng < 72.004 || $lng > 137.8347){
            return true;
        }
        if ($lat < 0.8293 || $lat > 55.8271){
            return true;
        }
        return false;
    }

    private static function transformLat($x, $y){
        $ret = -100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x * $y + 0.2 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * M_PI) + 20.0 * sin(2.0 * $x * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($y * M_PI) + 40.0 * sin($y / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($y / 12.0 * M_PI) + 320 * sin($y * M_PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    private static function transformlng($x, $y){
        $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * M_PI) + 20.0 * sin(2.0 * $x * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($x * M_PI) + 40.0 * sin($x / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($x / 12.0 * M_PI) + 300.0 * sin($x / 30.0 * M_PI)) * 2.0 / 3.0;
        return $ret;
    }
}