<?php
/**
 * @author: jinnoflife <git@jinnoflife.com>
 * @package: JolMediaCollector
 */

namespace JolMediaCollector\Commands;

use JolMediaCollector\Services\CollectionService;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MediaCollector
 */
class MediaCollector extends ShopwareCommand
{
    /**
     * @var CollectionService
     */
    private $collectionService;

    /**
     * MediaCollector constructor.
     *
     * @param $collectionService
     */
    public function __construct($collectionService)
    {
        $this->collectionService = $collectionService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('jol:media:collect')
            ->setDescription('Collects all files to specified album (default is: Unordered ')
            ->addArgument('albumId', InputArgument::OPTIONAL, 'Album id', -10);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collectionService->collectMedia($output, $input->getArgument('albumId'));
    }
}
