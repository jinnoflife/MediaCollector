<?php
/**
 * @author: jinnoflife <git@jinnoflife.com>
 * @package: JolMediaCollector
 */

namespace JolMediaCollector\Services;

use Doctrine\ORM\OptimisticLockException;
use JolMediaCollector\Factory\MediaFactory;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;
use Shopware\Models\User\User;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class CollectionService
 */
class CollectionService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var string
     */
    private $appRoot;

    /**
     * @var OutputInterface
     */
    private $outputInterface;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $albumId;

    /**
     * CollectionService constructor.
     *
     * @param ModelManager $modelManager
     * @param string       $pluginPath
     * @param string       $appRoot
     */
    public function __construct(ModelManager $modelManager, $appRoot)
    {
        $this->modelManager = $modelManager;
        $this->appRoot = $appRoot;
    }

    /**
     * @param OutputInterface $output
     * @param int             $albumId
     */
    public function collectMedia(OutputInterface $output, $albumId = -10)
    {
        $this->outputInterface = $output;
        $this->albumId = $albumId;
        $this->userId = $this->modelManager->getRepository(User::class)->findOneBy([
            'active' => true,
            'roleId' => 1,
        ])->getId();
        $images = $this->findImages();
        $this->registerImages($images);
    }

    /**
     * @return Finder
     */
    private function findImages()
    {
        $finder = Finder::create()
            ->in($this->appRoot . 'media/image')
            ->files()
            ->notPath('thumbnails')
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs(true)
            ->name('*.png')
            ->name('*.jpg')
            ->name('*.jpeg')
            ->name('*.svg')
            ->name('*.webp');

        return $this->addExistingMediasToIgnore($finder);
    }

    /**
     * @param Finder $finder
     *
     * @return Finder
     */
    private function addExistingMediasToIgnore(Finder $finder)
    {
        $medias = $this->getAlreadyImported();
        $this->outputInterface->writeln(count($medias) . ' already imported images found');
        foreach ($medias as $media) {
            $finder->notContains(str_replace('media/image/', '', $media));
        }

        return $finder;
    }

    /**
     * @return array
     */
    private function getAlreadyImported()
    {
        $medias = $this->modelManager->getDBALQueryBuilder('mediaCollector')
            ->select('path')
            ->from('s_media', 'media')
            ->where('type = ?')
            ->setParameter(0, Media::TYPE_IMAGE)
            ->execute()->fetchAll();

        return $medias;
    }

    /**
     * @param $images
     */
    private function registerImages(Finder $images)
    {
        $this->outputInterface->writeln($images->count() . ' not imported images found');
        $progress = new ProgressBar($this->outputInterface, count($images));
        $progress->start(count($images));
        foreach ($images as $image) {
            $newMedia = $this->registerImage($image);
            $this->modelManager->persist($newMedia);
            $progress->advance();
        }
        $progress->finish();
        try {
            $this->modelManager->flush();
        } catch (OptimisticLockException $e) {
        }
    }

    /**
     * @param SplFileInfo $image
     */
    private function registerImage($image)
    {
        return MediaFactory::build($image, $this->albumId, $this->userId);
    }
}
