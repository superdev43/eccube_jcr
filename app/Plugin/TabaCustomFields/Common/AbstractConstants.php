<?php
/*
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Common;

abstract class AbstractConstants
{
    /**
     * @var string プラグインコード
     */
    const PLUGIN_CODE = "TabaCustomFields";

    /**
     * @var string プラグインコード(小文字)
     */
    const PLUGIN_CODE_LC = "tabacustomfields";

    /**
     * @var string プラグインカテゴリーID
     */
    const PLUGIN_CATEGORY_ID = "taba-app";

    /**
     * @var string コンテナに登録するキー値
     */
    const CONTAINER_KEY_NAME = "spreadworks.taba";

    /**
     * @var string プラグインカテゴリー名
     */
    const PLUGIN_CATEGORY_NAME = "taba&trade; app";

    /**
     * @var string 管理画面用ルーティング接頭詞
     */
    const ADMIN_BIND_PREFIX = 'tabacustomfields_admin_';

    /**
     * @var string 管理画面用URI接頭詞
     */
    const ADMIN_URI_PREFIX = '/%eccube_admin_route%/plugin/taba-app/tabacustomfields';

    /**
     * @var string 管理画面用ルーティング接頭詞
     */
    const FRONT_BIND_PREFIX = 'tabacustomfields_';

    /**
     * @var string フロント用URI接頭詞
     */
    const FRONT_URI_PREFIX = '/plugin/tabacustomfields';

    /**
     * @var string フロント用コントローラー
     */
    const FRONT_FILE_UPLOAD_CONTROLLER = "Plugin\\TabaCustomFields\\Controller\\FrontFileUploadController";
    /**
     * @var string カスタムフィールド定義用コントローラー
     */
    const ADMIN_CUSTOM_FIELD_CONTROLLER = "Plugin\\TabaCustomFields\\Controller\\AdminCustomFieldsController";

    /**
     * @var string カスタムフィールド定義編集用コントローラー
     */
    const ADMIN_CUSTOM_FIELD_EDIT_CONTROLLER = "Plugin\\TabaCustomFields\\Controller\\AdminCustomFieldsEditController";

    /**
     * @var string カスタムフィールドコンテンツ操作コントローラー
     */
    const ADMIN_FILE_UPLOAD_CONTROLLER = "Plugin\\TabaCustomFields\\Controller\\AdminFileUploadController";

    /**
     * @var string ファイルアップロードフォームID
     */
    const FILE_UPLOAD_FORMTYPE_NAME = "custom_field_file_upload_form";

    /**
     * @var string 管理画面ページタイトル名
     */
    const PAGE_TITLE = 'taba&trade; app カスタムフィールド';

    /**
     * @var string 設定ファイル
     */
    const USER_CONFIG_FILE = "user_config.yml";

    /**
     * @var string プラグインで使用するデータを保管するディレクトリ
     */
    const PLUGIN_DATA_DIR = DIRECTORY_SEPARATOR . 'app' .  DIRECTORY_SEPARATOR . 'PluginData' . DIRECTORY_SEPARATOR . self::PLUGIN_CODE;

    /**
     * @var string プラグイン用ロガー
     */
    const LOGGER = 'monolog.logger.tabacustomfields';

    /**
     * @var string カスタムフィールド Repository のキー値
     */
    const CUSTOM_FIELDS_REPOSITOY = 'eccube.repository.tabacustomfields.custom_fields';

    /**
     * @var string カスタムフィールドコンテンツ Repository のキー値
     */
    const CUSTOM_FIELDS_CONTENTS_REPOSITOY = 'eccube.repository.tabacustomfields.custom_fields_contents';

    /**
     * @var string カスタムフィールド Entity のキー値
     */
    const CUSTOM_FIELDS_ENTITY = "Plugin\\TabaCustomFields\\Entity\\CustomFields";

    /**
     * @var string カスタムフィールドコンテンツ Entity のキー値
     */
    const CUSTOM_FIELDS_CONTENTS_ENTITY = "Plugin\\TabaCustomFields\\Entity\\CustomFieldsContents";

    /**
     * @var string 商品 カスタムフィールドコンテンツ Entity のキー値
     * CSV出力時に識別子として利用
     */
    const PRODUCT_CUSTOM_FIELDS_CONTENTS_ENTITY = "ProductCustomFieldsContents";

    /**
     * @var string 会員 カスタムフィールドコンテンツ Entity のキー値
     * CSV出力時に識別子として利用
     */
    const CUSTOMER_CUSTOM_FIELDS_CONTENTS_ENTITY = "CustomerCustomFieldsContents";

    /**
     * @var string 注文 カスタムフィールドコンテンツ Entity のキー値
     * CSV出力時に識別子として利用
     */
    const ORDER_CUSTOM_FIELDS_CONTENTS_ENTITY = "OrderCustomFieldsContents";

    /**
     * @var string フロント会員情報入力イベント のキー値
     */
    const FRONT_CUSTOMER_EVENT = 'eccube.plugin.tabacustomfields.front.customer.event';

    /**
     * @var string フロント注文情報入力イベント のキー値
     */
    const FRONT_ORDER_EVENT = 'eccube.plugin.tabacustomfields.front.order.event';

    /**
     * @var string 管理画面会員情報入力イベント のキー値
     */
    const ADMIN_CUSTOMER_EVENT = 'eccube.plugin.tabacustomfields.admin.customer.event';

    /**
     * @var string 管理画面商品情報入力イベント のキー値
     */
    const ADMIN_PRODUCT_EVENT = 'eccube.plugin.tabacustomfields.admin.product.event';

    /**
     * @var string 管理画面注文情報入力イベント のキー値
     */
    const ADMIN_ORDER_EVENT = 'eccube.plugin.tabacustomfields.admin.order.event';

    /**
     * カスタムフィールドのデフォルトカラム名
     */
    const CUSTOM_FIELD_COLUMN_NAME = 'plg_custom_field';

    /**
     * カスタムフィールドのデフォルトプロパティ名
     */
    const CUSTOM_FIELD_PROPATY_NAME = 'plgFieldContent';
    /**
     * カスタムフィールドのデフォルトセッターメソッド名
     */
    const CUSTOM_FIELD_SETTER_METHOD_NAME = 'setPlgFieldContent';

    /**
     * カスタムフィールドのデフォルトゲッターメソッド名
     */
    const CUSTOM_FIELD_GETTER_METHOD_NAME = 'getPlgFieldContent';

    /**
     * @var string フロント画面 Twig関数によって取得された一時データの変数名
     */
    const FRONT_CUSTOMFIELD_TWIG_TEMPDATA = 'eccube.plugin.tabacustomfields.front.customefields.twig.tempdata';

    /**
     * @var string 配列コンテンツの区切り文字
     */
    const CUSTOMFIELD_ARRAY_CONTENT_DELIMITER = ",";

    /**
     * 管理者権限
     */
    const CUSTOMFIELD_ACCESS_LEVEL_ADMIN = 1;

    /**
     * 会員権限
     */
    const CUSTOMFIELD_ACCESS_LEVEL_CUSTOMER = 2;

    /**
     * すべてのユーザー権限
     */
    const CUSTOMFIELD_ACCESS_LEVEL_ALL_USER = 9;

    /**
     * 商品CSVインポート用フォーマット タイプ
     */
    const CUSTOMFIELD_PRODUCT_CSV_TYPE = 999;

    /**
     * @var array ORMに追加するカスタムフィールド用のTYPE
     */
    public static $CUSTOM_FIELD_TYPE = array(
        'db_type_name'       => 'text_array',
        'doctrine_type_name'     => 'TextArrayType',
        'class_name' => '\Plugin\TabaCustomFields\Types\TextArrayType',
    );

    /**
     * @var array 追加先
     *
     * フロント画面のblockがchechboxに対応していないため、会員情報は、checkboxに対応しない。
     * セキュリティ対策後、フロント画面でのファイルアップロードを許可する
     * https://github.com/EC-CUBE/ec-cube/issues/3224
     */
    public static $TARGET_ENTITY = array (
        'Product' => array(
                'key' => "product",
                'name' => "taba_custom_fields.product",
                'available_field_types' => array(
                        'text','textarea','checkbox','select','radio','file','image',
                )
        ),
        'Order' => array(
                'key' => "order",
                'name' => "taba_custom_fields.order",
                'available_field_types' => array(
                        'text','textarea','checkbox','select','radio',
                )
        ),
        'Customer' => array(
            'key' => "customer",
            'name' => "taba_custom_fields.customer",
            'available_field_types' => array(
                    'text','textarea','checkbox','select','radio',
            )
        ),
    );

    /**
     * @var array カスタムフィールドのフォームタイプ
     */
    public static $FIELD_TYPE = array(
        'text' => array(
                'label' => "taba_custom_fields.form.text_form",
                'available_validation_rules' => array(
                'validation_regex' => true,
                'validation_is_number' => true,
                'validation_max_number' => false,
                'validation_min_number' => false,
                'validation_unique' => true,
                'validation_max_length' => true,
                'validation_min_length' => true,
            )
        ),
        'textarea' => array(
                'label' => "taba_custom_fields.form.text_area",
                'available_validation_rules' => array(
                'validation_regex' => true,
                'validation_max_length' => true,
                'validation_min_length' => true,
            )
        ),
        'checkbox' => array(
                'label' => "taba_custom_fields.form.checkbox",
                'available_validation_rules' => array(
                'validation_max_checked_number' => true,
                'validation_min_checked_number' => true,
            )
        ),
        'select' => array(
                'label' => "taba_custom_fields.form.select",
        ),
        'radio' => array(
                'label' => "taba_custom_fields.form.radio",
        ),
        'file' => array(
                'label' => "taba_custom_fields.form.file",
                'available_validation_rules' => array(
                'validation_document_file_type' => true,
                'validation_max_file_size' => true,
            )
        ),
        'image' => array(
                'label' => "taba_custom_fields.form.image",
                'available_validation_rules' => array(
                'validation_image_file_type' => true,
                'validation_max_file_size' => true,
                'validation_max_pixel_dimension_width' => true,
                'validation_min_pixel_dimension_width' => true,
                'validation_max_pixel_dimension_height' => true,
                'validation_min_pixel_dimension_height' => true,
            )
        ),
    );

    /**
     * @var array デフォルトのバリデートルール
     */
    public static $DEFAULT_AVAILABLE_VALIDATION_RULES = array(
        'validation_regex' => false,
        'validation_not_blank' => true,
        'validation_is_number' => false,
        'validation_max_number' => false,
        'validation_min_number' => false,
        'validation_unique' => false,
        'validation_max_length' => false,
        'validation_min_length' => false,
        'validation_max_checked_number' => false,
        'validation_min_checked_number' => false,
        'validation_document_file_type' => false,
        'validation_image_file_type' => false,
        'validation_max_file_size' => false,
        'validation_max_pixel_dimension_width' => false,
        'validation_min_pixel_dimension_width' => false,
        'validation_max_pixel_dimension_height' => false,
        'validation_min_pixel_dimension_height' => false,
    );
    /**
     * @var array カスタムフィールドのフォームオプション
     */
    public static $CUSTOM_FIELDS_FORM_OPTIONS = array(
        'read_allowed' => array(
            'label' => "taba_custom_fields.admin.list.display_permission",
            'choices' => array(
                self::CUSTOMFIELD_ACCESS_LEVEL_ADMIN => "taba_custom_fields.form.option.admin",
                self::CUSTOMFIELD_ACCESS_LEVEL_CUSTOMER => "taba_custom_fields.form.option.admin_member",
                self::CUSTOMFIELD_ACCESS_LEVEL_ALL_USER => "taba_custom_fields.form.option.user"
            )
        ),
        'write_allowed' => array(
            'label' => "taba_custom_fields.admin.list.edit_authority",
            'choices' => array(
                self::CUSTOMFIELD_ACCESS_LEVEL_ADMIN => "taba_custom_fields.form.option.admin",
                self::CUSTOMFIELD_ACCESS_LEVEL_CUSTOMER => "taba_custom_fields.form.option.admin_member",
                self::CUSTOMFIELD_ACCESS_LEVEL_ALL_USER => "taba_custom_fields.form.option.user"
            )
        ),
        'validation_not_blank' => array(
            'label' => "taba_custom_fields.form.validate_not_blank",
            'choices' => array(
                true => "taba_custom_fields.form.required",
                false => "taba_custom_fields.form.optional"
            ),
            'multiple' => false,
            'expanded' => true,
        ),
        'validation_is_number' => array(
            'label' => "taba_custom_fields.form.validate_number",
            'choices' => array(
                true => "taba_custom_fields.form.number_only",
                false => "taba_custom_fields.form.none_number",
            ),
            'multiple' => false,
            'expanded' => true,
        ),
        'validation_unique' => array(
            'label' => "taba_custom_fields.form.validate_unique",
            'choices' => array(
                true => "taba_custom_fields.form.unique",
                false => "taba_custom_fields.form.none_unique",
            ),
            'multiple' => false,
            'expanded' => true,
        ),
        'validation_document_file_type' => array(
            'label' => "taba_custom_fields.form.validate_file_type",
            'choices' => array(
                "text/plain" => 'taba_custom_fields.form.choice_txt',
                "application/msword" => 'taba_custom_fields.form.choice_doc',
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => 'taba_custom_fields.form.choice_docx',
                "application/vnd.ms-excel" => 'taba_custom_fields.form.choice_xls',
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => 'taba_custom_fields.form.choice_xlsx',
                "application/pdf" => 'taba_custom_fields.form.choice_pdf',
                "video/mp4" => 'taba_custom_fields.form.choice_mp4',
            ),
            'required' => false,
            'multiple' => true,
            'expanded' => true,
        ),
        'validation_image_file_type' => array(
            'label' => "taba_custom_fields.form.validate_image_type",
            'choices' => array(
                 "image/jpeg" => 'taba_custom_fields.form.choice_jpeg',
                 "image/gif" => 'taba_custom_fields.form.choice_gif',
                 "image/png" => 'taba_custom_fields.form.choice_png',
            ),
            'required' => false,
            'multiple' => true,
            'expanded' => true,
        ),
    );
}
