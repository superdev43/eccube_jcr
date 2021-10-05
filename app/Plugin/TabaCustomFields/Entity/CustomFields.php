<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Entity;

use Doctrine\ORM\Mapping as ORM;
use Plugin\TabaCustomFields\Common\Constants;


/**
 * CustomFields
 *
 * @ORM\Table(name="plg_taba_custom_fields")
 * @ORM\Entity(repositoryClass="Plugin\TabaCustomFields\Repository\CustomFieldsRepository")
 */
class CustomFields extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var 初期値
     */
    const SHOP_DISPLAYED_DEFAULT = true;
    const VALIDATION_NOT_BLANK_DEFAULT = false;
    const VALIDATION_IS_NUMBER_DEFAULT = false;
    const VALIDATION_UNIQUE_DEFAULT = false;

    /**
     * @ORM\Id()
     * @ORM\Column(name="target_entity", type="string", length=64, nullable=false, options={"fixed": false})
     * @ORM\GeneratedValue(strategy="NONE")
     * 
     * @var string
     */
    private $targetEntity;

    /**
     * @ORM\Id()
     * @ORM\Column(name="column_id", type="integer", nullable=false, options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var integer
     */
    private $columnId;

    /**
     * @ORM\Column(name="field_type", type="string", length=255, nullable=false, options={"fixed": false})
     *
     * @var string
     */
    private $fieldType;

    /**
     * @ORM\Column(name="label", type="string", length=255, nullable=true, options={"fixed": false})
     *
     * @var string
     */
    private $label;

    /**
     * @ORM\Column(name="data_key", type="string", length=255, nullable=false, options={"fixed": false})
     *
     * @var string
     */
    private $dataKey;

    /**
     * @ORM\Column(name="read_allowed", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $readAllowed;

    /**
     * @ORM\Column(name="write_allowed", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $writeAllowed;

    /**
     * @ORM\Column(name="rank", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $rank;

    /**
     * @ORM\Column(name="form_properties", type="text", length=65535, nullable=true, options={"fixed": false})
     *
     * @var string
     */
    private $formProperties;

    /**
     * @ORM\Column(name="form_option", type="text", length=65535, nullable=true, options={"fixed": false})
     *
     * @var string
     */
    private $formOption;

    /**
     * @ORM\Column(name="validation_regex", type="string", length=255, nullable=true, options={"fixed": false})
     *
     * @var string
     */
    private $validationRegex;

    /**
     * @ORM\Column(name="validation_not_blank", type="smallint", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationNotBlank;

    /**
     * @ORM\Column(name="validation_is_number", type="smallint", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationIsNumber;

    /**
     * @ORM\Column(name="validation_max_number", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMaxNumber;

    /**
     * @ORM\Column(name="validation_min_number", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMinNumber;

    /**
     * @ORM\Column(name="validation_unique", type="smallint", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationUnique;

    /**
     * @ORM\Column(name="validation_max_length", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMaxLength;

    /**
     * @ORM\Column(name="validation_min_length", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMinLength;

    /**
     * @ORM\Column(name="validation_max_checked_number", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMaxCheckedNumber;

    /**
     * @ORM\Column(name="validation_min_checked_number", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMinCheckedNumber;

    /**
     * @ORM\Column(name="validation_document_file_type", type="array", length=65535, nullable=true, options={"fixed": false})
     *
     * @var string
     */
    private $validationDocumentFileType;

    /**
     * @ORM\Column(name="validation_image_file_type", type="array", length=65535, nullable=true, options={"fixed": false})
     *
     * @var string
     */
    private $validationImageFileType;

    /**
     * @ORM\Column(name="validation_max_file_size", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMaxFileSize;

    /**
     * @ORM\Column(name="validation_max_pixel_dimension_width", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMaxPixelDimensionWidth;

    /**
     * @ORM\Column(name="validation_min_pixel_dimension_width", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMinPixelDimensionWidth;

    /**
     * @ORM\Column(name="validation_max_pixel_dimension_height", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMaxPixelDimensionHeight;

    /**
     * @ORM\Column(name="validation_min_pixel_dimension_height", type="integer", nullable=true, options={"unsigned": false})
     *
     * @var integer
     */
    private $validationMinPixelDimensionHeight;

    /**
     * @ORM\Column(name="create_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    private $createDate;

    /**
     * @ORM\Column(name="update_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    private $updateDate;

    /**
     * コンストラクタ
     *
     *
     */
    public function __construct()
    {
        $this->shopDisplayed = self::SHOP_DISPLAYED_DEFAULT;
        $this->validationNotBlank = self::VALIDATION_NOT_BLANK_DEFAULT;
        $this->validationIsNumber = self::VALIDATION_IS_NUMBER_DEFAULT;
        $this->validationUnique = self::VALIDATION_UNIQUE_DEFAULT;
        $this->validationDocumentFileType = Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_document_file_type']['choices'];
        $this->validationImageFileType = Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_image_file_type']['choices'];
    }

    /**
     * Set targetEntity
     *
     * @param string $targetEntity
     * @return CustomFields
     */
    public function setTargetEntity($targetEntity)
    {
        $this->targetEntity = $targetEntity;

        return $this;
    }

    /**
     * Get targetEntity
     *
     * @return string 
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * Set columnId
     *
     * @param string $columnId
     * @return CustomFields
     */
    public function setColumnId($columnId)
    {
        $this->columnId = $columnId;

        return $this;
    }

    /**
     * Get columnId
     *
     * @return integer 
     */
    public function getColumnId()
    {
        return $this->columnId;
    }

    /**
     * Set fieldType
     *
     * @param string $fieldType
     * @return CustomFields
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * Get fieldType
     *
     * @return string 
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return CustomFields
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set dataKey
     *
     * @param string $dataKey
     * @return CustomFields
     */
    public function setDataKey($dataKey)
    {
        $this->dataKey = $dataKey;

        return $this;
    }

    /**
     * Get dataKey
     *
     * @return string 
     */
    public function getDataKey()
    {
        return $this->dataKey;
    }

    /**
     * Set readAllowed
     *
     * @param integer $readAllowed
     * @return CustomFields
     */
    public function setReadAllowed($readAllowed)
    {
        $this->readAllowed = $readAllowed;

        return $this;
    }

    /**
     * Get readAllowed
     *
     * @return integer 
     */
    public function getReadAllowed()
    {
        return $this->readAllowed;
    }

    /**
     * Set writeAllowed
     *
     * @param integer $writeAllowed
     * @return CustomFields
     */
    public function setWriteAllowed($writeAllowed)
    {
        $this->writeAllowed = $writeAllowed;

        return $this;
    }

    /**
     * Get writeAllowed
     *
     * @return integer 
     */
    public function getWriteAllowed()
    {
        return $this->writeAllowed;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     * @return CustomFields
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer 
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set formProperties
     *
     * @param string $formProperties
     * @return CustomFields
     */
    public function setFormProperties($formProperties)
    {
        $this->formProperties = $formProperties;

        return $this;
    }

    /**
     * Get formProperties
     *
     * @return string 
     */
    public function getFormProperties()
    {
        return $this->formProperties;
    }

    /**
     * Set formOption
     *
     * @param string $formOption
     * @return CustomFields
     */
    public function setFormOption($formOption)
    {
        $this->formOption = $formOption;

        return $this;
    }

    /**
     * Get formOption
     *
     * @return string 
     */
    public function getFormOption()
    {
        return $this->formOption;
    }

    /**
     * Set validationRegex
     *
     * @param string $validationRegex
     * @return CustomFields
     */
    public function setValidationRegex($validationRegex)
    {
        $this->validationRegex = $validationRegex;

        return $this;
    }

    /**
     * Get validationRegex
     *
     * @return string 
     */
    public function getValidationRegex()
    {
        return $this->validationRegex;
    }

    /**
     * Set validationNotBlank
     *
     * @param integer $validationNotBlank
     * @return CustomFields
     */
    public function setValidationNotBlank($validationNotBlank)
    {
        $this->validationNotBlank = $validationNotBlank;

        return $this;
    }

    /**
     * Get validationNotBlank
     *
     * @return integer 
     */
    public function getValidationNotBlank()
    {
        return $this->validationNotBlank;
    }

    /**
     * Set validationIsNumber
     *
     * @param integer $validationIsNumber
     * @return CustomFields
     */
    public function setValidationIsNumber($validationIsNumber)
    {
        $this->validationIsNumber = $validationIsNumber;

        return $this;
    }

    /**
     * Get validationIsNumber
     *
     * @return integer 
     */
    public function getValidationIsNumber()
    {
        return $this->validationIsNumber;
    }

    /**
     * Set validationMaxNumber
     *
     * @param integer $validationMaxNumber
     * @return CustomFields
     */
    public function setValidationMaxNumber($validationMaxNumber)
    {
        $this->validationMaxNumber = $validationMaxNumber;

        return $this;
    }

    /**
     * Get validationMaxNumber
     *
     * @return integer 
     */
    public function getValidationMaxNumber()
    {
        return $this->validationMaxNumber;
    }

    /**
     * Set validationMinNumber
     *
     * @param integer $validationMinNumber
     * @return CustomFields
     */
    public function setValidationMinNumber($validationMinNumber)
    {
        $this->validationMinNumber = $validationMinNumber;

        return $this;
    }

    /**
     * Get validationMinNumber
     *
     * @return integer 
     */
    public function getValidationMinNumber()
    {
        return $this->validationMinNumber;
    }

    /**
     * Set validationUnique
     *
     * @param integer $validationUnique
     * @return CustomFields
     */
    public function setValidationUnique($validationUnique)
    {
        $this->validationUnique = $validationUnique;

        return $this;
    }

    /**
     * Get validationUnique
     *
     * @return integer 
     */
    public function getValidationUnique()
    {
        return $this->validationUnique;
    }

    /**
     * Set validationMaxLength
     *
     * @param integer $validationMaxLength
     * @return CustomFields
     */
    public function setValidationMaxLength($validationMaxLength)
    {
        $this->validationMaxLength = $validationMaxLength;

        return $this;
    }

    /**
     * Get validationMaxLength
     *
     * @return integer 
     */
    public function getValidationMaxLength()
    {
        return $this->validationMaxLength;
    }

    /**
     * Set validationMinLength
     *
     * @param integer $validationMinLength
     * @return CustomFields
     */
    public function setValidationMinLength($validationMinLength)
    {
        $this->validationMinLength = $validationMinLength;

        return $this;
    }

    /**
     * Get validationMinLength
     *
     * @return integer 
     */
    public function getValidationMinLength()
    {
        return $this->validationMinLength;
    }

    /**
     * Set validationMaxCheckedNumber
     *
     * @param integer $validationMaxCheckedNumber
     * @return CustomFields
     */
    public function setValidationMaxCheckedNumber($validationMaxCheckedNumber)
    {
        $this->validationMaxCheckedNumber = $validationMaxCheckedNumber;

        return $this;
    }

    /**
     * Get validationMaxCheckedNumber
     *
     * @return integer 
     */
    public function getValidationMaxCheckedNumber()
    {
        return $this->validationMaxCheckedNumber;
    }

    /**
     * Set validationMinCheckedNumber
     *
     * @param integer $validationMinCheckedNumber
     * @return CustomFields
     */
    public function setValidationMinCheckedNumber($validationMinCheckedNumber)
    {
        $this->validationMinCheckedNumber = $validationMinCheckedNumber;

        return $this;
    }

    /**
     * Get validationMinCheckedNumber
     *
     * @return integer 
     */
    public function getValidationMinCheckedNumber()
    {
        return $this->validationMinCheckedNumber;
    }

    /**
     * Set validationDocumentFileType
     *
     * @param string $validationDocumentFileType
     * @return CustomFields
     */
    public function setValidationDocumentFileType($validationDocumentFileType)
    {
        $this->validationDocumentFileType = $validationDocumentFileType;

        return $this;
    }

    /**
     * Get validationDocumentFileType
     *
     * @return string 
     */
    public function getValidationDocumentFileType()
    {
        return $this->validationDocumentFileType;
    }

    /**
     * Set validationImageFileType
     *
     * @param string $validationImageFileType
     * @return CustomFields
     */
    public function setValidationImageFileType($validationImageFileType)
    {
        $this->validationImageFileType = $validationImageFileType;

        return $this;
    }

    /**
     * Get validationImageFileType
     *
     * @return string 
     */
    public function getValidationImageFileType()
    {
        return $this->validationImageFileType;
    }

    /**
     * Set validationMaxFileSize
     *
     * @param integer $validationMaxFileSize
     * @return CustomFields
     */
    public function setValidationMaxFileSize($validationMaxFileSize)
    {
        $this->validationMaxFileSize = $validationMaxFileSize;

        return $this;
    }

    /**
     * Get validationMaxFileSize
     *
     * @return integer 
     */
    public function getValidationMaxFileSize()
    {
        return $this->validationMaxFileSize;
    }

    /**
     * Set validationMaxPixelDimensionWidth
     *
     * @param integer $validationMaxPixelDimensionWidth
     * @return CustomFields
     */
    public function setValidationMaxPixelDimensionWidth($validationMaxPixelDimensionWidth)
    {
        $this->validationMaxPixelDimensionWidth = $validationMaxPixelDimensionWidth;

        return $this;
    }

    /**
     * Get validationMaxPixelDimensionWidth
     *
     * @return integer 
     */
    public function getValidationMaxPixelDimensionWidth()
    {
        return $this->validationMaxPixelDimensionWidth;
    }

    /**
     * Set validationMinPixelDimensionWidth
     *
     * @param integer $validationMinPixelDimensionWidth
     * @return CustomFields
     */
    public function setValidationMinPixelDimensionWidth($validationMinPixelDimensionWidth)
    {
        $this->validationMinPixelDimensionWidth = $validationMinPixelDimensionWidth;

        return $this;
    }

    /**
     * Get validationMinPixelDimensionWidth
     *
     * @return integer 
     */
    public function getValidationMinPixelDimensionWidth()
    {
        return $this->validationMinPixelDimensionWidth;
    }

    /**
     * Set validationMaxPixelDimensionHeight
     *
     * @param integer $validationMaxPixelDimensionHeight
     * @return CustomFields
     */
    public function setValidationMaxPixelDimensionHeight($validationMaxPixelDimensionHeight)
    {
        $this->validationMaxPixelDimensionHeight = $validationMaxPixelDimensionHeight;

        return $this;
    }

    /**
     * Get validationMaxPixelDimensionHeight
     *
     * @return integer 
     */
    public function getValidationMaxPixelDimensionHeight()
    {
        return $this->validationMaxPixelDimensionHeight;
    }

    /**
     * Set validationMinPixelDimensionHeight
     *
     * @param integer $validationMinPixelDimensionHeight
     * @return CustomFields
     */
    public function setValidationMinPixelDimensionHeight($validationMinPixelDimensionHeight)
    {
        $this->validationMinPixelDimensionHeight = $validationMinPixelDimensionHeight;

        return $this;
    }

    /**
     * Get validationMinPixelDimensionHeight
     *
     * @return integer 
     */
    public function getValidationMinPixelDimensionHeight()
    {
        return $this->validationMinPixelDimensionHeight;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     * @return CustomFields
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set updateDate
     *
     * @param \DateTime $updateDate
     * @return CustomFields
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }
}
