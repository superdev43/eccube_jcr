<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

/**
 * カスタムフィールドで保存するType
 */
class TextArrayType extends TextType
{

    const TEXT_ARRAY = 'text_array'; // modify to match your type name

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * 
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_array($value)) {
            return serialize($value);
        } else {
            return $value;
        }
    }

    /**
     * シリアライズされているかを確認し、TEXTとARRAYに分けて返却する
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        if ($value === null) {
            return null;
        }

        $val = @unserialize($value);
        if ($val === false && $value != 'b:0;') {
            // TEXT
            return $value;
        }
        // ARRAY
        return $val;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::TEXT_ARRAY;
    }
}
