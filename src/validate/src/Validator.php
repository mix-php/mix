<?php

namespace Mix\Validate;

use Mix\Bean\BeanInjector;
use Mix\Validate\Exception\InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class Validator
 * @package Mix\Validate
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class Validator implements \JsonSerializable
{

    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var UploadedFileInterface[]
     */
    public $uploadedFiles = [];

    /**
     * @var string
     */
    protected $_scenario = '';

    /**
     * @var array
     */
    protected $_validators = [
        'integer'      => \Mix\Validate\Validator\IntegerValidator::class,
        'double'       => \Mix\Validate\Validator\DoubleValidator::class,
        'alpha'        => \Mix\Validate\Validator\AlphaValidator::class,
        'alphaNumeric' => \Mix\Validate\Validator\AlphaNumericValidator::class,
        'string'       => \Mix\Validate\Validator\StringValidator::class,
        'in'           => \Mix\Validate\Validator\InValidator::class,
        'date'         => \Mix\Validate\Validator\DateValidator::class,
        'email'        => \Mix\Validate\Validator\EmailValidator::class,
        'phone'        => \Mix\Validate\Validator\PhoneValidator::class,
        'url'          => \Mix\Validate\Validator\UrlValidator::class,
        'compare'      => \Mix\Validate\Validator\CompareValidator::class,
        'match'        => \Mix\Validate\Validator\MatchValidator::class,
        'call'         => \Mix\Validate\Validator\CallValidator::class,
        'file'         => \Mix\Validate\Validator\FileValidator::class,
        'image'        => \Mix\Validate\Validator\ImageValidator::class,
    ];

    /**
     * @var array
     */
    protected $_errors = [];

    /**
     * Validator constructor.
     * @param array $attributes
     * @param UploadedFileInterface[] $uploadedFiles
     */
    public function __construct(array $attributes, array $uploadedFiles = [])
    {
        // 为了效验对象数组类型，使用BeanInjector
        BeanInjector::inject($this, [
            'attributes'    => $attributes,
            'uploadedFiles' => $uploadedFiles,
        ]);
    }

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [];
    }

    /**
     * 消息
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * 设置当前场景
     * @param string $scenario
     */
    public function setScenario(string $scenario)
    {
        $scenarios = $this->scenarios();
        if (!isset($scenarios[$scenario])) {
            throw new InvalidArgumentException("场景不存在：{$scenario}");
        }
        if (!isset($scenarios[$scenario]['required'])) {
            $scenarios[$scenario]['required'] = [];
        }
        if (!isset($scenarios[$scenario]['optional'])) {
            $scenarios[$scenario]['optional'] = [];
        }
        $this->_scenario = $scenarios[$scenario];
    }

    /**
     * 验证
     * @return bool
     */
    public function validate()
    {
        if (!isset($this->_scenario)) {
            throw new InvalidArgumentException("场景未设置");
        }
        $this->_errors      = [];
        $scenario           = $this->_scenario;
        $scenarioAttributes = array_merge($scenario['required'], $scenario['optional']);
        $rules              = $this->rules();
        $messages           = $this->messages();
        // 判断是否定义了规则
        foreach ($scenarioAttributes as $attribute) {
            if (!isset($rules[$attribute])) {
                throw new InvalidArgumentException("属性 {$attribute} 未定义规则");
            }
        }
        // 验证器验证
        foreach ($rules as $attribute => $rule) {
            if (!in_array($attribute, $scenarioAttributes)) {
                continue;
            }
            $validatorType = array_shift($rule);
            if (!isset($this->_validators[$validatorType])) {
                throw new InvalidArgumentException("属性 {$attribute} 的验证类型 {$validatorType} 不存在");
            }
            $attributeValue = isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
            // 实例化
            $validatorClass           = $this->_validators[$validatorType];
            $validator                = new $validatorClass([
                'isRequired'     => in_array($attribute, $scenario['required']),
                'options'        => $rule,
                'attribute'      => $attribute,
                'attributeValue' => $attributeValue,
                'messages'       => $messages,
                'attributes'     => $this->attributes,
                'uploadedFiles'  => $this->uploadedFiles,
            ]);
            $validator->mainValidator = $this;
            // 验证
            if (!$validator->validate()) {
                // 记录错误消息
                $this->_errors[$attribute] = $validator->errors;
            }
        }
        return empty($this->_errors);
    }

    /**
     * 返回全部错误
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * 返回一条错误
     * @return string
     */
    public function getError()
    {
        $errors = $this->_errors;
        if (empty($errors)) {
            return '';
        }
        $item  = array_shift($errors);
        $error = array_shift($item);
        return $error;
    }

    /**
     * Json serialize
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $val) {
            if (in_array($key, ['attributes', 'uploadedFiles', '_scenario', '_validators', '_errors'])) {
                continue;
            }
            $data[$key] = $val;
        }
        return $data;
    }

}
