<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\TranscriptionChunk;
use App\Repository\TranscriptionChunkRepository;
use App\Service\EmbeddingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-embeddings',
    description: 'Generate embeddings for TranscriptionChunks using LLPhant',
)]
class GenerateEmbeddingsCommand extends Command
{
    private const BATCH_SIZE = 25;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EmbeddingService $embeddingService,
        private readonly TranscriptionChunkRepository $chunkRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating embeddings for TranscriptionChunks');

        $total = $this->chunkRepository->count(['embedding' => null]);

        if ($total === 0) {

            $io->success('No transcription chunks require embedding generation');

            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d transcription chunk(s) to process', $total));

        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        $offset = 0;
        $processed = 0;

        while ($offset < $total) {
            $chunks = $this->chunkRepository->findBy(['embedding' => null], null, self::BATCH_SIZE, $offset);

            foreach ($chunks as $chunk) {
                if (!$chunk instanceof TranscriptionChunk) {
                    continue;
                }

                try {
                    $embedding = $this->embeddingService->generateEmbedding($chunk->getContent());
                    $chunk->setEmbedding($embedding);
                    $this->entityManager->persist($chunk);

                } catch (\Throwable $e) {

                    $io->error(sprintf(
                        'Error processing chunk ID %d: %s',
                        $chunk->getId(),
                        $e->getMessage()
                    ));
                    continue;
                }
                $processed++;
                $progressBar->advance();
            }

            $this->entityManager->flush();
            $this->entityManager->clear(); // Free memory

            $offset += self::BATCH_SIZE;
        }

        $progressBar->finish();
        $output->writeln('');
        $io->success(sprintf('Processed %d transcription chunk(s) successfully.', $processed));

        return Command::SUCCESS;
    }
}
