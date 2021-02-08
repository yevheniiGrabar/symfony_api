<?php

namespace App\Services;

use App\Constants\ResponseMessages;
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
    /**
     * @param UserRequest $request
     * @param bool $validateRole
     * @return ConstraintViolationListInterface
     */
    public static function validate(UserRequest $request, bool $validateRole = false): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($request->name, [
            new NotBlank(['message' => ResponseMessages::NAME_IS_REQUIRE_MESSAGE]),
            new Length([
                'min' => 2,
                'max' => 255,
                'minMessage' => ResponseMessages::NAME_IS_TOO_SHORT_MESSAGE,
                'maxMessage' => ResponseMessages::NAME_IS_TOO_LONG_MESSAGE,
            ]),
        ]);

        $violations->addAll(
            $validator->validate($request->password, [
                new NotBlank(['message' => ResponseMessages::PASSWORD_IS_REQUIRED_MESSAGE]),
                new Length([
                    'min' => 8,
                    'max' => 255,
                    'minMessage' => ResponseMessages::PASSWORD_IS_TOO_SHORT_MESSAGE,
                    'maxMessage' => ResponseMessages::PASSWORD_IS_TOO_LONG_MESSAGE,
                ]),
                new NotCompromisedPassword(['message' => ResponseMessages::PASSWORD_IS_COMPROMISED_MESSAGE])
            ])
        );

        $violations->addAll(
            $validator->validate($request->email, [
                new NotBlank(['message' => ResponseMessages::EMAIL_IS_REQUIRED_MESSAGE]),
                new Email(['message' => ResponseMessages::EMAIL_IS_INVALID_MESSAGE])
            ])
        );

        if ($validateRole) {
            $violations->addAll(
                $validator->validate($request->role, [
                    new NotNull(['message' => ResponseMessages::ROLE_IS_REQUIRED_MESSAGE])
                ])
            );
        }

        return $violations;
    }
}

