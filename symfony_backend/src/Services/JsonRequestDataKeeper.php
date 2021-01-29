<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class JsonRequestDataKeeper
{
    /**
     * @param Request $request
     * @return Request
     */
    public static function keepJson(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || count($data) == 0) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}

