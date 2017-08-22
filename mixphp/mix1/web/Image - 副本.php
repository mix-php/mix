<?php

/**
 * Image类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

class Image
{

    // 文件地址
    public $location;

    // 图片宽度
    public $width;

    // 图片高度
    public $height;

    // 图片的 MIME 信息
    public $mime;

    // 处理过后的图片
    protected $image;

    // 打开文件
    public static function open($location)
    {
        $imageInfo = getimagesize($location);
        $object = new self();
        $object->location = $location;
        $object->width = $imageInfo[0];
        $object->height = $imageInfo[1];
        $object->mime = $imageInfo['mime'];
        return $object;
    }

    // 等比缩放
    public function resize($width, $height)
    {
        switch ($this->mime) {
            case 'image/gif':
                $imageCreate = 'imagecreatefromgif';
                break;
            case 'image/png':
                $imageCreate = 'imagecreatefrompng';
                break;
            case 'image/jpeg':
                $imageCreate = 'imagecreatefromjpeg';
                break;
            default:
                $imageCreate = 'imagecreatefromjpeg';
                break;
        }
        $image = $imageCreate($this->location);
        $widthRatio = $width / $this->width;
        $heightRatio = $height / $this->height;
        if ($widthRatio >= 1 && $heightRatio >= 1) {
            // 源图宽高均小于要设置的值
            $canvas = $image;
        } else {
            // 根据缩放倍率小的宽或者高缩放
            if ($widthRatio < $heightRatio) {
                $widthZoom = $width;
                $heightZoom = $this->height * ($width / $this->width);
            } else {
                $widthZoom = $this->width * ($height / $this->height);
                $heightZoom = $height;
            }
            // 声明图片资源
            $canvas = imagecreatetruecolor($widthZoom, $heightZoom);
            // 保留png透明色
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            // 缩放
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $widthZoom, $heightZoom, $this->width, $this->height);
        }
        // 销毁资源
        imagedestroy($image);
        // 保存处理好的图片
        $this->image = $canvas;
        // 返回对象
        return $this;
    }

    // 居中剪裁
    public function crop($width, $height)
    {
        $source_path = $this->source_image;
        $target_width = $this->width;
        $target_height = $this->height;
        list($source_width, $source_height) = $imagesize = getimagesize($source_path);
        $source_mime = $imagesize['mime'];
        $source_ratio = $source_height / $source_width;
        $target_ratio = $target_height / $target_width;
        if ($source_ratio > $target_ratio) {
            // 源图过高
            $cropped_width = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x = 0;
            $source_y = ($source_height - $cropped_height) / 2;
        } elseif ($source_ratio < $target_ratio) {
            // 源图过宽
            $cropped_width = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x = ($source_width - $cropped_width) / 2;
            $source_y = 0;
        } else {
            // 源图适中
            $cropped_width = $source_width;
            $cropped_height = $source_height;
            $source_x = 0;
            $source_y = 0;
        }
        switch ($source_mime) {
            case 'image/gif':
                $source_func = 'imagecreatefromgif';
                $output_func = 'imagegif';
                $suffix = '.gif';
                break;
            case 'image/png':
                $source_func = 'imagecreatefrompng';
                $output_func = 'imagepng';
                $suffix = '.png';
                break;
            case 'image/jpeg':
                $source_func = 'imagecreatefromjpeg';
                $output_func = 'imagejpeg';
                $suffix = '.jpg';
                break;
            default:
                $source_func = 'imagecreatefromjpeg'; // 兼容app, 许多app上传的图片无mime信息
                $output_func = 'imagejpeg';
                $suffix = '.jpg';
                break;
        }
        $source_image = $source_func($source_path);
        // 声明图片资源
        $target_image = imagecreatetruecolor($target_width, $target_height);
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
        switch ($imageInfo['mime']) {
            case 'image/gif':
                $source_func = 'imagecreatefromgif';
                $output_func = 'imagegif';
                $suffix = '.gif';
                break;
            case 'image/png':
                $source_func = 'imagecreatefrompng';
                $output_func = 'imagepng';
                $suffix = '.png';
                break;
            case 'image/jpeg':
                $source_func = 'imagecreatefromjpeg';
                $output_func = 'imagejpeg';
                $suffix = '.jpg';
                break;
            default:
                $source_func = 'imagecreatefromjpeg'; // 兼容app, 许多app上传的图片无mime信息
                $output_func = 'imagejpeg';
                $suffix = '.jpg';
                break;
        }
    }

    // 另存为
    public function saveAs($file)
    {

    }

}
