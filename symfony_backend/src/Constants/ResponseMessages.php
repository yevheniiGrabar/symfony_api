<?php

namespace App\Constants;

interface ResponseMessages
{
    public const NAME_IS_REQUIRE_MESSAGE = 'Name is require';
    public const NAME_IS_TOO_SHORT_MESSAGE = 'Name is too short';
    public const NAME_IS_TOO_LONG_MESSAGE = 'Name is too long';
    public const PASSWORD_IS_REQUIRED_MESSAGE = 'Password is required';
    public const PASSWORD_IS_TOO_SHORT_MESSAGE = 'Password is too short';
    public const PASSWORD_IS_TOO_LONG_MESSAGE = 'Password is too long';
    public const PASSWORD_IS_COMPROMISED_MESSAGE = 'This password was compromised';
    public const EMAIL_IS_REQUIRED_MESSAGE = 'Email is required';
    public const EMAIL_IS_INVALID_MESSAGE = 'Email is invalid';
    public const ROLE_IS_REQUIRED_MESSAGE = 'Role is required';
    public const EMAIL_ALREADY_IN_USE_MESSAGE = 'This email already in use';
    public const ACCESS_DENIED_MESSAGE = 'Access denied';
    public const EXPIRED_REFRESH_TOKEN_MESSAGE = 'Expired refresh token';
    public const PASSWORD_IS_INVALID_MESSAGE = 'Invalid password';
    public const REFRESH_TOKEN_NOT_FOUND_MESSAGE = 'Refresh token not found';
    public const ENTITY_WAS_NOT_REMOVED_MESSAGE = 'Entity was not removed';
    public const USER_NOT_FOUND_MESSAGE = 'User not found';
    public const POST_NOT_FOUND_MESSAGE = 'Post not found';
    public const POST_DELETED_SUCCESSFULLY = 'Post deleted successfully';
    public const TITLE_IS_REQUIRED_MESSAGE = 'Title is required';
    public const TITLE_IS_TOO_SHORT_MESSAGE = 'Title is too short';
    public const TITLE_IS_TOO_LONG_MESSAGE = 'Title is too long';
    public const CONTENT_IS_REQUIRED_MESSAGE = 'Content is required';
    public const CONTENT_IS_TOO_SHORT_MESSAGE = 'Content is too short';
    public const CONTENT_IS_TOO_LONG_MESSAGE = 'Content is too long';


    public const EXISTING_ADMIN_POST_ID = 1;
    public const EXISTING_ADMIN_TITLE = 'Admin Post For Test';
    public const EXISTING_ADMIN_CONTENT = 'Some Content for admin post';
    public const ADMIN_POST_CREATED_AT = '2021-02-09 10:20:34';
    public const ADMIN_POST_UPDATED_AT = '2021-02-09 10:35:08';
    public const EXISTING_USER_POST_ID = 2;
    public const EXISTING_USER_TITLE = 'User Post For Test';
    public const EXISTING_USER_CONTENT = 'Some content for user post';
    public const USER_POST_CREATED_AT = '2021-02-09 10:35:01';
    public const USER_POST_UPDATED_AT = '2021-02-09 10:35:12';
    public const NEW_USER_POST_TITLE = 'New User post title';
    public const NEW_USER_POST_CONTENT = 'New User post content';

    public const USER_POST_SHORT_TITLE = 'T';
    public const USER_POST_SHORT_CONTENT = 'Test';





}

