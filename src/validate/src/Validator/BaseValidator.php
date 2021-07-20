<?php

namespace Mix\Validate\Validator;

use Mix\Validate\Validator;
use Mix\Validate\Exception\InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class BaseValidator
 * @package Mix\Validate\Validator
 */
abstract class BaseValidator
{

    /**
     * 必填字段
     * @var bool
     */
    public $isRequired;

    /**
     * 需要验证的选项
     * @var array
     */
    public $options;

    /**
     * 当前属性名
     * @var string
     */
    public $attribute;

    /**
     * 当前属性值
     * @var mixed
     */
    public $attributeValue;

    /**
     * 全部消息
     * @var array
     */
    public $messages;

    /**
     * 全部属性
     * @var array
     */
    public $attributes;

    /**
     * 主验证器的引用
     * @var Validator
     */
    public $mainValidator;

    /**
     * 上传的文件
     * @var UploadedFileInterface[]
     */
    public $uploadedFiles = [];

    /**
     * 错误
     * @var array
     */
    public $errors = [];

    /**
     * 设置
     * @var array
     */
    protected $settings = [];

    /**
     * 初始化选项
     * @var array
     */
    protected $initOptions = [];

    /**
     * 启用的选项
     * @var array
     */
    protected $enabledOptions = [];

    /**
     * BaseValidator constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * 验证
     * @return bool
     */
    public function validate()
    {
        // 清扫数据
        $this->errors = [];
        $this->settings = [];
        // 验证
        if ($this->required() && $this->scalar() && !(is_null($this->attributeValue) || $this->attributeValue === '')) {
            // 预处理
            foreach ($this->options as $name => $option) {
                if (!in_array($name, $this->enabledOptions)) {
                    throw new InvalidArgumentException("属性 {$this->attribute} 的验证选项 {$name} 不存在");
                }
                // 不存在的选项转为设置
                if (!method_exists($this, $name)) {
                    $this->settings[$name] = $option;
                    unset($this->options[$name]);
                }
            }
            // 执行初始化选项验证
            foreach ($this->initOptions as $option) {
                $this->options = array_merge([$option => null], $this->options);
            }
            // 执行全部选项验证
            foreach ($this->options as $name => $param) {
                $success = $this->$name($param);
                if (!$success) {
                    break;
                }
            }
        }
        $result = empty($this->errors);
        // 属性赋值
        $attribute = $this->attribute;
        if (!$result) {
            $this->mainValidator->$attribute = null;
        } else {
            // 属性赋值
            $this->mainValidator->$attribute = $this->attributeValue;
        }
        // 返回
        return $result;
    }

    /**
     * 获取消息
     * @param $attribute
     * @param $option
     * @return mixed|null
     */
    protected function getMessage($attribute, $option)
    {
        $messages = $this->messages;
        if (isset($messages["{$attribute}.{$option}"])) {
            return $messages["{$attribute}.{$option}"];
        }
        if (isset($messages[$attribute])) {
            return $messages[$attribute];
        }
        return null;
    }

    /**
     * 设置错误消息
     * @param $option
     * @param $defaultMessage
     */
    protected function setError($option, $defaultMessage)
    {
        $message = $this->getMessage($this->attribute, $option);
        if (is_null($message)) {
            $message = $defaultMessage;
        }
        $this->errors[$option] = $message;
    }

    /**
     * 必需验证
     * @return bool
     */
    protected function required()
    {
        $value = $this->attributeValue;
        if ($this->isRequired && (is_null($value) || $value === '')) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能为空.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 标量类型验证
     * @return bool
     */
    protected function scalar()
    {
        $value = $this->attributeValue;
        if (!is_null($value) && !is_scalar($value)) {
            // 文件/图片验证器忽略该类型的验证
            if ($this instanceof FileValidator) {
                return true;
            }
            // 回调验证器忽略该类型的验证
            if ($this instanceof CallValidator) {
                return true;
            }
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不是标量类型.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 无符号验证
     * @param $param
     * @return bool
     */
    protected function unsigned($param)
    {
        $value = $this->attributeValue;
        if ($param && substr($value, 0, 1) == '-') {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能为负数.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 最小数值验证
     * @param $param
     * @return bool
     */
    protected function min($param)
    {
        $value = $this->attributeValue;
        if (is_numeric($value) && $value < $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能小于{$param}.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 最大数值验证
     * @param $param
     * @return bool
     */
    protected function max($param)
    {
        $value = $this->attributeValue;
        if (is_numeric($value) && $value > $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能大于{$param}.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 固定长度验证
     * @param $param
     * @return bool
     */
    protected function length($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) != $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}长度只能为{$param}位.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 最小长度验证
     * @param $param
     * @return bool
     */
    protected function minLength($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) < $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}长度不能小于{$param}位.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    /**
     * 最大长度验证
     * @param $param
     * @return bool
     */
    protected function maxLength($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) > $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}长度不能大于{$param}位.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
