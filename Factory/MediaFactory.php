<?php
/**
 * @author: jinnoflife <git@jinnoflife.com>
 * @package: JolMediaCollector
 */

namespace JolMediaCollector\Factory;

use Shopware\Models\Media\Media;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class MediaFactory
 */
class MediaFactory
{
    /**
     * @param SplFileInfo $image
     * @param $albumId
     * @param $userId
     *
     * @throws \Exception
     *
     * @return Media
     */
    public static function build(SplFileInfo $image, $albumId, $userId)
    {
        $media = new Media();
        $media->setName(str_replace($image->getExtension(), '', $image->getFilename()));
        $media->setDescription('');
        $media->setPath('media/image/' . $image->getFilename());
        $media->setAlbumId($albumId);
        $media->setUserId($userId);
        $media->setType(Media::TYPE_IMAGE);
        $media->setExtension($image->getExtension());
        $media->setFileSize($image->getSize());
        $media->setCreated(new \DateTime());

        return $media;
    }
}
