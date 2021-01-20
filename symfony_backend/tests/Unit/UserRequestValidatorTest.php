<?php

namespace App\Tests\Unit;

use App\Entity\Role;
use App\Requests\UserRequest;
use App\Services\UserRequestValidator;
use PHPUnit\Framework\TestCase;

class UserRequestValidatorTest extends TestCase
{
    private const VALID_NAME = 'test';
    private const VALID_EMAIL = 'test@email.com';
    private const VALID_PASSWORD = 'password1q2w3e4r5t6y7!';
    private const INVALID_NAME = 't';
    private const INVALID_PASSWORD = '111111';
    private const INVALID_EMAIL = 'email.email.com';


    public function testValidate()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = self::VALID_EMAIL;
        $request->password = self::VALID_PASSWORD;
        $request->role = new Role();

        $violations = UserRequestValidator::validate($request, true);
        $this->assertCount(0, $violations);
    }

    public function testValidateWithoutRole()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = self::VALID_EMAIL;
        $request->password = self::VALID_PASSWORD;
        $request->role = null;

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(0, $violations);
    }

    public function testValidateWithoutName()
    {
        $request = new UserRequest();
        $request->name = '';
        $request->email =  self::VALID_EMAIL;
        $request->password = self::VALID_PASSWORD;
        $violations = UserRequestValidator::validate($request);
        $this->assertCount(2, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::NAME_IS_TOO_SHORT_MESSAGE, $errorMessage);
    }

    public function testValidateWithShortName()
    {
        $request = new UserRequest();
        $request->name = self::INVALID_NAME;
        $request->email = self::VALID_EMAIL;
        $request->password = self::VALID_PASSWORD;

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::NAME_IS_TOO_SHORT_MESSAGE, $errorMessage);

    }

    public function testValidateWithLongName()
    {

        $request = new UserRequest();
        $request->name = $this->generateLargeString();
        $request->email = self::VALID_EMAIL;
        $request->password = self::VALID_PASSWORD;

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::NAME_IS_TOO_LONG_MESSAGE, $errorMessage);
    }

    public function testValidateWithoutEmail()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = '';
        $request->password = self::VALID_PASSWORD;

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::EMAIL_IS_REQUIRED_MESSAGE, $errorMessage);
    }

    public function testValidateWithInvalidEmail()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = self::INVALID_EMAIL;
        $request->password = self::VALID_PASSWORD;

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::EMAIL_IS_INVALID_MESSAGE, $errorMessage);
    }

    public function testValidateWithoutPassword()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = self::VALID_EMAIL;
        $request->password = '';

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(2, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::PASSWORD_IS_REQUIRE_MESSAGE, $errorMessage);
    }

    public function testValidateWithShortPassword()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = self::VALID_EMAIL;
        $request->password = self::INVALID_PASSWORD;

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(2, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::PASSWORD_IS_TOO_SHORT_MESSAGE, $errorMessage);
    }

    public function testValidateWithLongPassword()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = self::VALID_EMAIL;
        $request->password = $this->generateLargeString();

        $violations = UserRequestValidator::validate($request);
        $this->assertCount(1, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::PASSWORD_IS_TOO_LONG_MESSAGE, $errorMessage);
    }

    public function testValidateWithWeakPassword()
    {
        $request = new UserRequest();
        $request->name = self::VALID_NAME;
        $request->email = self::VALID_EMAIL;
        $request->password = self::INVALID_PASSWORD;
        $violations = UserRequestValidator::validate($request);
        $this->assertCount(2, $violations);
        $errorMessage = (string)$violations;
        $this->assertStringContainsString(UserRequestValidator::PASSWORD_IS_COMPROMISED_MESSAGE, $errorMessage);
    }

    /**
     * @return string
     */
    private function generateLargeString(): string
    {
        $str = '';

        for ($i = 0; $i < 256; $i++) {
            $str .= 'h';
        }

        return $str;
    }
}
