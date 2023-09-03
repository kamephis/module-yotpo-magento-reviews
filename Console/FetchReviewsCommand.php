<?php

namespace Kamephis\YotpoImporter\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kamephis\YotpoImporter\Cron\FetchYotpoReviews;

class FetchReviewsCommand extends Command
{
    /**
     * @var FetchYotpoReviews
     */
    private $fetchYotpoReviews;

    /**
     * FetchReviewsCommand constructor.
     * @param FetchYotpoReviews $fetchYotpoReviews
     * @param null $name
     */
    public function __construct(FetchYotpoReviews $fetchYotpoReviews, $name = null)
    {
        $this->fetchYotpoReviews = $fetchYotpoReviews;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('kamephis:yotpo:fetch-reviews')
            ->setDescription('Manually fetch Yotpo reviews')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting Yotpo reviews fetching');
        $this->fetchYotpoReviews->execute();
        $output->writeln('Fetching completed');
    }
}

