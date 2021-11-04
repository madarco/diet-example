<?php

namespace App\Controller;

use App\Entity\FoodEntry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Provide CRUD for food entries
 * @Route("/api/food_entries", name="food_entry")
 */
class FoodEntryController extends BaseController
{
    /**
     * Retrieve a food entry
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
     * Update a food entry
     * @Route("/{id}", methods="PUT", requirements={"id"="\d+"}, name="food_entry_put")
     */
    public function putAction(int $id, Request $request): Response
    {
        $entry = $this->replace($request, $id);
        return $this->json($entry);
    }

    /**
     * Create a new food entry
     * @Route("/", methods="POST", name="food_entry_post")
     */
    public function postAction(Request $request): Response
    {
        $entry = $this->replace($request);
        return $this->json($entry);
    }

    /**
     * Delete a food entry
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
     * List all food entries in the period for the user
     * @Route("/", methods="GET", name="food_entry_get_all")
     *
     * @OA\Parameter(name="from", in="query", @OA\Schema(type="string"))
     * @OA\Parameter(name="to", in="query", @OA\Schema(type="string"))
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
     * Get the daily count of calories
     * @Route("/calories", methods="GET", name="food_entry_calories")
     *
     * @OA\Parameter(name="from", in="query", @OA\Schema(type="string"))
     * @OA\Parameter(name="to", in="query", @OA\Schema(type="string"))
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
            if (!in_array($update->getUser(), array_column($this->users, 'id')) ) {
                throw new BadRequestHttpException("user: User {$update->getUser()} not found");
            }
            $entry->setUser($update->getUser());
        }


        $entityManager->flush();

        return $entry;
    }
}
