<?php

namespace App\Tests\Feature;

use App\Constants\ResponseMessages;
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

        self::assertResponseOk();
        self::assertArrayHasKey('id', $this->response);
        self::assertGreaterThan(0, $this->response);
        unset($this->response['id']);

        self::assertResponse([
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

        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::ROLE_IS_REQUIRED_MESSAGE, $this->response['errors']);
    }

    public function testAdminStoreIfInvalidName()
    {
        $this->post('/api/users/store', [
            'name' => self::SHORT_NAME,
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ], $this->getAdminAuthClient());

        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(ResponseMessages::NAME_IS_TOO_SHORT_MESSAGE, $this->response['errors']);
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfExistingEmail()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ], $this->getAdminAuthClient());

        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::EMAIL_ALREADY_IN_USE_MESSAGE, $this->response['errors']
        );
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfWeakPassword()
    {
        $this->post('/api/users/store', [
            'name' => self::VALID_NAME,
            'email' => self::VALID_EMAIL,
            'password' => 'password',
            'role_id' => '2'
        ], $this->getAdminAuthClient());

        self::assertArrayHasKey('errors', $this->response);
        self::assertStringContainsString(
            ResponseMessages::PASSWORD_IS_COMPROMISED_MESSAGE, $this->response['errors']
        );
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminShow()
    {
        $this->get('/api/users/show/' . self::EXISTING_USER_ID, $this->getAdminAuthClient());

        self::assertResponseOk();
        self::assertResponse([
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

        self::assertResponseOk();
        self::assertResponse([
            'id' => self::EXISTING_USER_ID,
            'name' => self::NEW_USER_NAME,
            'email' => self::NEW_USER_EMAIL,
            'isAdmin' => true
        ]);
    }

    public function testAdminDelete()
    {
        $this->delete('/api/users/delete/' . self::EXISTING_USER_ID, $this->getAdminAuthClient());
        self::assertResponseOk();
    }
}
