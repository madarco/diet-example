<?php

namespace App\Controller;

use App\Entity\FoodEntry;
use App\Repository\FoodEntryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Admin features
 * @Route("/api/admin", name="admin")
 */
class AdminController extends BaseController
{
    /**
     * [Admin Only] Get the stats on the system
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

        $from = new \DateTime('-7 days');
        $from->setTime(0, 0);
        $to = new \DateTime('now');

        $result['entriesLast7Days'] = $repo->statsEntries($from, $to);
        $result['averageCaloriesPerUser'] = $repo->statsCalories($from, $to);

        $fromLastWeek = new \DateTime('-15 days');
        $fromLastWeek->setTime(0, 0);
        $toLastWeek = new \DateTime('-8 days');
        $toLastWeek->setTime(23, 59);

        $result['entriesLastWeek'] = $repo->statsEntries($fromLastWeek, $toLastWeek);

        return $this->json($result);
    }
}
