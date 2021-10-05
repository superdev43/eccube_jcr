<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Twig\Extension;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Eccube\Service\OrderHelper;
use Eccube\Repository\OrderRepository;
use Eccube\Request\Context;
use Plugin\TabaCustomFields\Common\Constants;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    private $cached;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var Context
     */
    protected $requestContext;

    /**
     * @var CustomFieldsContentsRepository
     */
    protected $customFieldsContentsRepository;

    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    /**
     * @var Packages
     */
    protected $assetPackage;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;


    /**
     * TwigExtension constructor.
     *
     * @param \Twig_Environment $twig
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Context $requestContext
     * @param CustomFieldsContentsRepository $customFieldsContentsRepository
     * @param CustomFieldsRepository $customFieldsRepository
     * @param Packages $assetPackages
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        \Twig_Environment $twig,
        AuthorizationCheckerInterface $authorizationChecker,
        Context $requestContext,
        CustomFieldsContentsRepository $customFieldsContentsRepository,
        CustomFieldsRepository $customFieldsRepository,
        Packages $assetPackages,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        OrderRepository $orderRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->requestContext = $requestContext;
        $this->customFieldsContentsRepository = $customFieldsContentsRepository;
        $this->customFieldsRepository = $customFieldsRepository;
        $this->assetPackage = $assetPackages;
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->orderRepository = $orderRepository;
    }
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(Constants::PLUGIN_CODE.'Customer', array($this, 'getCustomer')),
            new \Twig_SimpleFunction(Constants::PLUGIN_CODE.'Product', array($this, 'getProduct')),
            new \Twig_SimpleFunction(Constants::PLUGIN_CODE.'Order', array($this, 'getOrder')),
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return Constants::PLUGIN_CODE_LC;
    }

    /**
     * 会員カスタムフィールドの内容を返す。
     *
     * @param string  $data_key
     * @param integer|null  $target_id
     * @param array|null  $option
     * @return string|array|boolean $content
     */
    public function getCustomer($data_key, $target_id = null, $option = null)
    {
        $target_entity = "customer";

        // データキーから格納されているカラムIDを取得
        $getterMethod = $this->getGetterMethod($target_entity, $data_key);

        // データ取得
        if ($this->requestContext->isAdmin()) {
            // 管理画面
            //  指定のIDの情報
            if ($this->authorizationChecker->isGranted('ROLE_ADMIN') && $target_id) {
                $customFieldsContents = $this->getCacheCustomFieldsContents($target_entity, $target_id);
            }
        } else {
            // フロント画面
            //  ログインユーザーの情報
            if ($this->authorizationChecker->isGranted('ROLE_USER') && $this->getUser()) {
                $customFieldsContents = $this->getCacheCustomFieldsContents($target_entity, $this->getUser()->getId());
            }
        }
        if (!isset($customFieldsContents) || !$customFieldsContents) {
            // 新規(空)
            $customFieldsContents = $this->customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);
        }

        if ($customFieldsContents
            && $getterMethod
            && method_exists($customFieldsContents, $getterMethod)
            && $customFieldsContents->$getterMethod()
            ) {
            $content = $this->getContent($customFieldsContents->$getterMethod(), $target_entity, $data_key, $option);
            return $content;
        }
        return false;
    }

    /**
     * 注文カスタムフィールドの内容を返す。
     *
     * @param string  $data_key
     * @param integer|null  $target_id
     * @param array|null  $option
     * @return string|array|boolean $content
     */
    public function getOrder($data_key, $target_id = null, $option = null)
    {
        $target_entity = "order";

        // データキーから格納されているカラムIDを取得
        $getterMethod = $this->getGetterMethod($target_entity, $data_key);

        // 権限による制御
        if ($this->requestContext->isAdmin()) {
            // 管理画面
            if ($this->authorizationChecker->isGranted('ROLE_ADMIN') && $target_id) {
                // 管理者 指定のIDの情報
                $customFieldsContents = $this->getCacheCustomFieldsContents($target_entity, $target_id);
            }
        } else {
            // フロント画面
            if ($this->authorizationChecker->isGranted('ROLE_USER') ) {
                // 会員
                // ログインしている場合は、自身の購入履歴を参照
                $Order = $this->orderRepository->findOneBy(
                    [
                        'order_no' => $target_id,
                        'Customer' => $this->getUser(),
                    ]
                );
                if ($Order) {
                    $customFieldsContents = $this->getCacheCustomFieldsContents($target_entity, $target_id);
                }
            } else {
                // ゲスト
                // カートに入っている注文情報のみ対象
                // カートの注文IDと引数を比較
                $orderId = $this->session->get(OrderHelper::SESSION_ORDER_ID);
                if ($target_id === $orderId){
                    $customFieldsContents = $this->getCacheCustomFieldsContents($target_entity, $target_id);
                }
            }
        }
        if (!isset($customFieldsContents) || !$customFieldsContents) {
            // 新規(空)
            $customFieldsContents = $this->customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);
        }

        if ($customFieldsContents
            && $getterMethod
            && method_exists($customFieldsContents, $getterMethod)
            && $customFieldsContents->$getterMethod()
            ) {
            $content = $this->getContent($customFieldsContents->$getterMethod(), $target_entity, $data_key, $option);
            return $content;
        }
        return false;
    }

    /**
     * 商品カスタムフィールドの内容を返す。
     *
     * @param string  $data_key
     * @param integer  $target_id
     * @param array|null  $option
     * @return string|array|boolean $content
     */
    public function getProduct($data_key, $target_id, $option = null)
    {
        $target_entity = "product";
        return $this->getCustomFieldsContent($target_entity, $data_key, $target_id, $option);
    }

    /**
     * カスタムフィールドのコンテンツを返す。
     *
     * @param string $target_entity
     * @param string  $data_key
     * @param integer  $target_id
     * @param array|null  $option
     * @return string|array|boolean $content
     */
    public function getCustomFieldsContent($target_entity, $data_key, $target_id, $option = null) {

        // データキーから格納されているカラムIDを取得
        $getterMethod = $this->getGetterMethod($target_entity, $data_key);

        // データ取得
        $customFieldsContents = $this->getCacheCustomFieldsContents($target_entity, $target_id);
        if (!isset($customFieldsContents) || !$customFieldsContents) {
            // 新規(空)
            $customFieldsContents = $this->customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);
        }

        if ($customFieldsContents
            && $getterMethod
            && method_exists($customFieldsContents, $getterMethod)
            && $customFieldsContents->$getterMethod()
            ) {
            $content = $this->getContent($customFieldsContents->$getterMethod(), $target_entity, $data_key, $option);
            return $content;
        }
        return false;
    }

    /**
     * オプションの指定に応じたデータを返す
     *
     * @param string|array  $default_content
     * @param string  $data_key
     * @param integer  $target_id
     * @param array|null  $option
     * @return string|array|boolean $content
     */
    private function getContent($content, $target_entity, $data_key, $option) {
        // 一時キャッシュ経由でカスタムフィールドを取得
        $customField = $this->getCacheCustomField($target_entity, $data_key);
        if (!$customField) {return false;}

        // ファイル、画像の場合は、フルパスで返す
        if ($customField->getFieldType() === "file" || $customField->getFieldType() === "image") {
            $content = $this->assetPackage->getPackage('save_image')->getUrl($content);
        }

        // 定義にある選択肢順にソート
        if (is_array($content)) {
            if ( $customField->getFormOption() ){
                $string = str_replace( array( " ", "　", "	", "\"", ";"), "", $customField->getFormOption());
                $lines = explode("\r\n", $string);
                if (count($lines)>0) {
                    $tmp_content = array();
                    foreach ($lines as $line) {
                        if (in_array($line, $content)){
                            $tmp_content[] = $line;
                        }
                    }
                    $content = $tmp_content;
                }
            }
        }

        // optionの指定がない場合は、Stringの状態で返す
        //  配列はカンマ区切りで返す。
        if (!$option) {
            return $content = (is_array($content))? join( Constants::CUSTOMFIELD_ARRAY_CONTENT_DELIMITER,$content): $content;
        }

        // それぞれのフォーマットで値を返す
        if (isset($option['format'])) {
            switch($option['format']) {
                case 'text':
                    $delimiter = isset($option['delimiter'])? $option['delimiter']: Constants::CUSTOMFIELD_ARRAY_CONTENT_DELIMITER;
                    return $content = (is_array($content))? join( $delimiter, $content): $content;
                case 'array':
                    return $content = (is_array($content))? $content: array($content);
                case 'object':
                    return (object) $obj = [
                        'value' => $content,
                        'label' => $customField->getLabel()
                    ];
            }
        }
        return false;
    }

    /**
     * データキーからcustomFieldsContentsのゲッターのメソッド名を取得
     *
     * @param string $target_entity
     * @param string  $data_key
     * @return string|boolean $getterMethod
     */
    private function getGetterMethod($target_entity, $data_key) {
        // 一時キャッシュ経由でカスタムフィールドを取得
        $customField = $this->getCacheCustomField($target_entity, $data_key);

        // ゲッターを定義する
        if ($customField) {
            return $getterMethod = Constants::CUSTOM_FIELD_GETTER_METHOD_NAME.$customField->getColumnId();
        }
        return false;
    }

    /**
     * カスタムフィールドの定義を一時データ経由で返します
     * また定義内設定されている権限の照会も行います。
     *
     * @param string $target_entity
     * @param string  $data_key
     * @return array CustomFieldsRepository
     */
    private function getCacheCustomField($target_entity, $data_key) {

        // 権限IDとキャッシュ名を定義
        $read_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_ALL_USER;
        $cache_name = Constants::FRONT_CUSTOMFIELD_TWIG_TEMPDATA.'.all.'.$target_entity.'.custom_fields';
        if ($this->requestContext->isAdmin()) {
            // 管理画面
            //  指定のIDの情報
            if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                $read_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_ADMIN;
                $cache_name = Constants::FRONT_CUSTOMFIELD_TWIG_TEMPDATA.'.admin.'.$target_entity.'.custom_fields';
            }
        } else {
            // フロント画面
            //  ログインユーザーの情報
            if ($this->authorizationChecker->isGranted('ROLE_USER')) {
                $read_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_CUSTOMER;
                $cache_name = Constants::FRONT_CUSTOMFIELD_TWIG_TEMPDATA.'.customer.'.$target_entity.'.custom_fields';
            } else {
                //
                // ゲスト購入時の注文データ参照のため、orderの場合は、ログインユーザと同じ定義とする
                // データの取得の権限管理は、getOrder内で実装しています。
                //
                if ( $target_entity === "order" ) {
                    $read_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_CUSTOMER;
                    $cache_name = Constants::FRONT_CUSTOMFIELD_TWIG_TEMPDATA.'.guest.'.$target_entity.'.custom_fields';
                }
            }
        }

        $customFields = $this->customFieldsRepository->getReadCustomFields($target_entity, $read_allowed_id);
        // 取得できなかった場合でも、結果を保存します
        $this->cached[$cache_name] = $customFields;

        // データキーから該当の定義オブジェクトを返す
        if ($customFields = $this->cached[$cache_name]){
            if (count($customFields)>0) {
                foreach($customFields as $customField) {
                    if ($customField->getDataKey() === $data_key ){
                        return $customField;
                    }
                }
            }
        }
        return false;
    }

    /**
     * カスタムフィールドのコンテンツを一時データ経由で返します
     *
     * @param $target_entity
     * @param $target_id
     * @return CustomFieldsContentsRepository
     */
    private function getCacheCustomFieldsContents($target_entity, $target_id) {
        if (!$target_entity && !$target_id) { return $this->customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);}

        $cache_name = Constants::FRONT_CUSTOMFIELD_TWIG_TEMPDATA.'.'.$target_entity.'_'.$target_id.'.custom_fields_content';
        if (isset($this->cached[$cache_name])) {
            return $this->cached[$cache_name];
        } else {
            $customFieldsContent = $this->customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);

            // 取得できなかった場合でも、結果を保存します
            $this->cached[$cache_name] = $customFieldsContent;
            return $this->cached[$cache_name];
        }
    }

    /**
     * Get current logged user
     *
     * @return \Eccube\Entity\Customer|null
     */
    protected function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
