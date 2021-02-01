<?php

namespace App\Tests\Feature;

use App\Services\UserRequestValidator;
use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminsApiTests extends FeatureTestCase
{
    public function testAdminStore()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => 2
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('id', $this->response);
        $this->assertGreaterThan(0, $this->response);
        unset($this->response['id']);
        $this->assertResponse([
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'isAdmin' => false
        ]);

    }

    public function testAdminStoreIfWrongRole()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '-1'
        ], $this->getAdminAuthClient());
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('errors', $this->response);
        $this->assertStringContainsString(UserRequestValidator::ROLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testAdminStoreIfInvalidName()
    {
        $this->post('/api/users/store', [
            'name' => self::SHORT_NAME,
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('errors', $this->response);
        $this->assertStringContainsString(UserRequestValidator::NAME_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfExistingEmail()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('errors', $this->response);
        $this->assertStringContainsString(
            UserRequestValidator::THIS_EMAIL_IS_ALREADY_IN_USE_MESSAGE, $this->response['errors']
        );
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfWeakPassword()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'password' => 'password',
            'role_id' => '2'
        ], $this->getAdminAuthClient());
        $this->assertArrayHasKey('errors', $this->response);
        $this->assertStringContainsString(
            UserRequestValidator::PASSWORD_IS_COMPROMISED_MESSAGE, $this->response['errors']
        );
        $this->assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminShow()
    {
        $this->get('/api/users/show/' . self::EXISTING_USER_ID, $this->getAdminAuthClient());
        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::EXISTING_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'isAdmin' => false,
        ]);
    }

    public function testAdminUpdate(): void
    {
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, [
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '1'
        ], $this->getAdminAuthClient());

        $this->assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'isAdmin' => true
        ]);
    }

    public function testAdminDelete()
    {
        $this->delete('/api/users/delete/' . self::EXISTING_USER_ID, $this->getAdminAuthClient());
        $this->assertResponseOk();
    }
}

