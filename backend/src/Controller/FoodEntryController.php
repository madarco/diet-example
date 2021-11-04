<?php

namespace App\Controller;

use App\Entity\FoodEntry;
use App\Repository\FoodEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/food_entries", name="food_entry")
 */
class FoodEntryController extends AbstractController
{
    /**
     * Simplified version of authentication (instead of the base symfony security bundle)
     * @var array[]
     */
    private $users = [
      'abc1' => [ 'id' => 1, 'limit' => 2400, 'isAdmin' => false ],
      'abc2' => [ 'id' => 2, 'limit' => 2000, 'isAdmin' => false ],
      'abc3' => [ 'id' => 3, 'limit' => 1200, 'isAdmin' => true ],
    ];

    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/{id}", methods="GET", requirements={"id"="\d+"}, name="food_entry_get")
     */
    public function getAction(int $id): Response
    {
        $user = $this->getUser();

        /** @var FoodEntry $entry */
        $entry = $this->getDoctrine()
            ->getRepository(FoodEntry::class)
            ->find($id);

        if (!$entry) {
            throw $this->createNotFoundException("No food entry found for id $id");
        }

        $this->checkEntryAuth($user, $entry);
        
        return $this->json($entry);
    }

    /**
     * @Route("/calories", methods="GET", name="food_entry_calories")
     */
    public function caloriesAction(Request $request): Response
    {
        $user = $this->getUser();

        $from = new \DateTime($request->get('from', '-7 days'));
        $from->setTime(0, 0);
        $to = new \DateTime($request->get('to', 'now'));
        $to->setTime(23, 59);

        $entries = $this->getDoctrine()
            ->getRepository(FoodEntry::class)
            ->getCalories($user['id'], $from, $to);

        return $this->json(['limit' => $user['limit'], 'entries' => $entries]);
    }

    /**
     * @Route("/stats", methods="GET", name="food_entry_stats")
     */
    public function statsAction(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user['isAdmin']) {
            throw new AccessDeniedHttpException("Only admins");
        }

        /** @var FoodEntryRepository $repo */
        $repo = $this->getDoctrine()->getRepository(FoodEntry::class);

        $result = [];

        $from = new \DateTime($request->get('from', '-7 days'));
        $from->setTime(0, 0);
        $to = new \DateTime($request->get('to', 'now'));

        $result['entriesLast7Days'] = $repo->statsEntries($from, $to);
        $result['averageCaloriesPerUser'] = $repo->statsCalories($from, $to);

        $fromLastWeek = new \DateTime($request->get('from', '-14 days'));
        $fromLastWeek->setTime(0, 0);
        $toLastWeek = new \DateTime($request->get('to', '-8 days'));
        $toLastWeek->setTime(23, 59);

        $result['entriesLastWeek'] = $repo->statsEntries($fromLastWeek, $toLastWeek);

        return $this->json($result);
    }

    /**
     * @Route("/{id}", methods="PUT", requirements={"id"="\d+"}, name="food_entry_put")
     */
    public function putAction(int $id, Request $request): Response
    {
        $entry = $this->replace($request, $id);
        return $this->json($entry);
    }

    /**
     * @Route("/", methods="POST", name="food_entry_post")
     */
    public function postAction(Request $request): Response
    {
        $entry = $this->replace($request);
        return $this->json($entry);
    }

    /**
     * @Route("/{id}", methods="DELETE", requirements={"id"="\d+"}, name="food_entry_delete")
     */
    public function deleteAction(int $id, Request $request): Response
    {
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();

        // UPDATE
        /** @var FoodEntry $entry */
        $entry = $this->getDoctrine()
            ->getRepository(FoodEntry::class)
            ->find($id);

        if (!$entry) {
            throw $this->createNotFoundException("No food entry found for id $id");
        }

        // Security:
        $this->checkEntryAuth($user, $entry);

        $entityManager->remove($entry);
        $entityManager->flush();

        return $this->json($entry);
    }

    /**
     * @Route("/", name="food_entry_get_all")
     */
    public function getAllAction(Request $request): Response
    {
        $user = $this->getUser();
        $repo = $this->getDoctrine()->getRepository(FoodEntry::class);

        $from = new \DateTime($request->get('from', '-7 days'));
        $from->setTime(0, 0);
        $to = new \DateTime($request->get('to', 'now'));
        $to->setTime(23, 59);

        $entries = $repo->search($user['isAdmin'] ? null : $user['id'], $from, $to);

        return $this->json($entries);
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

    protected function replace(Request $request, $id = null): FoodEntry
    {
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        /** @var FoodEntry $update */
        $update = $this->serializer->deserialize($request->getContent(), FoodEntry::class, "json");

        // Validate:
        $errors = $this->validator->validate($update);
        if (count($errors) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getPropertyPath() . ": " . $errors->get(0)->getMessage());
        }

        if ($id === null) {
            // CREATE
            $entry = new FoodEntry();
            $entry->setUser($user['id']);
            $entityManager->persist($entry);
        }
        else {
            // UPDATE
            /** @var FoodEntry $entry */
            $entry = $this->getDoctrine()
                ->getRepository(FoodEntry::class)
                ->find($id);

            if (!$entry) {
                throw $this->createNotFoundException("No food entry found for id $id");
            }

            // Security:
            $this->checkEntryAuth($user, $entry);
        }

        $entry->setName($update->getName());
        $entry->setCalories($update->getCalories());
        $entry->setEatDate($update->getEatDate());
        $entry->setSkipDiet($update->getSkipDiet());

        // Admin can change user:
        if ($user['isAdmin']) {
            if (empty(array_column($this->users, 'id')[$update->getUser()])) {
                throw new BadRequestHttpException("user: User {$update->getUser()} not found");
            }
            $entry->setUser($update->getUser());
        }


        $entityManager->flush();

        return $entry;
    }
}
