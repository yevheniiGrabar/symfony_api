<?php

namespace App\Services;

use App\Requests\UserRequest;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

class UserRequestValidator
{
    /**
     * @param UserRequest $request
     * @param bool $validateRole
     * @return ConstraintViolationListInterface
     */
    public static function validate(UserRequest $request, bool $validateRole = false): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($request->name, [
            new NotBlank(['message' => 'Name is required']),
            new Length([
                'min' => 2,
                'max' => 255,
                'minMessage' => 'Name is too short',
                'maxMessage' => 'Name is too long'
            ]),
        ]);

        $violations->addAll(
            $validator->validate($request->password, [
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
            $validator->validate($request->email, [
                new NotBlank(['message' => 'Email is required']),
                new Email(['message' => 'Invalid email'])
            ])
        );

        if ($validateRole) {
            $violations->addAll(
                $validator->validate($request->role, [
                    new NotNull(['message' => 'Role is required'])
                ])
            );
        }

        return $violations;
    }
}
