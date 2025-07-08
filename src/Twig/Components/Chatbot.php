<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\ChatQuestion;
use App\Entity\ChatResponse;
use App\Form\{ChatQuestionForm};
use App\Repository\ChatQuestionRepository;
use App\Service\RAGService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\{Attribute\AsLiveComponent,
    Attribute\LiveAction,
    Attribute\LiveProp,
    ComponentWithFormTrait,
    DefaultActionTrait};
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

#[AsLiveComponent]
final class Chatbot extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?ChatQuestion $initialFormData = null;

    #[LiveProp]
    public array $messages = [];

    #[LiveProp]
    public bool $isLoading = false;

    #[LiveProp]
    public ?int $pendingQuestion = null;

    #[LiveProp]
    public ?string $rateLimitError = null;

    #[LiveProp]
    public int $questionsRemaining = 3;

    public function __construct(
        private readonly RAGService $ragService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ChatQuestionRepository $chatQuestionRepository,
        private readonly RateLimiterFactoryInterface $chatbotQuestionsLimiter,
        private readonly RequestStack $requestStack,
    ) {
        $this->messages[] = [
            'type' => 'bot',
            'content' => 'Hello! I\'m your Starter Story assistant. Ask me anything about Starter Story YouTube videos!',
            'videos' => []
        ];
    }

    public function mount(): void
    {
        if ($this->requestStack->getCurrentRequest()->hasSession()) {
            $sessionId = $this->requestStack->getCurrentRequest()->getSession()->getId();
            $limiter = $this->chatbotQuestionsLimiter->create($sessionId);

            $currentState = $limiter->consume(0);
            $this->questionsRemaining = $currentState->getRemainingTokens();
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChatQuestionForm::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var ChatQuestion $chatQuestion */
        $chatQuestion = $this->getForm()->getData();

        $sessionId = $this->requestStack->getCurrentRequest()->getSession()->getId();
        $limiter = $this->chatbotQuestionsLimiter->create($sessionId);

        $rateLimit = $limiter->consume();
        if (!$rateLimit->isAccepted()) {
            $this->rateLimitError = 'You have reached the maximum number of questions (3) for this session. Please try again later.';

            return;
        }

        $this->rateLimitError = null;
        $this->questionsRemaining = $rateLimit->getRemainingTokens();

        $this->messages[] = [
            'type' => 'user',
            'content' => $chatQuestion->getQuestion(),
            'videos' => []
        ];

        $this->isLoading = true;

        $this->entityManager->persist($chatQuestion);
        $this->entityManager->flush();
        $this->pendingQuestion = $chatQuestion->getId();

        $this->initialFormData = null;
        $this->resetForm();
    }

    #[LiveAction]
    public function processResponse(): void
    {
        if (!$this->pendingQuestion) {
            return;
        }

        $chatQuestion = $this->chatQuestionRepository->findOneBy(['id' => $this->pendingQuestion]);

        if ($chatQuestion) {

            $ragResponse = $this->ragService->generateResponse($chatQuestion->getQuestion());

            $chatResponse = (new ChatResponse)
                        ->setQuestion($chatQuestion)
                        ->setAnswer($ragResponse['answer'])
                        ->setRelevantVideos($ragResponse['videos']);

            $this->entityManager->persist($chatResponse);
            $this->entityManager->flush();
        }

        $this->messages[] = [
            'type' => 'bot',
            'content' => $ragResponse['answer'],
            'videos' => $ragResponse['videos']
        ];

        $this->isLoading = false;
        $this->pendingQuestion = null;
        $this->initialFormData = null;
    }
}
