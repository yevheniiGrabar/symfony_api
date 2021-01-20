<?php

namespace App\Services;

use App\Requests\UserRequest;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class UserRequestValidator
{
    public const NAME_IS_REQUIRE_MESSAGE = 'Name is require';
    public const NAME_IS_TOO_SHORT_MESSAGE = 'Name is too short';
    public const NAME_IS_TOO_LONG_MESSAGE = 'Name is too long';
    public const PASSWORD_IS_REQUIRE_MESSAGE = 'Password is require';
    public const PASSWORD_IS_TOO_SHORT_MESSAGE = 'Password is too short';
    public const PASSWORD_IS_TOO_LONG_MESSAGE = 'Password is too long';
    public const PASSWORD_IS_COMPROMISED_MESSAGE = 'This password was compromised';
    public const EMAIL_IS_REQUIRED_MESSAGE = 'Email is required';
    public const EMAIL_IS_INVALID_MESSAGE = 'Email is invalid';
    public const ROLE_IS_REQUIRED_MESSAGE = 'Role is required';

    /**
     * @param UserRequest $request
     * @param bool $validateRole
     * @return ConstraintViolationListInterface
     */
    public static function validate(UserRequest $request, bool $validateRole = false): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($request->name, [
            new NotBlank(['message' => self::NAME_IS_REQUIRE_MESSAGE]),
            new Length([
                'min' => 2,
                'max' => 255,
                'minMessage' => self::NAME_IS_TOO_SHORT_MESSAGE,
                'maxMessage' => self::NAME_IS_TOO_LONG_MESSAGE,
            ]),
        ]);

        $violations->addAll(
            $validator->validate($request->password, [
                new NotBlank(['message' => self::PASSWORD_IS_REQUIRE_MESSAGE]),
                new Length([
                    'min' => 8,
                    'max' => 255,
                    'minMessage' => self::PASSWORD_IS_TOO_SHORT_MESSAGE,
                    'maxMessage' => self::PASSWORD_IS_TOO_LONG_MESSAGE,
                ]),
                new NotCompromisedPassword(['message' => self::PASSWORD_IS_COMPROMISED_MESSAGE])
            ])
        );

        $violations->addAll(
            $validator->validate($request->email, [
                new NotBlank(['message' => self::EMAIL_IS_REQUIRED_MESSAGE]),
                new Email(['message' => self::EMAIL_IS_INVALID_MESSAGE])
            ])
        );

        if ($validateRole) {
            $violations->addAll(
                $validator->validate($request->role, [
                    new NotNull(['message' => self::ROLE_IS_REQUIRED_MESSAGE])
                ])
            );
        }

        return $violations;
    }
}
