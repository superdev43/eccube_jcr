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

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CheckRegexFormat extends Constraint
{
    public $message = 'taba_custom_fields.validate.invalid_regex';

    public function __construct($message= "")
    {
        $this->message = trans($this->message);
        if(!empty($message['message'])){
            $this->message = $message['message'];
        }
    }
}