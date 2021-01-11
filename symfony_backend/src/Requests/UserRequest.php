<?php

namespace App\Requests;

use App\Entity\Role;
use App\Services\JsonRequestDataKeeper;
use App\Services\RolesManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

// @todo: keep it as simple stuct
class UserRequest
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $email = '';

    /** @var string */
    public $password = '';

    /** @var Role|null */
    public $role = null;
    /**
     * @var RolesManager
     */
    private $rolesManager;

    public function __construct(RolesManager $rolesManager)
    {
        $this->rolesManager = $rolesManager;
    }

    /**
     * @param Request $request
     * @todo: move this method into separate service
     */
    public function setUserRequest(Request $request): void
    {
        $request = JsonRequestDataKeeper::keepJson($request);
        $roleId = (int)$request->get('role_id', 0);
        $role = $this->rolesManager->findOrDefault($roleId);
        $this->name = (string)$request->get('name', '');
        $this->email = (string)$request->get('email', '');
        $this->password = (string)$request->get('password', '');
        $this->role = $role;
    }

    /**
     * @return ConstraintViolationListInterface
     * @todo: move this method into separate service and use optional parameter for role validation
     */
    public function validateUserRequest(): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($this->name, [
            new NotBlank(['message' => 'Name is required']),
            new Length([
                'min' => 2,
                'max' => 255,
                'minMessage' => 'Name is too short',
                'maxMessage' => 'Name is too long'
            ]),
        ]);

        $violations->addAll(
            $validator->validate($this->password, [
                new NotBlank(['message' => 'Password is required']),
                new Length([
                    'min' => 8,
                    'max' => 255,
                    'minMessage' => 'Password is too short',
                    'maxMessage' => 'Password is too long'
                ]),
                new NotCompromisedPassword(['message' => 'This password was compromised'])
            ])
        );

        $violations->addAll(
            $validator->validate($this->email, [
                new NotBlank(['message' => 'Email is required']),
                new Email(['message' => 'Invalid email'])
            ])
        );

        $violations->addAll(
            $validator->validate($this->role, [
                new NotNull(['message' => 'Role is required'])
            ])
        );

        return $violations;
    }

}
