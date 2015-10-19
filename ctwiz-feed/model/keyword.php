<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 19/04/15
 * Time: 13:47
 */

class ctwizKeywordModel {

    static $map;

    static public function init() {
        $filePath= dirname(__FILE__).'/../config.ini';
        $keywords = parse_ini_file($filePath);
        self::$map = self::map($keywords);
    }

    static public function map($keywords){
        $arr = array();
        foreach ($keywords as $key=>$word){
            if (is_array($word)){
                $arr[$key] = self::map($word);
            }
            else {
                $parts = explode(",",$word);
                foreach ($parts as $part){
                    $part = self::fix($part);
                    $arr[$part] = $key;
                }
            }
        }
        return $arr;
    }
    static public function _($key,$section=null){
        $key = self::fix($key);
        if (!sizeof(self::$map))
            self::init();

        if ($section){
            if (isset(self::$map[$section][$key])){
                return self::$map[$section][$key];
            }
        }
        else {
            if (isset(self::$map[$key])){
                return self::$map[$key];
            }
        }


    }

    static function fix ($term){
        $term = preg_replace('/\s+/', ' ',$term);
        $term = mb_strtolower($term);
        $term = trim($term);
        return $term;
    }
}