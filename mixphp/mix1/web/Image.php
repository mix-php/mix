<?php

namespace mix\web;

/**
 * Image类
 * @author 刘健 <coder.liu@qq.com>
 */
class Image
{

    // 居中剪裁
    const CROP_CENTER = 0;

    // 顶部剪裁
    const CROP_TOP = 1;

    // 图片的路径
    public $filename;

    // 图片宽度
    public $width;

    // 图片高度
    public $height;

    // 图片的 MIME 信息
    public $mime;

    // 处理过后的图片
    protected $image;

    // 打开图片
    public static function open($filename)
    {
        $imageInfo = getimagesize($filename);
        $object = new self();
        $object->filename = $filename;
        $object->width = $imageInfo[0];
        $object->height = $imageInfo[1];
        $object->mime = $imageInfo['mime'];
        return $object;
    }

    // 获取图片文件大小
    public function getSize()
    {
        return filesize($this->filename);
    }

    // 选择执行
    protected function switchExecute($action)
    {
        switch ($this->mime) {
            case 'image/gif':
                $imageCreate = 'imagecreatefromgif';
                $imageSave = 'imagegif';
                break;
            case 'image/png':
                $imageCreate = 'imagecreatefrompng';
                $imageSave = 'imagepng';
                break;
            case 'image/jpeg':
                $imageCreate = 'imagecreatefromjpeg';
                $imageSave = 'imagejpeg';
                break;
            default:
                $imageCreate = 'imagecreatefromjpeg';
                $imageSave = 'imagejpeg';
                break;
        }
        switch ($action) {
            case 'CREATE':
                return $imageCreate;
                break;
            case 'SAVE':
                return $imageSave;
                break;
        }
    }

    // 等比缩放
    public function resize($width, $height)
    {
        // 创建图像
        $imageCreate = $this->switchExecute('CREATE');
        $image = $imageCreate($this->filename);
        // 计算比率
        $widthRatio = $width / $this->width;
        $heightRatio = $height / $this->height;
        if ($widthRatio >= 1 && $heightRatio >= 1) {
            // 源图宽高均小于要设置的值
            $canvas = $image;
        } else {
            // 根据缩放倍率小的宽或者高缩放
            if ($widthRatio < $heightRatio) {
                $zoomWidth = $width;
                $zoomHeight = $this->height * ($width / $this->width);
            } else {
                $zoomWidth = $this->width * ($height / $this->height);
                $zoomHeight = $height;
            }
            // 声明图片资源
            $canvas = imagecreatetruecolor($zoomWidth, $zoomHeight);
            // 保留png透明色
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            // 缩放
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $zoomWidth, $zoomHeight, $this->width, $this->height);
            // 销毁资源
            imagedestroy($image);
        }
        // 保存处理好的图片
        $this->image = $canvas;
        // 返回对象
        return $this;
    }

    // 图片剪裁
    public function crop($width, $height, $mode = self::CROP_CENTER)
    {
        // 创建图像
        $imageCreate = $this->switchExecute('CREATE');
        $image = $imageCreate($this->filename);
        // 计算比率
        $imageRatio = $this->height / $this->width;
        $cropRatio = $height / $width;
        if ($imageRatio > $cropRatio) {
            // 源图过高
            $cropWidth = $this->width;
            $cropHeight = $this->width * $cropRatio;
            $cropX = 0;
            if ($mode == self::CROP_CENTER) {
                $cropY = ($this->height - $cropHeight) / 2;
            }
            if ($mode == self::CROP_TOP) {
                $cropY = 0;
            }
        } elseif ($imageRatio < $cropRatio) {
            // 源图过宽
            $cropWidth = $this->height / $cropRatio;
            $cropHeight = $this->height;
            if ($mode == self::CROP_CENTER || $mode == self::CROP_TOP) {
                $cropX = ($this->width - $cropWidth) / 2;
            }
            $cropY = 0;
        } else {
            // 源图相等
            $cropWidth = $this->width;
            $cropHeight = $this->height;
            $cropX = 0;
            $cropY = 0;
        }
        // 声明图片资源
        $canvas = imagecreatetruecolor($width, $height);
        $tmpCanvas = imagecreatetruecolor($cropWidth, $cropHeight);
        // 保留png透明色
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagealphablending($tmpCanvas, false);
        imagesavealpha($tmpCanvas, true);
        // 裁剪
        imagecopy($tmpCanvas, $image, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight);
        // 缩放
        imagecopyresampled($canvas, $tmpCanvas, 0, 0, 0, 0, $width, $height, $cropWidth, $cropHeight);
        // 销毁资源
        imagedestroy($image);
        imagedestroy($tmpCanvas);
        // 保存处理好的图片
        $this->image = $canvas;
        // 返回对象
        return $this;
    }

    // 保存
    public function save()
    {
        if (isset($this->image)) {
            $imageSave = $this->switchExecute('SAVE');
            $imageSave($this->image, $this->filename);
        }
    }

    // 另存为
    public function saveAs($filename)
    {
        if (!isset($this->image)) {
            // 创建图像
            $imageCreate = $this->switchExecute('CREATE');
            $this->image = $imageCreate($this->filename);
        }
        $imageSave = $this->switchExecute('SAVE');
        $imageSave($this->image, $filename);
    }

}
