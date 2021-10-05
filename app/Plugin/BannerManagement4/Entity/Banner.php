<?php

/*
 * This file is part of BannerManagement4
 *
 * Copyright(c) U-Mebius Inc. All Rights Reserved.
 *
 * https://umebius.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BannerManagement4\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Banner
 *
 * @ORM\Table(name="plg_banner")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\BannerManagement4\Repository\BannerRepository")
 */
class Banner extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255)
     */
    private $file_name;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="text", nullable=true)
     */
    private $alt;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_no", type="integer", options={"unsigned":true})
     */
    private $sort_no;


    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    private $url;

    /**
     * @var boolean
     *
     * @ORM\Column(name="link_method", type="boolean", options={"default":false})
     */
    private $link_method = false;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_class", type="text", nullable=true)
     */
    private $additional_class;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @var \Plugin\BannerManagement4\Entity\BannerField
     * @ORM\ManyToOne(targetEntity="\Plugin\BannerManagement4\Entity\BannerField")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="field_id", referencedColumnName="id")
     * })
     */
    private $Field;



    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set file_name
     *
     * @param string $fileName
     * @return Banner
     */
    public function setFileName($fileName)
    {
        $this->file_name = $fileName;

        return $this;
    }

    /**
     * Get file_name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     * @return Banner
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }




    /**
     * Set alt
     *
     * @param string $alt
     * @return Banner
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return Banner
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param \DateTime $updateDate
     * @return Banner
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * Set Field
     *
     * @param \Plugin\BannerManagement4\Entity\BannerField $field
     * @return Banner
     */
    public function setField(\Plugin\BannerManagement4\Entity\BannerField $field = null)
    {
        $this->Field = $field;

        return $this;
    }

    /**
     * Get Field
     *
     * @return \Plugin\BannerManagement4\Entity\BannerField
     */
    public function getField()
    {
        return $this->Field;
    }



    /**
     * Set sort_no
     *
     * @param integer $sort_no
     * @return Banner
     */
    public function setSortNo($sort_no)
    {
        $this->sort_no = $sort_no;

        return $this;
    }

    /**
     * Get $sort_no
     *
     * @return integer
     */
    public function getSortNo()
    {
        return $this->sort_no;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Banner
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set link_method
     *
     * @param integer $linkMethod
     * @return Banner
     */
    public function setLinkMethod($linkMethod)
    {
        $this->link_method = $linkMethod;

        return $this;
    }

    /**
     * Get link_method
     *
     * @return integer
     */
    public function getLinkMethod()
    {
        return $this->link_method;
    }


    /**
     * Set additional_class
     *
     * @param string $additionalClass
     * @return Banner
     */
    public function setAdditionalClass($additionalClass)
    {
        $this->additional_class = $additionalClass;

        return $this;
    }

    /**
     * Get additional_class
     *
     * @return string
     */
    public function getAdditionalClass()
    {
        return $this->additional_class;
    }
}
