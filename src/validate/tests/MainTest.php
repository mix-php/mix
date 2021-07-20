<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $form = new UserForm([
            'name' => 'foo',
            'email' => 'dfsdfsdf',
        ]);
        $ok = $form->scenario('create')->validate();
        $errors = $form->errors();
        $this->assertEquals($ok, false);
        $this->assertNotEmpty($errors);
    }

}

class UserForm extends \Mix\Validate\Validator
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
    public function rules(): array
    {
        return [
            'name' => ['string', 'maxLength' => 25, 'filter' => ['trim']],
            'age' => ['integer', 'unsigned' => true, 'min' => 1, 'max' => 120],
            'email' => ['email'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios(): array
    {
        return [
            'create' => ['required' => ['name'], 'optional' => ['email', 'age']],
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => '名称不能为空.',
            'name.maxLength' => '名称最多不能超过25个字符.',
            'age.integer' => '年龄必须是数字.',
            'age.unsigned' => '年龄不能为负数.',
            'age.min' => '年龄不能小于1.',
            'age.max' => '年龄不能大于120.',
            'email' => '邮箱格式错误.',
        ];
    }

}
