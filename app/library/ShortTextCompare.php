<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/20
 * Time: 11:08
 */

#中文短句子相似度比较
#参考文献 http://www.doc88.com/p-2055556399236.html
class ShortTextCompare
{
    #计算匹配文字$arr1[$i]的最小匹配偏移值
    public static function PosOffset($arr1, $i, $arr2)
    {
        $len2 = count($arr2);
        $arr2_reverse = array_reverse($arr2);
        for ($j = 0; $j < $len2; $j++) {
            $rev_num = abs($i - $j) - 1;
            $rev_data = isset($arr2_reverse[$rev_num]) ? $arr2_reverse[$rev_num] : '';
            $notrev_data = isset($arr2[$i - $j]) ? $arr2[$i - $j] : '';
            if ($i + $j >= 0 && $arr1[$i] == ($i - $j >= 0 ? $notrev_data : $rev_data)) {
                return $j;
            }
            if ($i + $j < $len2 && $arr1[$i] == $arr2[$i + $j]) {
                return $j;
            }
        }
        return $len2;
    }

    #计算匹配文字$arr1[$i]对于整体相似度的贡献量
    public static function CC($arr1, $i, $arr2)
    {
        $len2 = count($arr2);
        $len2_float = sprintf("%.2f", $len2);
        $temp = self::PosOffset($arr1, $i, $arr2);
        $data = ($len2 - $temp) / $len2_float;
        return $data;
    }

    #计算短语$arr1相对于短语$arr2的相似度sc
    public static function SC($arr1, $arr2)
    {
        $sc = 0.0;
        $len1 = count($arr1);
        for ($i = 0; $i < $len1; $i++) {
            $sc += self::CC($arr1, $i, $arr2);
        }
        $sc /= $len1;
        return $sc;
    }

    #计算短语$arr1与短语$arr2之间的相似度
    public static function S($arr1, $arr2)
    {
        $temp1 = self::SC($arr1, $arr2);
        $temp2 = self::SC($arr2, $arr1);
        return ($temp1 + $temp2) / 2;
    }

    #将字符串转换成数组存储
    public static function CharToArr($str)
    {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }
}

//$str1 = '计算机专业英语';
//$str2 = '大学英语';
//$str_arr1 = ShortTextCompare::CharToArr($str1);
//$str_arr2 = ShortTextCompare::CharToArr($str2);
//$num = ShortTextCompare::S($str_arr1, $str_arr2);
//print $num;