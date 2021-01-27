<?php

namespace App\Tests\Feature;

use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthApiTests extends FeatureTestCase
{
    public function testRegister(): void
    {
        $email = $this->registerAsUser();
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $this->assertArrayHasKey('id', $response);
        $this->assertGreaterThan(0, $response['id']);
        unset($response['id']);
        $expectedResponse = [
            'name' => self::VALID_NAME,
            'email' => $email,
            'isAdmin' => false
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testRegisterWithExistingEmail(): void
    {
        $this->post('/api/register', [
            'name' => self::VALID_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterWithWeakPassword(): void
    {
        $email = $this->getNonExistingValidEmail();
        $this->post('/api/register', [
            'name' => self::VALID_NAME,
            'email' => $email,
            'password' => self::WEAK_PASSWORD,
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterWithoutName(): void
    {
        $email = $this->getNonExistingValidEmail();
        $this->post('/api/register', [
            'email' => $email,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterWithoutShortName(): void
    {
        $email = $this->getNonExistingValidEmail();
        $this->post('/api/register', [
            'name' => 'A',
            'email' => $email,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterWithoutEmail(): void
    {
        $this->post('/api/register', [
            'name' => self::VALID_NAME,
            'password' => self::VALID_PASSWORD,
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterWithoutPassword(): void
    {
        $email = $this->getNonExistingValidEmail();
        $this->post('/api/register', [
            'name' => self::VALID_NAME,
            'email' => $email,
        ]);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testLogin(): void
    {
        $this->loginAsUser();
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $token = $response['token'];
        $refreshToken = $response['refresh_token'];
        $this->assertGreaterThan(0, strlen($token));
        $this->assertGreaterThan(0, strlen($refreshToken));
    }

    public function testRefreshAction(): void
    {
        $this->loginAsUser();
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $this->assertArrayHasKey('refresh_token', $response);
        $refreshToken = $response['refresh_token'];

        $this->post('/api/token/refresh', [
            'refresh_token' => $refreshToken
        ]);

        $response = $this->getArrayResponse();
        $this->assertGreaterThan(0, strlen($response['token']));
        $this->assertEquals($refreshToken, $response['refresh_token']);

        $this->get('/api/users/show/' . self::EXISTING_USER_ID, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $response['token'],
            'CONTENT_TYPE' => 'application/json'
        ]);
        $response = $this->getArrayResponse();
        $expectedResponseAfterShowAction = [
            'id' => self::EXISTING_USER_ID,
            'name' => self::EXISTING_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'isAdmin' => false,
        ];
        $this->assertEquals($expectedResponseAfterShowAction, $response);
    }

    public function testRefreshActionIfExpiredRefreshToken(): void
    {
        $this->post('/api/token/refresh', [
            'refresh_token' => self::EXPIRED_TOKEN
        ]);
        $response = $this->getArrayResponse();
        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $expectedResponse = [
            'errors' => 'Expired refresh token'
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testLoginWithIncorrectEmail(): void
    {
        $this->post('/api/login', [
            'email' => $this->getNonExistingValidEmail(),
            'password' => self::EXISTING_USER_PASSWORD,
        ]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testLoginWithIncorrectPassword(): void
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
            'password' => 'wrongPassword',
        ]);
        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginWithoutPassword(): void
    {
        $this->post('/api/login', [
            'email' => self::EXISTING_USER_EMAIL,
        ]);
        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginWithoutEmail(): void
    {
        $this->post('/api/login', [
            'password' => self::EXISTING_USER_PASSWORD,
        ]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }


    public function testRefreshActionAfterUpdateEmail()
    {
        $this->loginAsUser();
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $refreshToken = $response['refresh_token'];

        $newData = [
            'name' => 'user',
            'email' => 'newUserEmail@email.com',
            'password' => 'NeWPasWord!2341**',
            'role_id' => 2
        ];
        $this->put('/api/users/update/' . self::EXISTING_USER_ID, $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);
        $response = $this->getArrayResponse();
        $this->assertResponseOk();

        $expectedResponse  = [
            'id' => self::EXISTING_USER_ID,
            'name' => $newData['name'],
            'email' => $newData['email'],
            'isAdmin' => false
        ];

        $this->assertEquals($expectedResponse, $response);

        $this->post('/api/token/refresh', [
            'refresh_token' => $refreshToken
        ]);
        $response = $this->getArrayResponse();
        $this->assertGreaterThan(0, strlen($response['token']));
        $this->assertEquals($refreshToken, $response['refresh_token']);
        $newAccessToken = $response['token'];

        $this->get('/api/users/show/' . self::EXISTING_USER_ID, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $newAccessToken,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->getArrayResponse();
        $this->assertEquals($newData['email'], $response['email'] );
    }
}
