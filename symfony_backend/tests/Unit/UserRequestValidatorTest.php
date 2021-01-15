<?php

namespace App\Tests\Unit;

use App\Entity\Role;
use App\Requests\UserRequest;
use App\Services\UserRequestValidator;
use PHPUnit\Framework\TestCase;

class UserRequestValidatorTest extends TestCase
{
    public function testValidate()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = 'test@email.com';
        $request->password = 'password1q2w3e4r5t6y7u8i9o';
        $request->role = new Role();

        $violations = UserRequestValidator::validate($request, true);
        $this->assertCount(0, $violations);
    }

    public function testValidateWithoutRole()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = 'test@email.com';
        $request->password = 'password1q2w3e4r5t6y7u8i9o';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(0, $violations);
    }

    public function testValidateWithoutName()
    {
        $request = new UserRequest();
        $request->name = '';
        $request->email = 'test@email.com';
        $request->password = 'password1q2w3e4r5t6y7u8i9o';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(2, $violations);
    }

    public function testValidateWithShortName()
    {
        $request = new UserRequest();
        $request->name = 't';
        $request->email = 'test@email.com';
        $request->password = 'password1q2w3e4r5t6y7u8i9o';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
    }

    public function testValidateWithLongName()
    {

        $request = new UserRequest();
        $request->name = $this->generateLargeString();
        $request->email = 'test@email.com';
        $request->password = 'password1q2w3e4r5t6y7u8i9o';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
    }

    public function testValidateWithoutEmail()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = '';
        $request->password = 'password1q2w3e4r5t6y7u8i9o';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
    }

    public function testValidateWithInvalidEmail()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = 'test.email.com';
        $request->password = 'password1q2w3e4r5t6y7u8i9o';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
    }

    public function testValidateWithoutPassword()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = 'test@test.com';
        $request->password = '';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(2, $violations);
    }

    public function testValidateWithShortPassword()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = 'test@test.com';
        $request->password = '1q2wjg*';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
    }

    public function testValidateWithLongPassword()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = 'test@test.com';
        $request->password = $this->generateLargeString();

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
    }

    public function testValidateWithWeakPassword()
    {
        $request = new UserRequest();
        $request->name = 'test';
        $request->email = 'test@test.com';
        $request->password = 'password';
        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
    }

    /**
     * @return string
     */
    private function generateLargeString():string
    {
        $str = '';

        for ($i = 0; $i < 256; $i++) {
            $str .= 'h';
        }

        return $str;
    }
}