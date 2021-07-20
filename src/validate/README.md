> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix Validate

Validator based on PSR-7 standard

基于 PSR-7 标准的验证器

## Installation

```
composer require mix/validate
```

## 创建表单

继承 `Mix\Validate\Validator` 并定义：

- `public $name` 字段名
- `rules()` 验证规则
- `scenarios()` 验证场景
- `messages()` 错误消息

```php
<?php

namespace App\Forms;

use Mix\Validate\Validator;

class UserForm extends Validator
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $age;

    /**
     * @var string
     */
    public $email;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name'  => ['string', 'maxLength' => 25, 'filter' => ['trim']],
            'age'   => ['integer', 'unsigned' => true, 'min' => 1, 'max' => 120],
            'email' => ['email'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            'create' => ['required' => ['name'], 'optional' => ['email', 'age']],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'  => '名称不能为空.',
            'name.maxLength' => '名称最多不能超过25个字符.',
            'age.integer'    => '年龄必须是数字.',
            'age.unsigned'   => '年龄不能为负数.',
            'age.min'        => '年龄不能小于1.',
            'age.max'        => '年龄不能大于120.',
            'email'          => '邮箱格式错误.',
        ];
    }

}
```

## 在控制器中验证

```php
// 使用表单验证
$form = new UserForm($request->getAttributes());
if (!$form->setScenario('create')->validate()) {
    $data = ['code' => 1, 'message' => $form->getError()];
    $ctx->JSON(200, $data);
    return;
}

// 将表单对象直接传递到模型中保存数据
(new UserModel())->add($form);
```

- `$form->getError() : string` 获取单条错误信息
- `$form->getErrors() : array` 获取全部错误信息

## 验证规则

全部的验证类型与对应的验证选项如下

```php
public function rules()
{
    return [
        'a' => ['integer', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        'b' => ['double', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        'c' => ['alpha', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        'd' => ['alphaNumeric', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        'e' => ['string', 'length' => 10, 'minLength' => 3, 'maxLength' => 5, 'filter' => ['trim', 'strip_tags', 'htmlspecialchars']],
        'f' => ['email', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        'g' => ['phone'],
        'h' => ['url', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        'i' => ['in', 'range' => ['A', 'B'], 'strict' => true],
        'j' => ['date', 'format' => 'Y-m-d'],
        'k' => ['compare', 'compareAttribute' => 'a'],
        'l' => ['match', 'pattern' => '/^[\w]{1,30}$/'],
        'm' => ['call', 'callback' => [$this, 'check']],
        'n' => ['file', 'mimes' => ['audio/mp3', 'video/mp4'], 'maxSize' => 1024 * 1],
        'r' => ['image', 'mimes' => ['image/gif', 'image/jpeg', 'image/png'], 'maxSize' => 1024 * 1],
    ];
}
```

### call 验证类型

该类型为用户自定义验证规则，callback 内指定一个用户自定义的方法来验证

```php
public function check($fieldValue)
{
    // 验证代码
    // ...
    
    return true;
}
```

### file / image 验证类型

该类型用来验证文件，包含的两个验证选项如下：

- mimes：输入你想要限制的文件mime类型，[MIME参考手册](http://www.w3school.com.cn/media/media_mimeref.asp)
- maxSize：允许的文件最大尺寸，单位 KB

验证成功后模型类会增加一个同名属性，该属性为 Psr\Http\Message\UploadedFileInterface 类的实例化对象，可直接调用 $this->[attributeName]->moveTo($targetPath) 移动到你需要存放的位置

## 静态调用

```php
// 验证是否为字母与数字
Mix\Validate\Validate::isAlphaNumeric($value);

// 验证是否为字母
Mix\Validate\Validate::isAlpha($value); 

// 验证是否为日期
Mix\Validate\Validate::isDate($value, $format); 

// 验证是否为浮点数
Mix\Validate\Validate::isDouble($value); 

// 验证是否为邮箱
Mix\Validate\Validate::isEmail($value); 

// 验证是否为整数
Mix\Validate\Validate::isInteger($value);

// 验证是否在某个范围
Mix\Validate\Validate::in($value, $range, $strict = false); 

// 正则验证
Mix\Validate\Validate::match($value, $pattern); 

// 验证是否为手机
Mix\Validate\Validate::isPhone($value); 

// 验证是否为网址
Mix\Validate\Validate::isUrl($value); 
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
