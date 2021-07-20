<?php

namespace Mix\Validate\Validator;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Class FileValidator
 * @package Mix\Validate\Validator
 */
class FileValidator extends BaseValidator
{

    /**
     * 初始化选项
     * @var array
     */
    protected $initOptions = ['upload'];

    /**
     * 启用的选项
     * @var array
     */
    protected $enabledOptions = ['mimes', 'maxSize'];

    /**
     * 验证器名称
     * @var string
     */
    protected $name = '文件';

    /**
     * FileValidator constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        // 获取文件信息
        $uploadedFiles        = $this->uploadedFiles;
        $this->attributeValue = $uploadedFiles[$this->attribute] ?? null; // attributeValue = null 时该类的全部验证方法不会被执行
    }

    /**
     * 上传验证
     * @return bool
     */
    protected function upload()
    {
        /** @var UploadedFileInterface $value */
        $value = $this->attributeValue;
        if ($value->getError() > 0) {
            // 设置错误消息
            switch ($value->getError()) {
                case UPLOAD_ERR_INI_SIZE:
                    $defaultMessage = "上传的{$this->name}大小超过了 php.ini 中 upload_max_filesize 选项限制的值.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $defaultMessage = "上传的{$this->name}大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $defaultMessage = "{$this->name}只有部分被上传.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $defaultMessage = "没有{$this->name}被上传.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $defaultMessage = '找不到临时文件夹.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $defaultMessage = '上传写入失败.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $defaultMessage = '上传扩展错误.';
                    break;
                default:
                    $defaultMessage = '未知上传错误.';
                    break;
            }
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * MIME类型验证
     * @param $param
     * @return bool
     */
    protected function mimes($param)
    {
        /** @var UploadedFileInterface $value */
        $value = $this->attributeValue;
        if (!in_array($value->getClientMediaType(), $param)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}类型不在" . implode(',', $param) . "范围内.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 最大文件大小效验
     * @param $param
     * @return bool
     */
    protected function maxSize($param)
    {
        /** @var UploadedFileInterface $value */
        $value = $this->attributeValue;
        if ($value->getSize() > $param * 1024) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能大于{$param}KB.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
