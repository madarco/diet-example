<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Provide access to current user and users
 * @Route("/api/users", name="users")
 */
class UserController extends BaseController
{
    /**
     * Return info on the current user
     * @Route("/me", methods="GET", name="user_get")
     */
    public function userAction(): Response
    {
        $user = $this->getUser();

        return $this->json($user);
    }

    /**
     * [Admin Only] Return the list of users in the system
     * @Route("/", methods="GET", name="users_get_all")
     */
    public function usersAction(): Response
    {
        $user = $this->getUser();
        if (!$user['isAdmin']) {
            throw new AccessDeniedHttpException("Only admins");
        }

        $users = [];
        foreach($this->users as $token => $user) {
            $users[] = $user;
        }

        return $this->json($users);
    }
}
