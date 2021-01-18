<?php

namespace App\Tests\Unit;

use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Requests\UserRequest;
use App\Services\RolesManager;
use App\Services\UserRequestParser;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class UserRequestParserTest extends TestCase
{
    /** @var Role */
    protected Role $defaultRole;

    /** @var Role|null */
    private ?Role $foundRole;

    /** @var UserRequestParser */
    private UserRequestParser $userRequestParser;

    private const NAME = 'test';
    private const EMAIL = 'test@email.com';
    private const PASSWORD = 'password1q2w3e4r5t6y7!';

    public function setUp(): void
    {
        parent::setUp();

        $this->userRequestParser = new UserRequestParser($this->mockRolesManager(), $this->mockRoleRepository());

        $this->defaultRole = new Role();
        $this->defaultRole->setName('user');
        $this->defaultRole->setId(2);

        $this->foundRole = new Role();
        $this->foundRole->setName('someRole');
        $this->foundRole->setId(3);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testParseRequest()
    {
        $request = new Request();
        $request->request->set('name', self::NAME);
        $request->request->set('email', self::EMAIL);
        $request->request->set('password', self::PASSWORD);
        $request->request->set('role_id', 0);
        $parsedRequest = $this->userRequestParser->parseRequest($request, true);
        $this->assertInstanceOf(UserRequest::class, $parsedRequest);
        $this->assertEquals(self::NAME, $parsedRequest->name);
        $this->assertEquals(self::EMAIL, $parsedRequest->email);
        $this->assertEquals(self::PASSWORD, $parsedRequest->password);
        $this->assertNotNull($parsedRequest->role);
        $this->assertEquals($this->foundRole->getName(), $parsedRequest->role->getName());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testParseRequestIfRoleNotFound()
    {
        $this->foundRole = null;
        $request = new Request();
        $request->request->set('name', self::NAME);
        $request->request->set('email', self::EMAIL);
        $request->request->set('password', self::PASSWORD);
        $request->request->set('role_id', 0);
        $parsedRequest = $this->userRequestParser->parseRequest($request, true);
        $this->assertInstanceOf(UserRequest::class, $parsedRequest);
        $this->assertEquals(self::NAME, $parsedRequest->name);
        $this->assertEquals(self::EMAIL, $parsedRequest->email);
        $this->assertEquals(self::PASSWORD, $parsedRequest->password);
        $this->assertNull($parsedRequest->role);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testParseRequestWithDefaultRole()
    {
        $request = new Request();
        $request->request->set('name', self::NAME);
        $request->request->set('email', self::EMAIL);
        $request->request->set('password', self::PASSWORD);
        $request->request->set('role_id', 0);
        $parsedRequest = $this->userRequestParser->parseRequest($request);
        $this->assertInstanceOf(UserRequest::class, $parsedRequest);
        $this->assertEquals(self::NAME, $parsedRequest->name);
        $this->assertEquals(self::EMAIL, $parsedRequest->email);
        $this->assertEquals(self::PASSWORD, $parsedRequest->password);
        $this->assertNotNull($parsedRequest->role);
        $this->assertEquals($this->defaultRole->getName(), $parsedRequest->role->getName());

    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testParseRequestWithoutName()
    {
        $request = new Request();
        $request->request->set('email', self::EMAIL);
        $request->request->set('password', self::PASSWORD);
        $parsedRequest = $this->userRequestParser->parseRequest($request);
        $this->assertInstanceOf(UserRequest::class, $parsedRequest);
        $this->assertEquals(self::EMAIL, $parsedRequest->email);
        $this->assertEquals(self::PASSWORD, $parsedRequest->password);
        $this->assertEmpty($parsedRequest->name);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testParseRequestWithoutEmail()
    {
        $request = new Request();
        $request->request->set('name', self::NAME);
        $request->request->set('password', self::PASSWORD);
        $parsedRequest = $this->userRequestParser->parseRequest($request);
        $this->assertInstanceOf(UserRequest::class, $parsedRequest);
        $this->assertEquals(self::NAME, $parsedRequest->name);
        $this->assertEquals(self::PASSWORD, $parsedRequest->password);
        $this->assertEmpty($parsedRequest->email);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testParseRequestWithoutPassword()
    {
        $request = new Request();
        $request->request->set('name', self::NAME);
        $request->request->set('email', self::EMAIL);
        $parsedRequest = $this->userRequestParser->parseRequest($request);
        $this->assertInstanceOf(UserRequest::class, $parsedRequest);
        $this->assertEquals(self::NAME, $parsedRequest->name);
        $this->assertEquals(self::EMAIL, $parsedRequest->email);
        $this->assertEmpty($parsedRequest->password);
    }

    /**
     * @return RoleRepository|MockInterface
     */
    private function mockRoleRepository()
    {
        /** @var RoleRepository|MockInterface $roleRepository */
        $roleRepository = Mockery::mock(RoleRepository::class);

        $roleRepository->shouldReceive('find')->andReturnUsing(function () {
            return $this->foundRole;
        });

        return $roleRepository;
    }

    /**
     * @return RolesManager|MockInterface
     */
    private function mockRolesManager()
    {
        /** @var RolesManager|MockInterface $rolesManager */
        $rolesManager = Mockery::mock(RolesManager::class);

        $rolesManager->shouldReceive('getDefaultRole')->andReturnUsing(function () {
            return $this->defaultRole;
        });

        return $rolesManager;
    }
}
