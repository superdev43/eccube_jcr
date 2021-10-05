<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Validator\Constraint;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CheckRegexFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // 正規表現文字列チェック
        if ($value) {
            try {
                preg_match($value, 'あいうえおかきくけこ', $array_result);
            } catch (\Symfony\Component\Debug\Exception\ContextErrorException $e) {
                // エラーの登録
                $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
            }
        }
    }
}