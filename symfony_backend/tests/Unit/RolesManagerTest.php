<?php

namespace App\Tests\Unit;

use Mockery;
use App\Entity\Role;
use App\Entity\User;
use Mockery\MockInterface;
use Doctrine\ORM\ORMException;
use App\Services\RolesManager;
use PHPUnit\Framework\TestCase;
use App\Repository\RoleRepository;
use Doctrine\ORM\OptimisticLockException;

class RolesManagerTest extends TestCase
{
    /** @var Role|null */
    private ?Role $role;

    /** @var Role|null */
    private ?Role $foundRole;

    /** @var Role|null */
    private ?Role $createdRole;

    /** @var Role */
    private Role $adminRole;

    /** @var Role */
    private Role $userRole;

    /** @var RolesManager */
    private RolesManager $rolesManager;

    /** @var User */
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->rolesManager = new RolesManager($this->mockRoleRepository());
        $this->user = new User();
        $this->adminRole = new Role();
        $this->adminRole->setName('admin');
        $this->adminRole->setId(1);
        $this->userRole = new Role();
        $this->userRole->setName('user');
        $this->userRole->setId(2);
        $this->role = $this->adminRole;
        $this->foundRole = $this->adminRole;
        $this->createdRole = $this->adminRole;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testIsAdmin(): void
    {
        $this->foundRole = $this->adminRole;
        $this->user->setRole($this->adminRole);
        $result = $this->rolesManager->isAdmin($this->user);
        $this->assertTrue($result);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testIsAdminIfUser(): void
    {
        $this->foundRole = $this->adminRole;
        $this->user->setRole($this->userRole);
        $result = $this->rolesManager->isAdmin($this->user);
        $this->assertFalse($result);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testFindOrDefault()
    {
        $this->role = $this->adminRole;
        $role = $this->rolesManager->findOrDefault(1);
        $this->assertEquals($this->adminRole->getName(), $role->getName());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testFindOrDefaultIfNotExists()
    {
        $this->role = null;
        $this->foundRole = $this->userRole;
        $role = $this->rolesManager->findOrDefault(1);
        $this->assertEquals($this->userRole->getName(), $role->getName());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testFindOrDefaultIfNoRoles()
    {
        $this->role = null;
        $this->foundRole = null;
        $this->createdRole = $this->userRole;
        $role = $this->rolesManager->findOrDefault(1);
        $this->assertEquals($this->userRole->getName(), $role->getName());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testGetDefaultRole()
    {
        $this->foundRole = $this->userRole;
        $role = $this->rolesManager->getDefaultRole();
        $this->assertEquals($this->userRole->getName(), $role->getName());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testDefaultRoleIfDoesNotExist()
    {
        $this->foundRole = null;
        $this->createdRole = $this->userRole;
        $role = $this->rolesManager->getDefaultRole();
        $this->assertEquals($this->userRole->getName(), $role->getName());
    }

    /**
     * @return RoleRepository|MockInterface
     */
    private function mockRoleRepository()
    {
        /** @var RoleRepository|MockInterface $roleRepository */
        $roleRepository = Mockery::mock(RoleRepository::class);

        $roleRepository->shouldReceive('find')->andReturnUsing(function () {
            return $this->role;
        });
        $roleRepository->shouldReceive('findOneBy')->andReturnUsing(function () {
            return $this->foundRole;
        });
        $roleRepository->shouldReceive('createWithName')->andReturnUsing(function () {
            return $this->createdRole;
        });

        return $roleRepository;
    }
}

