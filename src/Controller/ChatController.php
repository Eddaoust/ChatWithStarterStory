<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\{ChatQuestionRepository};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat')]
    public function index(ChatQuestionRepository $chatQuestionRepository): Response
    {
        $recentQuestions = $chatQuestionRepository->findRecentQuestionsWithResponses(10);

        return $this->render('home/index.html.twig', [
            'recentQuestions' => $recentQuestions,
        ]);
    }
}
