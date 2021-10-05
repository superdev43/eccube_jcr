<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Util;

class Validator
{
    /**
     * 値が重複していないかチェックします。
     *
     * 記述例
     * new Assert\Callback(
     *     array(
     *         'callback' => array('Plugin\PLUGIN_CODE\Util\Validator','unique'),
     *         'payload' => array(
     *             'app' => $this->app,
     *             'entity' => Constants::$ENTITY['TYPE'],
     *             'column' => 'dataKey',
     *             'group_columns' => array('targetEntity','columnName')
     *         )
     *     )
     * ),
     *
     * payload
     *   app           : [必須] Application
     *   entity        : [必須] Entityクラス名
     *   column        : [必須] ユニークをチェックするカラム名
     *   group_columns : [任意] グループカラム名 (グループ内でのユニークチェックを行うため）
     *   error_message : [任意] エラーメッセージ
     *   error_target  : [任意] エラーを関連付けるフォーム名
     *
     * @param string $value
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function unique($value, \Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {
        if (!$value) { return; }

        $payload = $context->getConstraint()->payload; // 設定情報

        if (isset($payload['orm.em']) && isset($payload['entity']) && isset($payload['column'])) {
            // ユニークチェック
            $data = $context->getRoot()->getData();
            $qb = $payload['orm.em']->createQueryBuilder()
            ->select('T')
            ->from($payload['entity'],'T')
            ->where('T.' . $payload['column'] . ' = :value')->setParameter('value',$value);
            if (isset($payload['group_columns']) && count($payload['group_columns'])>0) {
                // グループ内でのユニークのチェック
                foreach($payload['group_columns'] as $group_column) {
                    if (!isset($data[$group_column])) { continue; }
                    $qb->andWhere('T.' . $group_column . ' = :value_' . $group_column)->setParameter('value_' . $group_column, $data[$group_column]);
                }
            }
            $query = $qb->getQuery();
            $res = $query->getResult();
            if (count($res) === 0 ) {return;}
            if (count($res) === 1) {
                // すでにデータがある場合には、自身のデータを除くために、Entityから主キーを取得し自身のデータかをチェックする
                $meta = $payload['orm.em']->getClassMetadata($payload['entity']);
                $ids = $meta->getIdentifierFieldNames();
                $is_match = true;
                if ($ids) {
                    foreach($ids as $id){
                        // 一致しないデータの場合は、自身のデータではない
                        if ($data[$id] !== $res[0][$id]) { $is_match = false; break; }
                    }
                    if ($is_match) { return; }
                }
            }
            $message = trans('taba_custom_fields.validate.error_unique');
            if (!empty($payload['error_message'])) $message = $payload['error_message'];
            $context->addViolation($message);
        }
    }

    /**
     * データキーの文字列として正しいかチェックします。
     *
     * ・「a-z , 0-9 , - , _」の文字列であること。
     * ・1文字目に - , _ は使用できません。
     *
     * @param string $value
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public static function validDataKey($value,\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {
        if (preg_match('/[^a-z0-9\-\_]+/',$value)) {
            $context->addViolation(trans('taba_custom_fields.validate.invalid_character'));
        }

        if (preg_match('/^[\-\_0-9]/',$value)) {
            $context->addViolation(trans('taba_custom_fields.validate.invalid_first_character'));
        }
    }
}
