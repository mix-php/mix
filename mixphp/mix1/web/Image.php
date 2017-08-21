<?php

/**
 * Image类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

class Image
{

	// 源文件路径
    protected $file;

    // 要设置的宽度
    protected $width;

    // 要设置的高度
    protected $height;

    // 打开文件
    public static function open($file)
    {
        $this->file = $file;
        return new self();
    }

    // 等比缩放
    public function resize($width, $height)
    {
        $source_path                        = $this->source_image;
        $target_width                       = $this->width;
        $target_height                      = $this->height;
        list($source_width, $source_height) = $imagesize = getimagesize($source_path);
        $source_mime                        = $imagesize['mime'];
        switch ($source_mime) {
            case 'image/gif':
                $source_func = 'imagecreatefromgif';
                $output_func = 'imagegif';
                $suffix      = '.gif';
                break;
            case 'image/png':
                $source_func = 'imagecreatefrompng';
                $output_func = 'imagepng';
                $suffix      = '.png';
                break;
            case 'image/jpeg':
                $source_func = 'imagecreatefromjpeg';
                $output_func = 'imagejpeg';
                $suffix      = '.jpg';
                break;
            default:
                $source_func = 'imagecreatefromjpeg'; // 兼容app, 许多app上传的图片无mime信息
                $output_func = 'imagejpeg';
                $suffix      = '.jpg';
                break;
        }
        $source_image = $source_func($source_path);
        $width_ratio  = $target_width / $source_width;
        $height_ratio = $target_height / $source_height;
        // 源图宽高均小于要设置的值
        if ($width_ratio >= 1 && $height_ratio >= 1) {
            $target_image = $source_image;
        } else {
            // 根据缩放倍率小的宽或者高缩放
            if ($width_ratio < $height_ratio) {
                $zoom_width  = $target_width;
                $zoom_height = $source_height * ($target_width / $source_width);
            } else {
                $zoom_height = $target_height;
                $zoom_width  = $source_width * ($target_height / $source_height);
            }
            // 声明图片资源
            $target_image = imagecreatetruecolor($zoom_width, $zoom_height);
            // 保留png透明色
            imagealphablending($target_image, false);
            imagesavealpha($target_image, true);
            // 缩放
            imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $zoom_width, $zoom_height, $source_width, $source_height);
        }
        // 图片地址为url
        if (strpos($source_path, 'http') !== false) {
            $output_func($target_image, __DIR__ . '/tmp' . $suffix);
        } else {
            if ($this->create_thumb) {
                $source_path = str_replace('.', $this->thumb_marker . '.', $source_path);
            }
            $output_func($target_image, $source_path);
        }
        //销毁资源
        imagedestroy($source_image);
        @imagedestroy($target_image);

        return $this;
    }

    // 居中剪裁
    public function crop($width, $height)
    {
        $source_path                        = $this->source_image;
        $target_width                       = $this->width;
        $target_height                      = $this->height;
        list($source_width, $source_height) = $imagesize = getimagesize($source_path);
        $source_mime                        = $imagesize['mime'];
        $source_ratio                       = $source_height / $source_width;
        $target_ratio                       = $target_height / $target_width;
        if ($source_ratio > $target_ratio) {
            // 源图过高
            $cropped_width  = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x       = 0;
            $source_y       = ($source_height - $cropped_height) / 2;
        } elseif ($source_ratio < $target_ratio) {
            // 源图过宽
            $cropped_width  = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x       = ($source_width - $cropped_width) / 2;
            $source_y       = 0;
        } else {
            // 源图适中
            $cropped_width  = $source_width;
            $cropped_height = $source_height;
            $source_x       = 0;
            $source_y       = 0;
        }
        switch ($source_mime) {
            case 'image/gif':
                $source_func = 'imagecreatefromgif';
                $output_func = 'imagegif';
                $suffix      = '.gif';
                break;
            case 'image/png':
                $source_func = 'imagecreatefrompng';
                $output_func = 'imagepng';
                $suffix      = '.png';
                break;
            case 'image/jpeg':
                $source_func = 'imagecreatefromjpeg';
                $output_func = 'imagejpeg';
                $suffix      = '.jpg';
                break;
            default:
                $source_func = 'imagecreatefromjpeg'; // 兼容app, 许多app上传的图片无mime信息
                $output_func = 'imagejpeg';
                $suffix      = '.jpg';
                break;
        }
        $source_image = $source_func($source_path);
        // 声明图片资源
        $target_image  = imagecreatetruecolor($target_width, $target_height);
        $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
        // 保留png透明色
        imagealphablending($target_image, false);
        imagesavealpha($target_image, true);
        imagealphablending($cropped_image, false);
        imagesavealpha($cropped_image, true);
        // 裁剪
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
        // 缩放
        imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
        // 图片地址为url
        if (strpos($source_path, 'http') !== false) {
            $output_func($target_image, __DIR__ . '/tmp' . $suffix);
        } else {
            if ($this->create_thumb) {
                $source_path = str_replace('.', $this->thumb_marker . '.', $source_path);
            }
            $output_func($target_image, $source_path);
        }
        // 销毁资源
        imagedestroy($source_image);
        imagedestroy($target_image);
        imagedestroy($cropped_image);

        return $this;
    }

    // 保存
    public function save()
    {

    }

    // 另存为
    public function saveAs($file)
    {

    }

}
