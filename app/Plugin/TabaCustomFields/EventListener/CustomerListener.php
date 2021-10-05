<?php
/*
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\TabaCustomFields\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;
use Plugin\TabaCustomFields\Common\Constants;

class CustomerListener extends EntityListener
{
    protected $entityKeyName = "Customer";

    /**
     * 
     * 会員情報の削除に合わせて、追加フィールドも削除する  
     * 
     * @param LifecycleEventArgs  $args
     * @param Customer  $Customer
     *
     * @ORM\preRemove
     */
    public function preRemove(Customer $Customer, LifecycleEventArgs $args) {
        $target_entity =  Constants::$TARGET_ENTITY[$this->entityKeyName]['key'];
        $target_id = $Customer->getId();
        $this->removeCustomFieldsContents($target_entity, $target_id, $args);
    }
}