<?php

namespace App\Services;

use App\Requests\UserPostRequest;
use Symfony\Component\HttpFoundation\Request;

class PostRequestParser
{
    /**
     * @param Request $request
     * @return UserPostRequest
     */
    public function PostParseRequest(Request $request): UserPostRequest
    {
        $userPostRequest = $this->PostParseRequest($request);
        $userPostRequest->title = (string)$request->get('title', '');
        $userPostRequest->content = (string)$request->get('content', '');
        $userPostRequest->createdAt = $request->get('createdAt', '');
        $userPostRequest->updatedAt = $request->get('updatedAt', '');

        return $userPostRequest;
    }
}