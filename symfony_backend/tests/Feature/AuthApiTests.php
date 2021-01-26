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

    public function testLoginWithRefreshToken(): void
    {
        $this->loginAsUser();
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $token = $response['token'];
        $refreshToken = $response['refresh_token'];

        $this->get('api/token/refresh',[
            'refresh_token' => $refreshToken
        ]);
        $this->assertResponseStatus(Response::HTTP_OK);
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
}
