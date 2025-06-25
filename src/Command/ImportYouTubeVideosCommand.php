<?php

declare(strict_types=1);

namespace App\Command;

use App\{Entity\Video, Service\SupadataApiService, Service\YouTubeService};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

#[AsCommand(
    name: 'app:import-youtube-videos',
    description: 'Import videos from a YouTube channel and save them to the database',
)]
class ImportYouTubeVideosCommand extends Command
{
    private const CHANNEL_ID = 'UChhw6DlKKTQ9mYSpTfXUYqA'; // StarterStory youtube channel
    private const VIDEO_LIMIT = 100;

    public function __construct(
        private readonly SupadataApiService $supadataApiService,
        private readonly YouTubeService $youTubeService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ObjectMapperInterface $objectMapper,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Importing YouTube videos');

        try {
            $io->info(sprintf('Fetching videos from channel ID: %s', self::CHANNEL_ID));

            $channelVideos = $this->supadataApiService->getYoutubeChannelVideos(
                self::CHANNEL_ID,
                self::VIDEO_LIMIT
            );


            if (empty($channelVideos) || empty($channelVideos['videoIds'])) {

                $io->error('No videos found for the specified channel.');

                return Command::FAILURE;
            }

            $videoIds = $channelVideos['videoIds'];

            $io->info(sprintf('Found %d videos', count($videoIds)));
            $io->info('Fetching detailed metadata for videos...');

            $chunkedVideoIds = array_chunk($videoIds, 50);

            $io->info(sprintf('Processing %d chunks of video IDs', count($chunkedVideoIds)));

            $videosMetadata = [];
            foreach ($chunkedVideoIds as $index => $chunk) {

                $io->info(sprintf('Processing chunk %d with %d videos', $index + 1, count($chunk)));

                $chunkMetadata = $this->youTubeService->getMultipleVideosMetadata($chunk);
                $videosMetadata = array_merge($videosMetadata, $chunkMetadata);
            }

            $io->info(sprintf('Retrieved metadata for %d videos', count($videosMetadata)));

            $io->progressStart(count($videosMetadata));

            $count = 0;
            foreach ($videosMetadata as $videoDTO) {
                $video = $this->objectMapper->map($videoDTO, Video::class);
                $this->entityManager->persist($video);

                // Flush every 10 entities to avoid memory issues
                if (++$count % 10 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }

                $io->progressAdvance();
            }

            $this->entityManager->flush();

            $io->progressFinish();
            $io->success(sprintf('Successfully imported %d videos into the database', count($videosMetadata)));

            return Command::SUCCESS;
        } catch (\Exception $e) {

            $io->error('An error occurred while importing videos: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
