<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\TranscriptionChunk;
use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Service\SupadataApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\{Attribute\AsCommand,
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface,
    Style\SymfonyStyle};

#[AsCommand(
    name: 'app:create-transcription-chunks',
    description: 'Create TranscriptionChunk entities from Video entities using the Supadata API',
)]
class CreateTranscriptionChunksCommand extends Command
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly SupadataApiService $supadataApiService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating TranscriptionChunk entities from Video entities');

        $videosWithIds = $this->videoRepository->findAllYoutubeIds();
        $totalVideos = count($videosWithIds);

        if ($totalVideos === 0) {
            $io->error('No videos found in the database');

            return Command::FAILURE;
        }

        $io->info(sprintf('Found %d videos to process', $totalVideos));
        $io->progressStart($totalVideos);

        $processedVideos = 0;
        $createdChunks = 0;

        foreach ($videosWithIds as $videoId => $youtubeId) {
            try {
                $video = $this->entityManager->getReference(Video::class, $videoId);

                $transcriptionJson = $this->supadataApiService->getYoutubeVideoTranscription($youtubeId);
                $transcriptionData = json_decode($transcriptionJson, true);

                if (!isset($transcriptionData['content']) || !is_array($transcriptionData['content'])) {

                    $io->note(sprintf('No valid transcription content for video ID %s (YouTube ID: %s)', $videoId, $youtubeId));

                    // Sleep for 1 second to respect API rate limit
                    sleep(1);
                    continue;
                }

                foreach ($transcriptionData['content'] as $chunk) {

                    $transcriptionChunk = (new TranscriptionChunk)
                            ->setVideo($video)
                            ->setContent($chunk['text'])
                            ->setVideoOffset(is_float($chunk['offset']) ? (int) round($chunk['offset']) : $chunk['offset'])
                            ->setDuration(is_float($chunk['duration']) ? (int) round($chunk['duration']) : $chunk['duration'])
                            ->setLang($chunk['lang'] ?? $transcriptionData['lang']);

                    $io->note(sprintf('Create transcription chunk for video ID %s', $videoId));

                    $this->entityManager->persist($transcriptionChunk);
                    $createdChunks++;
                }

                $this->entityManager->flush();
                $this->entityManager->clear();

                $processedVideos++;
                $io->progressAdvance();

                // Sleep for 1 second to respect API rate limit
                sleep(1);

            } catch (\Exception $e) {

                $io->error(sprintf(
                    'Error processing video ID %s (YouTube ID: %s): %s',
                    $videoId,
                    $youtubeId,
                    $e->getMessage()
                ));
            }
        }

        $io->progressFinish();

        $io->success([
            sprintf('Successfully processed %d/%d videos', $processedVideos, $totalVideos),
            sprintf('Created %d TranscriptionChunk entities', $createdChunks),
        ]);

        return Command::SUCCESS;
    }
}
