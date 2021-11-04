<?php

namespace App\Controller;

use App\Entity\FoodEntry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Base controller to provide simple token-based authentication
 */
abstract class BaseController extends AbstractController
{
    /**
     * Simplified version of authentication (instead of the base symfony security bundle)
     * @var array[]
     */
    protected $users = [
      'abc1' => [ 'id' => 1, 'username' => 'Marco', 'limit' => 2400, 'isAdmin' => false ],
      'abc2' => [ 'id' => 2, 'username' => 'John', 'limit' => 2000, 'isAdmin' => false ],
      'abc3' => [ 'id' => 3, 'username' => 'Admin', 'limit' => 1200, 'isAdmin' => true ],
    ];

    protected SerializerInterface $serializer;
    protected ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Simpler version of the base getUser symfony method
     */
    protected function getUser() {
        /** @var Request $request */
        $request = $this->get('request_stack')->getCurrentRequest();
        $token = $request->get('token', $request->headers->get('Authorization'));
        if (empty($token) || empty($this->users[$token])) {
            throw new AccessDeniedHttpException("No user found");
        }
        return $this->users[$token];
    }

    protected function checkEntryAuth($user, FoodEntry $entry) {
        if (!$user['isAdmin'] && $user['id'] != $entry->getUser()) {
            throw $this->createNotFoundException("No food entry found for id {$entry->getId()}");
        }
    }
}
