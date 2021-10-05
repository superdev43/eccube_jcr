<?php
/*
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\TabaCustomFields\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;

class EntityListener
{
    protected $entityKeyName;

    /**
     * 
     * Entityの削除に合わせて、追加フィールドも削除する
     * 
     * @param $target_entity
     * @param $target_id
     * @param LifecycleEventArgs  $args
     *
     */
    public function removeCustomFieldsContents($target_entity, $target_id, $args) {
        $em = $args->getEntityManager();
        $customFieldsContentsRepository = $em->getRepository('Plugin\TabaCustomFields\Entity\CustomFieldsContents');


        // 追加したカスタムフィールドを削除
        if ($customFieldsContents = $customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id)) {
            $em->remove($customFieldsContents);
        }
    }
}