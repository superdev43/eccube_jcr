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
use Doctrine\DBAL\Types\Type;

if (!Type::hasType('text_array')) {
    Type::addType('text_array', '\Plugin\TabaCustomFields\Types\TextArrayType');
}

/**
 * @ORM\Table(name="plg_taba_custom_fields_contents")
 * @ORM\Entity(repositoryClass="Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository")
 *
 * CustomFieldsContents
 */
class CustomFieldsContents extends \Eccube\Entity\AbstractEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="entity", type="string", length=255, nullable=false, options={"fixed":"false"})
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var string
     */
    private $entity;

    /**
     * @ORM\Id()
     * @ORM\Column(name="target_id", type="integer", nullable=false, options={"unsigned":"false"})
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var integer
     */
    private $targetId;

    /**
     * @ORM\Column(name="plg_field_content1", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent1;

    /**
     * @ORM\Column(name="plg_field_content2", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent2;

    /**
     * @ORM\Column(name="plg_field_content3", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent3;

    /**
     * @ORM\Column(name="plg_field_content4", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent4;

    /**
     * @ORM\Column(name="plg_field_content5", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent5;

    /**
     * @ORM\Column(name="plg_field_content6", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent6;

    /**
     * @ORM\Column(name="plg_field_content7", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent7;

    /**
     * @ORM\Column(name="plg_field_content8", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent8;

    /**
     * @ORM\Column(name="plg_field_content9", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent9;

    /**
     * @ORM\Column(name="plg_field_content10", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent10;

    /**
     * @ORM\Column(name="plg_field_content11", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent11;

    /**
     * @ORM\Column(name="plg_field_content12", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent12;

    /**
     * @ORM\Column(name="plg_field_content13", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent13;

    /**
     * @ORM\Column(name="plg_field_content14", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent14;

    /**
     * @ORM\Column(name="plg_field_content15", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent15;

    /**
     * @ORM\Column(name="plg_field_content16", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent16;

    /**
     * @ORM\Column(name="plg_field_content17", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent17;

    /**
     * @ORM\Column(name="plg_field_content18", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent18;

    /**
     * @ORM\Column(name="plg_field_content19", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent19;

    /**
     * @ORM\Column(name="plg_field_content20", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent20;

    /**
     * @ORM\Column(name="plg_field_content21", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent21;

    /**
     * @ORM\Column(name="plg_field_content22", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent22;

    /**
     * @ORM\Column(name="plg_field_content23", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent23;

    /**
     * @ORM\Column(name="plg_field_content24", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent24;

    /**
     * @ORM\Column(name="plg_field_content25", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent25;

    /**
     * @ORM\Column(name="plg_field_content26", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent26;

    /**
     * @ORM\Column(name="plg_field_content27", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent27;

    /**
     * @ORM\Column(name="plg_field_content28", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent28;

    /**
     * @ORM\Column(name="plg_field_content29", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent29;

    /**
     * @ORM\Column(name="plg_field_content30", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent30;

    /**
     * @ORM\Column(name="plg_field_content31", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent31;

    /**
     * @ORM\Column(name="plg_field_content32", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent32;

    /**
     * @ORM\Column(name="plg_field_content33", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent33;

    /**
     * @ORM\Column(name="plg_field_content34", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent34;

    /**
     * @ORM\Column(name="plg_field_content35", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent35;

    /**
     * @ORM\Column(name="plg_field_content36", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent36;

    /**
     * @ORM\Column(name="plg_field_content37", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent37;

    /**
     * @ORM\Column(name="plg_field_content38", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent38;

    /**
     * @ORM\Column(name="plg_field_content39", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent39;

    /**
     * @ORM\Column(name="plg_field_content40", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent40;

    /**
     * @ORM\Column(name="plg_field_content41", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent41;

    /**
     * @ORM\Column(name="plg_field_content42", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent42;

    /**
     * @ORM\Column(name="plg_field_content43", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent43;

    /**
     * @ORM\Column(name="plg_field_content44", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent44;

    /**
     * @ORM\Column(name="plg_field_content45", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent45;

    /**
     * @ORM\Column(name="plg_field_content46", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent46;

    /**
     * @ORM\Column(name="plg_field_content47", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent47;

    /**
     * @ORM\Column(name="plg_field_content48", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent48;

    /**
     * @ORM\Column(name="plg_field_content49", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent49;

    /**
     * @ORM\Column(name="plg_field_content50", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent50;

    /**
     * @ORM\Column(name="plg_field_content51", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent51;

    /**
     * @ORM\Column(name="plg_field_content52", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent52;

    /**
     * @ORM\Column(name="plg_field_content53", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent53;

    /**
     * @ORM\Column(name="plg_field_content54", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent54;

    /**
     * @ORM\Column(name="plg_field_content55", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent55;

    /**
     * @ORM\Column(name="plg_field_content56", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent56;

    /**
     * @ORM\Column(name="plg_field_content57", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent57;

    /**
     * @ORM\Column(name="plg_field_content58", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent58;

    /**
     * @ORM\Column(name="plg_field_content59", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent59;

    /**
     * @ORM\Column(name="plg_field_content60", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent60;

    /**
     * @ORM\Column(name="plg_field_content61", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent61;

    /**
     * @ORM\Column(name="plg_field_content62", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent62;

    /**
     * @ORM\Column(name="plg_field_content63", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent63;

    /**
     * @ORM\Column(name="plg_field_content64", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent64;

    /**
     * @ORM\Column(name="plg_field_content65", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent65;

    /**
     * @ORM\Column(name="plg_field_content66", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent66;

    /**
     * @ORM\Column(name="plg_field_content67", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent67;

    /**
     * @ORM\Column(name="plg_field_content68", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent68;

    /**
     * @ORM\Column(name="plg_field_content69", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent69;

    /**
     * @ORM\Column(name="plg_field_content70", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent70;

    /**
     * @ORM\Column(name="plg_field_content71", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent71;

    /**
     * @ORM\Column(name="plg_field_content72", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent72;

    /**
     * @ORM\Column(name="plg_field_content73", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent73;

    /**
     * @ORM\Column(name="plg_field_content74", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent74;

    /**
     * @ORM\Column(name="plg_field_content75", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent75;

    /**
     * @ORM\Column(name="plg_field_content76", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent76;

    /**
     * @ORM\Column(name="plg_field_content77", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent77;

    /**
     * @ORM\Column(name="plg_field_content78", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent78;

    /**
     * @ORM\Column(name="plg_field_content79", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent79;

    /**
     * @ORM\Column(name="plg_field_content80", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent80;

    /**
     * @ORM\Column(name="plg_field_content81", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent81;

    /**
     * @ORM\Column(name="plg_field_content82", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent82;

    /**
     * @ORM\Column(name="plg_field_content83", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent83;

    /**
     * @ORM\Column(name="plg_field_content84", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent84;

    /**
     * @ORM\Column(name="plg_field_content85", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent85;

    /**
     * @ORM\Column(name="plg_field_content86", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent86;

    /**
     * @ORM\Column(name="plg_field_content87", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent87;

    /**
     * @ORM\Column(name="plg_field_content88", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent88;

    /**
     * @ORM\Column(name="plg_field_content89", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent89;

    /**
     * @ORM\Column(name="plg_field_content90", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent90;

    /**
     * @ORM\Column(name="plg_field_content91", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent91;

    /**
     * @ORM\Column(name="plg_field_content92", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent92;

    /**
     * @ORM\Column(name="plg_field_content93", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent93;

    /**
     * @ORM\Column(name="plg_field_content94", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent94;

    /**
     * @ORM\Column(name="plg_field_content95", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent95;

    /**
     * @ORM\Column(name="plg_field_content96", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent96;

    /**
     * @ORM\Column(name="plg_field_content97", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent97;

    /**
     * @ORM\Column(name="plg_field_content98", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent98;

    /**
     * @ORM\Column(name="plg_field_content99", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent99;

    /**
     * @ORM\Column(name="plg_field_content100", type="text_array", length=null, nullable=true, options={"fixed":false})
     *
     * @var string
     */
    private $plgFieldContent100;

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
     * Set entity
     *
     * @param string $entity
     * @return CustomFieldsContents
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set targetId
     *
     * @param string $targetId
     * @return CustomFieldsContents
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Get targetId
     *
     * @return integer 
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Set plgFieldContent1
     *
     * @param string $plgFieldContent1
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent1($plgFieldContent1)
    {
        $this->plgFieldContent1 = $plgFieldContent1;

        return $this;
    }

    /**
     * Get plgFieldContent1
     *
     * @return string 
     */
    public function getPlgFieldContent1()
    {
        return $this->plgFieldContent1;
    }

    /**
     * Set plgFieldContent2
     *
     * @param string $plgFieldContent2
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent2($plgFieldContent2)
    {
        $this->plgFieldContent2 = $plgFieldContent2;

        return $this;
    }

    /**
     * Get plgFieldContent2
     *
     * @return string 
     */
    public function getPlgFieldContent2()
    {
        return $this->plgFieldContent2;
    }

    /**
     * Set plgFieldContent3
     *
     * @param string $plgFieldContent3
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent3($plgFieldContent3)
    {
        $this->plgFieldContent3 = $plgFieldContent3;

        return $this;
    }

    /**
     * Get plgFieldContent3
     *
     * @return string 
     */
    public function getPlgFieldContent3()
    {
        return $this->plgFieldContent3;
    }

    /**
     * Set plgFieldContent4
     *
     * @param string $plgFieldContent4
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent4($plgFieldContent4)
    {
        $this->plgFieldContent4 = $plgFieldContent4;

        return $this;
    }

    /**
     * Get plgFieldContent4
     *
     * @return string 
     */
    public function getPlgFieldContent4()
    {
        return $this->plgFieldContent4;
    }

    /**
     * Set plgFieldContent5
     *
     * @param string $plgFieldContent5
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent5($plgFieldContent5)
    {
        $this->plgFieldContent5 = $plgFieldContent5;

        return $this;
    }

    /**
     * Get plgFieldContent5
     *
     * @return string 
     */
    public function getPlgFieldContent5()
    {
        return $this->plgFieldContent5;
    }

    /**
     * Set plgFieldContent6
     *
     * @param string $plgFieldContent6
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent6($plgFieldContent6)
    {
        $this->plgFieldContent6 = $plgFieldContent6;

        return $this;
    }

    /**
     * Get plgFieldContent6
     *
     * @return string 
     */
    public function getPlgFieldContent6()
    {
        return $this->plgFieldContent6;
    }

    /**
     * Set plgFieldContent7
     *
     * @param string $plgFieldContent7
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent7($plgFieldContent7)
    {
        $this->plgFieldContent7 = $plgFieldContent7;

        return $this;
    }

    /**
     * Get plgFieldContent7
     *
     * @return string 
     */
    public function getPlgFieldContent7()
    {
        return $this->plgFieldContent7;
    }

    /**
     * Set plgFieldContent8
     *
     * @param string $plgFieldContent8
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent8($plgFieldContent8)
    {
        $this->plgFieldContent8 = $plgFieldContent8;

        return $this;
    }

    /**
     * Get plgFieldContent8
     *
     * @return string 
     */
    public function getPlgFieldContent8()
    {
        return $this->plgFieldContent8;
    }

    /**
     * Set plgFieldContent9
     *
     * @param string $plgFieldContent9
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent9($plgFieldContent9)
    {
        $this->plgFieldContent9 = $plgFieldContent9;

        return $this;
    }

    /**
     * Get plgFieldContent9
     *
     * @return string 
     */
    public function getPlgFieldContent9()
    {
        return $this->plgFieldContent9;
    }

    /**
     * Set plgFieldContent10
     *
     * @param string $plgFieldContent10
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent10($plgFieldContent10)
    {
        $this->plgFieldContent10 = $plgFieldContent10;

        return $this;
    }

    /**
     * Get plgFieldContent10
     *
     * @return string 
     */
    public function getPlgFieldContent10()
    {
        return $this->plgFieldContent10;
    }

    /**
     * Set plgFieldContent11
     *
     * @param string $plgFieldContent11
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent11($plgFieldContent11)
    {
        $this->plgFieldContent11 = $plgFieldContent11;

        return $this;
    }

    /**
     * Get plgFieldContent11
     *
     * @return string 
     */
    public function getPlgFieldContent11()
    {
        return $this->plgFieldContent11;
    }

    /**
     * Set plgFieldContent12
     *
     * @param string $plgFieldContent12
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent12($plgFieldContent12)
    {
        $this->plgFieldContent12 = $plgFieldContent12;

        return $this;
    }

    /**
     * Get plgFieldContent12
     *
     * @return string 
     */
    public function getPlgFieldContent12()
    {
        return $this->plgFieldContent12;
    }

    /**
     * Set plgFieldContent13
     *
     * @param string $plgFieldContent13
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent13($plgFieldContent13)
    {
        $this->plgFieldContent13 = $plgFieldContent13;

        return $this;
    }

    /**
     * Get plgFieldContent13
     *
     * @return string 
     */
    public function getPlgFieldContent13()
    {
        return $this->plgFieldContent13;
    }

    /**
     * Set plgFieldContent14
     *
     * @param string $plgFieldContent14
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent14($plgFieldContent14)
    {
        $this->plgFieldContent14 = $plgFieldContent14;

        return $this;
    }

    /**
     * Get plgFieldContent14
     *
     * @return string 
     */
    public function getPlgFieldContent14()
    {
        return $this->plgFieldContent14;
    }

    /**
     * Set plgFieldContent15
     *
     * @param string $plgFieldContent15
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent15($plgFieldContent15)
    {
        $this->plgFieldContent15 = $plgFieldContent15;

        return $this;
    }

    /**
     * Get plgFieldContent15
     *
     * @return string 
     */
    public function getPlgFieldContent15()
    {
        return $this->plgFieldContent15;
    }

    /**
     * Set plgFieldContent16
     *
     * @param string $plgFieldContent16
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent16($plgFieldContent16)
    {
        $this->plgFieldContent16 = $plgFieldContent16;

        return $this;
    }

    /**
     * Get plgFieldContent16
     *
     * @return string 
     */
    public function getPlgFieldContent16()
    {
        return $this->plgFieldContent16;
    }

    /**
     * Set plgFieldContent17
     *
     * @param string $plgFieldContent17
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent17($plgFieldContent17)
    {
        $this->plgFieldContent17 = $plgFieldContent17;

        return $this;
    }

    /**
     * Get plgFieldContent17
     *
     * @return string 
     */
    public function getPlgFieldContent17()
    {
        return $this->plgFieldContent17;
    }

    /**
     * Set plgFieldContent18
     *
     * @param string $plgFieldContent18
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent18($plgFieldContent18)
    {
        $this->plgFieldContent18 = $plgFieldContent18;

        return $this;
    }

    /**
     * Get plgFieldContent18
     *
     * @return string 
     */
    public function getPlgFieldContent18()
    {
        return $this->plgFieldContent18;
    }

    /**
     * Set plgFieldContent19
     *
     * @param string $plgFieldContent19
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent19($plgFieldContent19)
    {
        $this->plgFieldContent19 = $plgFieldContent19;

        return $this;
    }

    /**
     * Get plgFieldContent19
     *
     * @return string 
     */
    public function getPlgFieldContent19()
    {
        return $this->plgFieldContent19;
    }

    /**
     * Set plgFieldContent20
     *
     * @param string $plgFieldContent20
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent20($plgFieldContent20)
    {
        $this->plgFieldContent20 = $plgFieldContent20;

        return $this;
    }

    /**
     * Get plgFieldContent20
     *
     * @return string 
     */
    public function getPlgFieldContent20()
    {
        return $this->plgFieldContent20;
    }

    /**
     * Set plgFieldContent21
     *
     * @param string $plgFieldContent21
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent21($plgFieldContent21)
    {
        $this->plgFieldContent21 = $plgFieldContent21;

        return $this;
    }

    /**
     * Get plgFieldContent21
     *
     * @return string 
     */
    public function getPlgFieldContent21()
    {
        return $this->plgFieldContent21;
    }

    /**
     * Set plgFieldContent22
     *
     * @param string $plgFieldContent22
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent22($plgFieldContent22)
    {
        $this->plgFieldContent22 = $plgFieldContent22;

        return $this;
    }

    /**
     * Get plgFieldContent22
     *
     * @return string 
     */
    public function getPlgFieldContent22()
    {
        return $this->plgFieldContent22;
    }

    /**
     * Set plgFieldContent23
     *
     * @param string $plgFieldContent23
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent23($plgFieldContent23)
    {
        $this->plgFieldContent23 = $plgFieldContent23;

        return $this;
    }

    /**
     * Get plgFieldContent23
     *
     * @return string 
     */
    public function getPlgFieldContent23()
    {
        return $this->plgFieldContent23;
    }

    /**
     * Set plgFieldContent24
     *
     * @param string $plgFieldContent24
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent24($plgFieldContent24)
    {
        $this->plgFieldContent24 = $plgFieldContent24;

        return $this;
    }

    /**
     * Get plgFieldContent24
     *
     * @return string 
     */
    public function getPlgFieldContent24()
    {
        return $this->plgFieldContent24;
    }

    /**
     * Set plgFieldContent25
     *
     * @param string $plgFieldContent25
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent25($plgFieldContent25)
    {
        $this->plgFieldContent25 = $plgFieldContent25;

        return $this;
    }

    /**
     * Get plgFieldContent25
     *
     * @return string 
     */
    public function getPlgFieldContent25()
    {
        return $this->plgFieldContent25;
    }

    /**
     * Set plgFieldContent26
     *
     * @param string $plgFieldContent26
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent26($plgFieldContent26)
    {
        $this->plgFieldContent26 = $plgFieldContent26;

        return $this;
    }

    /**
     * Get plgFieldContent26
     *
     * @return string 
     */
    public function getPlgFieldContent26()
    {
        return $this->plgFieldContent26;
    }

    /**
     * Set plgFieldContent27
     *
     * @param string $plgFieldContent27
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent27($plgFieldContent27)
    {
        $this->plgFieldContent27 = $plgFieldContent27;

        return $this;
    }

    /**
     * Get plgFieldContent27
     *
     * @return string 
     */
    public function getPlgFieldContent27()
    {
        return $this->plgFieldContent27;
    }

    /**
     * Set plgFieldContent28
     *
     * @param string $plgFieldContent28
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent28($plgFieldContent28)
    {
        $this->plgFieldContent28 = $plgFieldContent28;

        return $this;
    }

    /**
     * Get plgFieldContent28
     *
     * @return string 
     */
    public function getPlgFieldContent28()
    {
        return $this->plgFieldContent28;
    }

    /**
     * Set plgFieldContent29
     *
     * @param string $plgFieldContent29
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent29($plgFieldContent29)
    {
        $this->plgFieldContent29 = $plgFieldContent29;

        return $this;
    }

    /**
     * Get plgFieldContent29
     *
     * @return string 
     */
    public function getPlgFieldContent29()
    {
        return $this->plgFieldContent29;
    }

    /**
     * Set plgFieldContent30
     *
     * @param string $plgFieldContent30
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent30($plgFieldContent30)
    {
        $this->plgFieldContent30 = $plgFieldContent30;

        return $this;
    }

    /**
     * Get plgFieldContent30
     *
     * @return string 
     */
    public function getPlgFieldContent30()
    {
        return $this->plgFieldContent30;
    }

    /**
     * Set plgFieldContent31
     *
     * @param string $plgFieldContent31
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent31($plgFieldContent31)
    {
        $this->plgFieldContent31 = $plgFieldContent31;

        return $this;
    }

    /**
     * Get plgFieldContent31
     *
     * @return string 
     */
    public function getPlgFieldContent31()
    {
        return $this->plgFieldContent31;
    }

    /**
     * Set plgFieldContent32
     *
     * @param string $plgFieldContent32
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent32($plgFieldContent32)
    {
        $this->plgFieldContent32 = $plgFieldContent32;

        return $this;
    }

    /**
     * Get plgFieldContent32
     *
     * @return string 
     */
    public function getPlgFieldContent32()
    {
        return $this->plgFieldContent32;
    }

    /**
     * Set plgFieldContent33
     *
     * @param string $plgFieldContent33
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent33($plgFieldContent33)
    {
        $this->plgFieldContent33 = $plgFieldContent33;

        return $this;
    }

    /**
     * Get plgFieldContent33
     *
     * @return string 
     */
    public function getPlgFieldContent33()
    {
        return $this->plgFieldContent33;
    }

    /**
     * Set plgFieldContent34
     *
     * @param string $plgFieldContent34
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent34($plgFieldContent34)
    {
        $this->plgFieldContent34 = $plgFieldContent34;

        return $this;
    }

    /**
     * Get plgFieldContent34
     *
     * @return string 
     */
    public function getPlgFieldContent34()
    {
        return $this->plgFieldContent34;
    }

    /**
     * Set plgFieldContent35
     *
     * @param string $plgFieldContent35
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent35($plgFieldContent35)
    {
        $this->plgFieldContent35 = $plgFieldContent35;

        return $this;
    }

    /**
     * Get plgFieldContent35
     *
     * @return string 
     */
    public function getPlgFieldContent35()
    {
        return $this->plgFieldContent35;
    }

    /**
     * Set plgFieldContent36
     *
     * @param string $plgFieldContent36
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent36($plgFieldContent36)
    {
        $this->plgFieldContent36 = $plgFieldContent36;

        return $this;
    }

    /**
     * Get plgFieldContent36
     *
     * @return string 
     */
    public function getPlgFieldContent36()
    {
        return $this->plgFieldContent36;
    }

    /**
     * Set plgFieldContent37
     *
     * @param string $plgFieldContent37
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent37($plgFieldContent37)
    {
        $this->plgFieldContent37 = $plgFieldContent37;

        return $this;
    }

    /**
     * Get plgFieldContent37
     *
     * @return string 
     */
    public function getPlgFieldContent37()
    {
        return $this->plgFieldContent37;
    }

    /**
     * Set plgFieldContent38
     *
     * @param string $plgFieldContent38
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent38($plgFieldContent38)
    {
        $this->plgFieldContent38 = $plgFieldContent38;

        return $this;
    }

    /**
     * Get plgFieldContent38
     *
     * @return string 
     */
    public function getPlgFieldContent38()
    {
        return $this->plgFieldContent38;
    }

    /**
     * Set plgFieldContent39
     *
     * @param string $plgFieldContent39
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent39($plgFieldContent39)
    {
        $this->plgFieldContent39 = $plgFieldContent39;

        return $this;
    }

    /**
     * Get plgFieldContent39
     *
     * @return string 
     */
    public function getPlgFieldContent39()
    {
        return $this->plgFieldContent39;
    }

    /**
     * Set plgFieldContent40
     *
     * @param string $plgFieldContent40
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent40($plgFieldContent40)
    {
        $this->plgFieldContent40 = $plgFieldContent40;

        return $this;
    }

    /**
     * Get plgFieldContent40
     *
     * @return string 
     */
    public function getPlgFieldContent40()
    {
        return $this->plgFieldContent40;
    }

    /**
     * Set plgFieldContent41
     *
     * @param string $plgFieldContent41
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent41($plgFieldContent41)
    {
        $this->plgFieldContent41 = $plgFieldContent41;

        return $this;
    }

    /**
     * Get plgFieldContent41
     *
     * @return string 
     */
    public function getPlgFieldContent41()
    {
        return $this->plgFieldContent41;
    }

    /**
     * Set plgFieldContent42
     *
     * @param string $plgFieldContent42
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent42($plgFieldContent42)
    {
        $this->plgFieldContent42 = $plgFieldContent42;

        return $this;
    }

    /**
     * Get plgFieldContent42
     *
     * @return string 
     */
    public function getPlgFieldContent42()
    {
        return $this->plgFieldContent42;
    }

    /**
     * Set plgFieldContent43
     *
     * @param string $plgFieldContent43
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent43($plgFieldContent43)
    {
        $this->plgFieldContent43 = $plgFieldContent43;

        return $this;
    }

    /**
     * Get plgFieldContent43
     *
     * @return string 
     */
    public function getPlgFieldContent43()
    {
        return $this->plgFieldContent43;
    }

    /**
     * Set plgFieldContent44
     *
     * @param string $plgFieldContent44
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent44($plgFieldContent44)
    {
        $this->plgFieldContent44 = $plgFieldContent44;

        return $this;
    }

    /**
     * Get plgFieldContent44
     *
     * @return string 
     */
    public function getPlgFieldContent44()
    {
        return $this->plgFieldContent44;
    }

    /**
     * Set plgFieldContent45
     *
     * @param string $plgFieldContent45
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent45($plgFieldContent45)
    {
        $this->plgFieldContent45 = $plgFieldContent45;

        return $this;
    }

    /**
     * Get plgFieldContent45
     *
     * @return string 
     */
    public function getPlgFieldContent45()
    {
        return $this->plgFieldContent45;
    }

    /**
     * Set plgFieldContent46
     *
     * @param string $plgFieldContent46
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent46($plgFieldContent46)
    {
        $this->plgFieldContent46 = $plgFieldContent46;

        return $this;
    }

    /**
     * Get plgFieldContent46
     *
     * @return string 
     */
    public function getPlgFieldContent46()
    {
        return $this->plgFieldContent46;
    }

    /**
     * Set plgFieldContent47
     *
     * @param string $plgFieldContent47
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent47($plgFieldContent47)
    {
        $this->plgFieldContent47 = $plgFieldContent47;

        return $this;
    }

    /**
     * Get plgFieldContent47
     *
     * @return string 
     */
    public function getPlgFieldContent47()
    {
        return $this->plgFieldContent47;
    }

    /**
     * Set plgFieldContent48
     *
     * @param string $plgFieldContent48
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent48($plgFieldContent48)
    {
        $this->plgFieldContent48 = $plgFieldContent48;

        return $this;
    }

    /**
     * Get plgFieldContent48
     *
     * @return string 
     */
    public function getPlgFieldContent48()
    {
        return $this->plgFieldContent48;
    }

    /**
     * Set plgFieldContent49
     *
     * @param string $plgFieldContent49
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent49($plgFieldContent49)
    {
        $this->plgFieldContent49 = $plgFieldContent49;

        return $this;
    }

    /**
     * Get plgFieldContent49
     *
     * @return string 
     */
    public function getPlgFieldContent49()
    {
        return $this->plgFieldContent49;
    }

    /**
     * Set plgFieldContent50
     *
     * @param string $plgFieldContent50
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent50($plgFieldContent50)
    {
        $this->plgFieldContent50 = $plgFieldContent50;

        return $this;
    }

    /**
     * Get plgFieldContent50
     *
     * @return string 
     */
    public function getPlgFieldContent50()
    {
        return $this->plgFieldContent50;
    }

    /**
     * Set plgFieldContent51
     *
     * @param string $plgFieldContent51
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent51($plgFieldContent51)
    {
        $this->plgFieldContent51 = $plgFieldContent51;

        return $this;
    }

    /**
     * Get plgFieldContent51
     *
     * @return string 
     */
    public function getPlgFieldContent51()
    {
        return $this->plgFieldContent51;
    }

    /**
     * Set plgFieldContent52
     *
     * @param string $plgFieldContent52
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent52($plgFieldContent52)
    {
        $this->plgFieldContent52 = $plgFieldContent52;

        return $this;
    }

    /**
     * Get plgFieldContent52
     *
     * @return string 
     */
    public function getPlgFieldContent52()
    {
        return $this->plgFieldContent52;
    }

    /**
     * Set plgFieldContent53
     *
     * @param string $plgFieldContent53
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent53($plgFieldContent53)
    {
        $this->plgFieldContent53 = $plgFieldContent53;

        return $this;
    }

    /**
     * Get plgFieldContent53
     *
     * @return string 
     */
    public function getPlgFieldContent53()
    {
        return $this->plgFieldContent53;
    }

    /**
     * Set plgFieldContent54
     *
     * @param string $plgFieldContent54
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent54($plgFieldContent54)
    {
        $this->plgFieldContent54 = $plgFieldContent54;

        return $this;
    }

    /**
     * Get plgFieldContent54
     *
     * @return string 
     */
    public function getPlgFieldContent54()
    {
        return $this->plgFieldContent54;
    }

    /**
     * Set plgFieldContent55
     *
     * @param string $plgFieldContent55
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent55($plgFieldContent55)
    {
        $this->plgFieldContent55 = $plgFieldContent55;

        return $this;
    }

    /**
     * Get plgFieldContent55
     *
     * @return string 
     */
    public function getPlgFieldContent55()
    {
        return $this->plgFieldContent55;
    }

    /**
     * Set plgFieldContent56
     *
     * @param string $plgFieldContent56
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent56($plgFieldContent56)
    {
        $this->plgFieldContent56 = $plgFieldContent56;

        return $this;
    }

    /**
     * Get plgFieldContent56
     *
     * @return string 
     */
    public function getPlgFieldContent56()
    {
        return $this->plgFieldContent56;
    }

    /**
     * Set plgFieldContent57
     *
     * @param string $plgFieldContent57
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent57($plgFieldContent57)
    {
        $this->plgFieldContent57 = $plgFieldContent57;

        return $this;
    }

    /**
     * Get plgFieldContent57
     *
     * @return string 
     */
    public function getPlgFieldContent57()
    {
        return $this->plgFieldContent57;
    }

    /**
     * Set plgFieldContent58
     *
     * @param string $plgFieldContent58
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent58($plgFieldContent58)
    {
        $this->plgFieldContent58 = $plgFieldContent58;

        return $this;
    }

    /**
     * Get plgFieldContent58
     *
     * @return string 
     */
    public function getPlgFieldContent58()
    {
        return $this->plgFieldContent58;
    }

    /**
     * Set plgFieldContent59
     *
     * @param string $plgFieldContent59
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent59($plgFieldContent59)
    {
        $this->plgFieldContent59 = $plgFieldContent59;

        return $this;
    }

    /**
     * Get plgFieldContent59
     *
     * @return string 
     */
    public function getPlgFieldContent59()
    {
        return $this->plgFieldContent59;
    }

    /**
     * Set plgFieldContent60
     *
     * @param string $plgFieldContent60
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent60($plgFieldContent60)
    {
        $this->plgFieldContent60 = $plgFieldContent60;

        return $this;
    }

    /**
     * Get plgFieldContent60
     *
     * @return string 
     */
    public function getPlgFieldContent60()
    {
        return $this->plgFieldContent60;
    }

    /**
     * Set plgFieldContent61
     *
     * @param string $plgFieldContent61
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent61($plgFieldContent61)
    {
        $this->plgFieldContent61 = $plgFieldContent61;

        return $this;
    }

    /**
     * Get plgFieldContent61
     *
     * @return string 
     */
    public function getPlgFieldContent61()
    {
        return $this->plgFieldContent61;
    }

    /**
     * Set plgFieldContent62
     *
     * @param string $plgFieldContent62
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent62($plgFieldContent62)
    {
        $this->plgFieldContent62 = $plgFieldContent62;

        return $this;
    }

    /**
     * Get plgFieldContent62
     *
     * @return string 
     */
    public function getPlgFieldContent62()
    {
        return $this->plgFieldContent62;
    }

    /**
     * Set plgFieldContent63
     *
     * @param string $plgFieldContent63
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent63($plgFieldContent63)
    {
        $this->plgFieldContent63 = $plgFieldContent63;

        return $this;
    }

    /**
     * Get plgFieldContent63
     *
     * @return string 
     */
    public function getPlgFieldContent63()
    {
        return $this->plgFieldContent63;
    }

    /**
     * Set plgFieldContent64
     *
     * @param string $plgFieldContent64
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent64($plgFieldContent64)
    {
        $this->plgFieldContent64 = $plgFieldContent64;

        return $this;
    }

    /**
     * Get plgFieldContent64
     *
     * @return string 
     */
    public function getPlgFieldContent64()
    {
        return $this->plgFieldContent64;
    }

    /**
     * Set plgFieldContent65
     *
     * @param string $plgFieldContent65
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent65($plgFieldContent65)
    {
        $this->plgFieldContent65 = $plgFieldContent65;

        return $this;
    }

    /**
     * Get plgFieldContent65
     *
     * @return string 
     */
    public function getPlgFieldContent65()
    {
        return $this->plgFieldContent65;
    }

    /**
     * Set plgFieldContent66
     *
     * @param string $plgFieldContent66
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent66($plgFieldContent66)
    {
        $this->plgFieldContent66 = $plgFieldContent66;

        return $this;
    }

    /**
     * Get plgFieldContent66
     *
     * @return string 
     */
    public function getPlgFieldContent66()
    {
        return $this->plgFieldContent66;
    }

    /**
     * Set plgFieldContent67
     *
     * @param string $plgFieldContent67
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent67($plgFieldContent67)
    {
        $this->plgFieldContent67 = $plgFieldContent67;

        return $this;
    }

    /**
     * Get plgFieldContent67
     *
     * @return string 
     */
    public function getPlgFieldContent67()
    {
        return $this->plgFieldContent67;
    }

    /**
     * Set plgFieldContent68
     *
     * @param string $plgFieldContent68
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent68($plgFieldContent68)
    {
        $this->plgFieldContent68 = $plgFieldContent68;

        return $this;
    }

    /**
     * Get plgFieldContent68
     *
     * @return string 
     */
    public function getPlgFieldContent68()
    {
        return $this->plgFieldContent68;
    }

    /**
     * Set plgFieldContent69
     *
     * @param string $plgFieldContent69
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent69($plgFieldContent69)
    {
        $this->plgFieldContent69 = $plgFieldContent69;

        return $this;
    }

    /**
     * Get plgFieldContent69
     *
     * @return string 
     */
    public function getPlgFieldContent69()
    {
        return $this->plgFieldContent69;
    }

    /**
     * Set plgFieldContent70
     *
     * @param string $plgFieldContent70
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent70($plgFieldContent70)
    {
        $this->plgFieldContent70 = $plgFieldContent70;

        return $this;
    }

    /**
     * Get plgFieldContent70
     *
     * @return string 
     */
    public function getPlgFieldContent70()
    {
        return $this->plgFieldContent70;
    }

    /**
     * Set plgFieldContent71
     *
     * @param string $plgFieldContent71
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent71($plgFieldContent71)
    {
        $this->plgFieldContent71 = $plgFieldContent71;

        return $this;
    }

    /**
     * Get plgFieldContent71
     *
     * @return string 
     */
    public function getPlgFieldContent71()
    {
        return $this->plgFieldContent71;
    }

    /**
     * Set plgFieldContent72
     *
     * @param string $plgFieldContent72
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent72($plgFieldContent72)
    {
        $this->plgFieldContent72 = $plgFieldContent72;

        return $this;
    }

    /**
     * Get plgFieldContent72
     *
     * @return string 
     */
    public function getPlgFieldContent72()
    {
        return $this->plgFieldContent72;
    }

    /**
     * Set plgFieldContent73
     *
     * @param string $plgFieldContent73
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent73($plgFieldContent73)
    {
        $this->plgFieldContent73 = $plgFieldContent73;

        return $this;
    }

    /**
     * Get plgFieldContent73
     *
     * @return string 
     */
    public function getPlgFieldContent73()
    {
        return $this->plgFieldContent73;
    }

    /**
     * Set plgFieldContent74
     *
     * @param string $plgFieldContent74
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent74($plgFieldContent74)
    {
        $this->plgFieldContent74 = $plgFieldContent74;

        return $this;
    }

    /**
     * Get plgFieldContent74
     *
     * @return string 
     */
    public function getPlgFieldContent74()
    {
        return $this->plgFieldContent74;
    }

    /**
     * Set plgFieldContent75
     *
     * @param string $plgFieldContent75
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent75($plgFieldContent75)
    {
        $this->plgFieldContent75 = $plgFieldContent75;

        return $this;
    }

    /**
     * Get plgFieldContent75
     *
     * @return string 
     */
    public function getPlgFieldContent75()
    {
        return $this->plgFieldContent75;
    }

    /**
     * Set plgFieldContent76
     *
     * @param string $plgFieldContent76
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent76($plgFieldContent76)
    {
        $this->plgFieldContent76 = $plgFieldContent76;

        return $this;
    }

    /**
     * Get plgFieldContent76
     *
     * @return string 
     */
    public function getPlgFieldContent76()
    {
        return $this->plgFieldContent76;
    }

    /**
     * Set plgFieldContent77
     *
     * @param string $plgFieldContent77
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent77($plgFieldContent77)
    {
        $this->plgFieldContent77 = $plgFieldContent77;

        return $this;
    }

    /**
     * Get plgFieldContent77
     *
     * @return string 
     */
    public function getPlgFieldContent77()
    {
        return $this->plgFieldContent77;
    }

    /**
     * Set plgFieldContent78
     *
     * @param string $plgFieldContent78
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent78($plgFieldContent78)
    {
        $this->plgFieldContent78 = $plgFieldContent78;

        return $this;
    }

    /**
     * Get plgFieldContent78
     *
     * @return string 
     */
    public function getPlgFieldContent78()
    {
        return $this->plgFieldContent78;
    }

    /**
     * Set plgFieldContent79
     *
     * @param string $plgFieldContent79
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent79($plgFieldContent79)
    {
        $this->plgFieldContent79 = $plgFieldContent79;

        return $this;
    }

    /**
     * Get plgFieldContent79
     *
     * @return string 
     */
    public function getPlgFieldContent79()
    {
        return $this->plgFieldContent79;
    }

    /**
     * Set plgFieldContent80
     *
     * @param string $plgFieldContent80
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent80($plgFieldContent80)
    {
        $this->plgFieldContent80 = $plgFieldContent80;

        return $this;
    }

    /**
     * Get plgFieldContent80
     *
     * @return string 
     */
    public function getPlgFieldContent80()
    {
        return $this->plgFieldContent80;
    }

    /**
     * Set plgFieldContent81
     *
     * @param string $plgFieldContent81
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent81($plgFieldContent81)
    {
        $this->plgFieldContent81 = $plgFieldContent81;

        return $this;
    }

    /**
     * Get plgFieldContent81
     *
     * @return string 
     */
    public function getPlgFieldContent81()
    {
        return $this->plgFieldContent81;
    }

    /**
     * Set plgFieldContent82
     *
     * @param string $plgFieldContent82
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent82($plgFieldContent82)
    {
        $this->plgFieldContent82 = $plgFieldContent82;

        return $this;
    }

    /**
     * Get plgFieldContent82
     *
     * @return string 
     */
    public function getPlgFieldContent82()
    {
        return $this->plgFieldContent82;
    }

    /**
     * Set plgFieldContent83
     *
     * @param string $plgFieldContent83
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent83($plgFieldContent83)
    {
        $this->plgFieldContent83 = $plgFieldContent83;

        return $this;
    }

    /**
     * Get plgFieldContent83
     *
     * @return string 
     */
    public function getPlgFieldContent83()
    {
        return $this->plgFieldContent83;
    }

    /**
     * Set plgFieldContent84
     *
     * @param string $plgFieldContent84
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent84($plgFieldContent84)
    {
        $this->plgFieldContent84 = $plgFieldContent84;

        return $this;
    }

    /**
     * Get plgFieldContent84
     *
     * @return string 
     */
    public function getPlgFieldContent84()
    {
        return $this->plgFieldContent84;
    }

    /**
     * Set plgFieldContent85
     *
     * @param string $plgFieldContent85
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent85($plgFieldContent85)
    {
        $this->plgFieldContent85 = $plgFieldContent85;

        return $this;
    }

    /**
     * Get plgFieldContent85
     *
     * @return string 
     */
    public function getPlgFieldContent85()
    {
        return $this->plgFieldContent85;
    }

    /**
     * Set plgFieldContent86
     *
     * @param string $plgFieldContent86
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent86($plgFieldContent86)
    {
        $this->plgFieldContent86 = $plgFieldContent86;

        return $this;
    }

    /**
     * Get plgFieldContent86
     *
     * @return string 
     */
    public function getPlgFieldContent86()
    {
        return $this->plgFieldContent86;
    }

    /**
     * Set plgFieldContent87
     *
     * @param string $plgFieldContent87
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent87($plgFieldContent87)
    {
        $this->plgFieldContent87 = $plgFieldContent87;

        return $this;
    }

    /**
     * Get plgFieldContent87
     *
     * @return string 
     */
    public function getPlgFieldContent87()
    {
        return $this->plgFieldContent87;
    }

    /**
     * Set plgFieldContent88
     *
     * @param string $plgFieldContent88
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent88($plgFieldContent88)
    {
        $this->plgFieldContent88 = $plgFieldContent88;

        return $this;
    }

    /**
     * Get plgFieldContent88
     *
     * @return string 
     */
    public function getPlgFieldContent88()
    {
        return $this->plgFieldContent88;
    }

    /**
     * Set plgFieldContent89
     *
     * @param string $plgFieldContent89
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent89($plgFieldContent89)
    {
        $this->plgFieldContent89 = $plgFieldContent89;

        return $this;
    }

    /**
     * Get plgFieldContent89
     *
     * @return string 
     */
    public function getPlgFieldContent89()
    {
        return $this->plgFieldContent89;
    }

    /**
     * Set plgFieldContent90
     *
     * @param string $plgFieldContent90
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent90($plgFieldContent90)
    {
        $this->plgFieldContent90 = $plgFieldContent90;

        return $this;
    }

    /**
     * Get plgFieldContent90
     *
     * @return string 
     */
    public function getPlgFieldContent90()
    {
        return $this->plgFieldContent90;
    }

    /**
     * Set plgFieldContent91
     *
     * @param string $plgFieldContent91
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent91($plgFieldContent91)
    {
        $this->plgFieldContent91 = $plgFieldContent91;

        return $this;
    }

    /**
     * Get plgFieldContent91
     *
     * @return string 
     */
    public function getPlgFieldContent91()
    {
        return $this->plgFieldContent91;
    }

    /**
     * Set plgFieldContent92
     *
     * @param string $plgFieldContent92
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent92($plgFieldContent92)
    {
        $this->plgFieldContent92 = $plgFieldContent92;

        return $this;
    }

    /**
     * Get plgFieldContent92
     *
     * @return string 
     */
    public function getPlgFieldContent92()
    {
        return $this->plgFieldContent92;
    }

    /**
     * Set plgFieldContent93
     *
     * @param string $plgFieldContent93
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent93($plgFieldContent93)
    {
        $this->plgFieldContent93 = $plgFieldContent93;

        return $this;
    }

    /**
     * Get plgFieldContent93
     *
     * @return string 
     */
    public function getPlgFieldContent93()
    {
        return $this->plgFieldContent93;
    }

    /**
     * Set plgFieldContent94
     *
     * @param string $plgFieldContent94
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent94($plgFieldContent94)
    {
        $this->plgFieldContent94 = $plgFieldContent94;

        return $this;
    }

    /**
     * Get plgFieldContent94
     *
     * @return string 
     */
    public function getPlgFieldContent94()
    {
        return $this->plgFieldContent94;
    }

    /**
     * Set plgFieldContent95
     *
     * @param string $plgFieldContent95
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent95($plgFieldContent95)
    {
        $this->plgFieldContent95 = $plgFieldContent95;

        return $this;
    }

    /**
     * Get plgFieldContent95
     *
     * @return string 
     */
    public function getPlgFieldContent95()
    {
        return $this->plgFieldContent95;
    }

    /**
     * Set plgFieldContent96
     *
     * @param string $plgFieldContent96
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent96($plgFieldContent96)
    {
        $this->plgFieldContent96 = $plgFieldContent96;

        return $this;
    }

    /**
     * Get plgFieldContent96
     *
     * @return string 
     */
    public function getPlgFieldContent96()
    {
        return $this->plgFieldContent96;
    }

    /**
     * Set plgFieldContent97
     *
     * @param string $plgFieldContent97
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent97($plgFieldContent97)
    {
        $this->plgFieldContent97 = $plgFieldContent97;

        return $this;
    }

    /**
     * Get plgFieldContent97
     *
     * @return string 
     */
    public function getPlgFieldContent97()
    {
        return $this->plgFieldContent97;
    }

    /**
     * Set plgFieldContent98
     *
     * @param string $plgFieldContent98
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent98($plgFieldContent98)
    {
        $this->plgFieldContent98 = $plgFieldContent98;

        return $this;
    }

    /**
     * Get plgFieldContent98
     *
     * @return string 
     */
    public function getPlgFieldContent98()
    {
        return $this->plgFieldContent98;
    }

    /**
     * Set plgFieldContent99
     *
     * @param string $plgFieldContent99
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent99($plgFieldContent99)
    {
        $this->plgFieldContent99 = $plgFieldContent99;

        return $this;
    }

    /**
     * Get plgFieldContent99
     *
     * @return string 
     */
    public function getPlgFieldContent99()
    {
        return $this->plgFieldContent99;
    }

    /**
     * Set plgFieldContent100
     *
     * @param string $plgFieldContent100
     * @return CustomFieldsContents
     */
    public function setPlgFieldContent100($plgFieldContent100)
    {
        $this->plgFieldContent100 = $plgFieldContent100;

        return $this;
    }

    /**
     * Get plgFieldContent100
     *
     * @return string 
     */
    public function getPlgFieldContent100()
    {
        return $this->plgFieldContent100;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     * @return CustomFieldsContents
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
     * @return CustomFieldsContents
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
