<?php

namespace App\Tests\Feature;

use App\Tests\TestCases\FeatureTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminsApiTests extends FeatureTestCase
{
    public function testAdminStore()
    {
        $this->loginAsAdmin();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $data = [
            'name' => self::VALID_NAME,
            'email' => $this->getNonExistingValidEmail(),
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ];
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ];
        $this->post('/api/users/store', $data, [], $headers);
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $this->assertGreaterThan(0, $response['id']);
        unset($response['id']);
        $expectedResponse = [
            'name' => $data['name'],
            'email' => $data['email'],
            'isAdmin' => false
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testAdminStoreIfWrongRole()
    {
        $this->loginAsAdmin();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $data = [
            'name' => self::VALID_NAME,
            'email' => $this->getNonExistingValidEmail(),
            'password' => self::VALID_PASSWORD,
            'role_id' => '-1'
        ];
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ];
        $this->post('/api/users/store', $data, [], $headers);
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $this->assertGreaterThan(0, $response['id']);
        unset($response['id']);
        $expectedResponse = [
            'name' => $data['name'],
            'email' => $data['email'],
            'isAdmin' => false
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testAdminStoreIfInvalidName()
    {
        $this->loginAsAdmin();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $data = [
            'name' => 'A',
            'email' => $this->getNonExistingValidEmail(),
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ];
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ];
        $this->post('/api/users/store', $data, [], $headers);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfExistingEmail()
    {
        $this->loginAsAdmin();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $data = [
            'name' => self::VALID_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ];
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ];
        $this->post('/api/users/store', $data, [], $headers);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminStoreIfWeakPassword()
    {
        $this->loginAsAdmin();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $data = [
            'name' => self::VALID_NAME,
            'email' => $this->getNonExistingValidEmail(),
            'password' => 'password',
            'role_id' => '2'
        ];
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ];
        $this->post('/api/users/store', $data, [], $headers);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAdminShow()
    {
        $this->loginAsAdmin();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $this->get('/api/users/show/' . self::EXISTING_USER_ID, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseOk();
        $response = $this->getArrayResponse();
        $expectedResponse = [
            'id' => self::EXISTING_USER_ID,
            'name' => self::EXISTING_USER_NAME,
            'email' => self::EXISTING_USER_EMAIL,
            'isAdmin' => false,
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testAdminUpdate(): void
    {
        $data = $this->loginAsAdminAndCreateUser();
        $newData = [
            'name' => 'Some new name',
            'email' => $this->getNonExistingValidEmail(),
            'password' => self::VALID_PASSWORD,
            'role_id' => '1'
        ];
        $this->put('/api/users/update/' . $data['id'], $newData, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $response = $this->getArrayResponse();
        $this->assertResponseOk();
        $expectedResponse = [
            'id' => $data['id'],
            'name' => $newData['name'],
            'email' => $newData['email'],
            'isAdmin' => true
        ];
        $this->assertEquals($response, $expectedResponse);
    }

    public function testAdminDelete()
    {
        $data = $this->loginAsAdminAndCreateUser();
        $this->get('/api/users/show/' . $data['id'], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseOk();
        $this->delete('/api/users/delete/' . $data['id'], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseOk();
        $this->get('/api/users/show/' . $data['id'], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['token'],
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @return array
     */
    private function loginAsAdminAndCreateUser(): array
    {
        $this->loginAsAdmin();
        $response = $this->getArrayResponse();
        $token = $response['token'];
        $data = [
            'name' => self::VALID_NAME,
            'email' => $this->getNonExistingValidEmail(),
            'password' => self::VALID_PASSWORD,
            'role_id' => '2'
        ];
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ];
        $this->post('/api/users/store', $data, [], $headers);

        return array_merge($this->getArrayResponse(), ['token' => $token]);
    }
}
