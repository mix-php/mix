<?php

namespace mix\web;

/**
 * Captcha类
 * @author 刘健 <coder.liu@qq.com>
 */
class Captcha
{

    // 宽度
    public $width = 100;

    // 高度
    public $height = 40;

    // 字集合
    public $wordSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // 字体文件
    public $fontFile = '';

    // 字体大小
    public $fontSize = 20;

    // 字数
    public $wordNumber = 4;

    // 角度随机
    public $angleRand = [-20, 20];

    // 字距
    public $xSpacing = 0.8;

    // Y轴随机
    public $yRand = [5, 15];

    // 构造
    public function __construct($config = [])
    {
        // 导入配置
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    // 生成
    public function generate()
    {
        $canvas     = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        $fontColor  = imagecolorallocate($canvas, 32, 64, 160);
        imagefill($canvas, 0, 0, $background);
        for ($i = 1; $i <= $this->wordNumber; $i++) {
            imagettftext($canvas, $this->fontSize, mt_rand($this->angleRand[0], $this->angleRand[1]), $this->fontSize * ($this->xSpacing * $i), $this->fontSize + mt_rand($this->yRand[0], $this->yRand[1]), $fontColor, $this->fontFile, iconv_substr($this->wordSet, floor(mt_rand(0, mb_strlen($this->wordSet, 'utf-8') - 1)), 1, 'utf-8'));
        }
        imagesavealpha($canvas, true);
        //ob_start();
        imagepng($canvas);
        imagedestroy($canvas);
        $content = ob_get_clean();
        return $content;
    }

}
