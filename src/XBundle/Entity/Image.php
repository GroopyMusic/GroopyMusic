<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="XBundle\Repository\ImageRepository")
 * @Vich\Uploadable
 */
class Image
{

    const PHOTOS_DIR = 'x/images/projects/';

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->filename;
    }

    public function getUploadFileName() {
        return $this->getFilename();
    }

    public static function getWebPath(Image $image) {
        return self::PHOTOS_DIR . $image->getFilename();
    }

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="x_project_header", fileNameProperty="fileName", size="imageSize")
     *
     * @var File
     */
    private $imageFile;

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }


    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="text")
     */
    private $filename;

    /**
     * @var int
     *
     * @ORM\Column(name="imageSize", type="integer", nullable=true)
     */
    private $imageSize;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updateAt", type="datetime", nullable=true)
     */
    private $updateAt;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Image
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set imageSize
     *
     * @param integer $imageSize
     *
     * @return Image
     */
    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    /**
     * Get imageSize
     *
     * @return int
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }

    /**
     * Set updateAt
     *
     * @param \DateTime $updateAt
     *
     * @return Image
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }
}

