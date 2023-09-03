<?php

namespace Kamephis\YotpoImporter\Console\Command;

use Kamephis\YotpoImporter\Cron\FetchYotpoReviews;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchReviews extends Command
{
    private FetchYotpoReviews $fetchYotpoReviews;

    public function __construct(FetchYotpoReviews $fetchYotpoReviews, string $name = null)
    {
        $this->fetchYotpoReviews = $fetchYotpoReviews;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('kamephis:yotpo:fetch-reviews')
            ->setDescription('Manually fetch Yotpo reviews');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->fetchYotpoReviews->execute();
            $output->writeln('<info>Successfully fetched Yotpo reviews</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>An error occurred: ' . $e->getMessage() . '</error>');
        }
    }
}
