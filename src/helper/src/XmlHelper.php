<?php

namespace Mix\Helper;

/**
 * Class XmlHelper
 * @package Mix\Helper
 * @author liu,jian <coder.keda@gmail.com>
 */
class XmlHelper
{

    /**
     * 编码
     * @param $data
     * @return string
     */
    public static function encode($data)
    {
        $xml = '<xml>';
        $xml .= self::arrayToXml($data);
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * 解码
     * @param $xml
     * @return array|null
     */
    public static function decode($xml)
    {
        return self::xmlToArray($xml);
    }

    /**
     * array转xml
     * @param $data
     * @return string
     */
    protected static function arrayToXml($data)
    {
        $xml = '';
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $xml .= "<$key>";
                if (is_array($val)) {
                    $xml .= self::arrayToXml($val);
                } elseif (is_numeric($val)) {
                    $xml .= $val;
                } else {
                    $xml .= self::characterDataReplace($val);
                }
                $xml .= "</$key>";
            }
        }
        return $xml;
    }

    /**
     * 字符数据替换
     * @param $string
     * @return string
     */
    protected static function characterDataReplace($string)
    {
        return sprintf('<![CDATA[%s]]>', $string);
    }

    /**
     * xml转array
     * @param $xml
     * @return array|null
     */
    protected static function xmlToArray($xml)
    {
        $res = [];
        // 如果为空,一般是xml有空格之类的,导致解析失败
        $data = @(array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if (isset($data[0]) && $data[0] === false) {
            $data = null;
        }
        if ($data) {
            $res = self::parseToArray($data);
        }
        return $res;
    }

    /**
     * 解析SimpleXMLElement到array
     * @param $data
     * @return null
     */
    protected static function parseToArray($data)
    {
        $res = null;
        if (is_object($data)) {
            $data = (array)$data;
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_iterable($val)) {
                    $res[$key] = self::parseToArray($val);
                } else {
                    $res[$key] = $val;
                }
            }
        }
        return $res;
    }

}
