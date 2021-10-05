<?php

/*
 * Plugin Name: JoolenPluginTemplateManager4
 *
 * Copyright(c) joolen inc. All Rights Reserved.
 *
 * https://www.joolen.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\JoolenPluginTemplateManager4\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Plugin;
use Eccube\Util\StringUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PluginTemplateManagerController extends AbstractController
{
    /**
     * PluginTemplateManagerController constructor.
     */
    public function __construct()
    {
    }

    /**
     * @Route("/%eccube_admin_route%/joolen_plugin_template_manager/index", name="joolen_plugin_template_manager_admin_index")
     * @Template("@JoolenPluginTemplateManager4/admin/index.twig")
     */
    public function index(Request $request)
    {
        $mode = $request->get('mode');

        // MEMO: 有効なプラグインの一覧を取得
        $enabledPlugins = $this->entityManager->getRepository(Plugin::class)
            ->findAllEnabled();

        $choices = [];
        foreach ($enabledPlugins as $enabledPlugin) {
            if ($enabledPlugin->getCode() === 'JoolenPluginTemplateManager4') {
                continue;
            }
            $choices[$enabledPlugin->getCode()] = $enabledPlugin->getName().'['.$enabledPlugin->getCode().']';
        }

        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('plugins', ChoiceType::class, [
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => trans('joolenplugintemplatemanager4.admin.template_manager.plugins.placeholder'),
                'choices' => array_flip($choices),
                'data' => 0,    // MEMO: これをしないと、$form->isValid()でエラーになる
            ])
            ->add('templates', ChoiceType::class, [
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => $mode ?
                    trans('joolenplugintemplatemanager4.admin.template_manager.templates.placeholder') :
                    trans('joolenplugintemplatemanager4.admin.template_manager.templates.no_plugin.placeholder'),
                'choices' => [],
            ])
            ->add('mode', HiddenType::class, [
                'required' => false,
            ])
            ->add('twig', TextareaType::class, [
                'required' => false,
            ])
            ->add('restore_twig', TextareaType::class, [
                'required' => false,
            ])

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form_data = $event->getData();
                $mode = $form_data['mode'];
                $form = $event->getForm();

                $templates = $this->getTemplateList($form_data['plugins']);

                if ($mode === 'plugin_select') {
                    // ファイル名とエディターを初期化する
                    $form_data['templates'] = null;
                    $form_data['twig'] = null;
                    $form_data['restore_twig'] = null;
                } elseif ($mode === 'template_select') {
                    $form_data['twig'] = null;
                    $form_data['restore_twig'] = null;
                }

                // MEMO: プラグイン名が未選択の場合
                if (!$form_data['plugins']) {
                    $placeholder = trans('joolenplugintemplatemanager4.admin.template_manager.templates.no_plugin.placeholder');
                } elseif (empty($templates)) {
                    // テンプレートが見つからない場合
                    $placeholder = trans('joolenplugintemplatemanager4.admin.template_manager.templates.no_template.placeholder');
                } else {
                    $placeholder = trans('joolenplugintemplatemanager4.admin.template_manager.templates.placeholder');
                }

                // ファイル選択用のドロップダウンを追加
                $form
                    ->add('templates', ChoiceType::class, [
                        'required' => !empty($templates),
                        'expanded' => false,
                        'multiple' => false,
                        'placeholder' => $placeholder,
                        'choices' => !empty($templates) ? array_flip($templates) : [],
                    ]);

                // MEMO:
                //  テンプレートが選択されている場合 かつ 保存以外 ファイルを読み込む
                //  saveのときに読み込むと入力された値を宇垣してしまうため。
                if ($form_data['mode'] !== 'save') {
                    if ($form_data['templates']) {
                        $twig_paths = json_decode(base64_decode($form_data['templates']), true);
                        $twig = $twig_paths['twig'];
                        $restore_twig = $twig_paths['restore_twig'];

                        if (file_exists($twig) && is_writable($twig)) {
                            $form_data['twig'] = file_get_contents($twig);
                        }

                        if (file_exists($restore_twig) && is_writable($restore_twig)) {
                            $form_data['restore_twig'] = file_get_contents($restore_twig);
                        }
                    } else {
                        // テンプレートが未選択の場合はエディターをリセットする
                        $form_data['twig'] = null;
                        $form_data['restore_twig'] = null;
                    }
                }

                $event->setData($form_data);
            })
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $form->getData();

            if ($form_data['mode'] === 'save') {
                // ファイルを保存
                $fs = new Filesystem();
                $twig_paths = json_decode(base64_decode($form_data['templates']), true);
                $twigData = StringUtil::convertLineFeed($form_data['twig']);
                $fs->dumpFile($twig_paths['twig'], $twigData);

                $this->addSuccess('admin.common.save_complete', 'admin');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * テンプレートの一覧を取得する
     *
     * @param $plugin_code
     *
     * @return array
     */
    protected function getTemplateList($plugin_code)
    {
        if (!$plugin_code) {
            return [];
        }

        $src_paths = glob($this->eccubeConfig['plugin_realdir'].'/'.$plugin_code.'/Resource/template/{default,Block}', GLOB_BRACE);

        // プラグインがBlock または defaultを使用していない場合終了
        if (empty($src_paths)) {
            return [];
        }

        $drop_down_item = [];
        // コピー元のフォルダを走査
        foreach ($src_paths as $src_path) {
            $src_finder = Finder::create()
                ->files()
                ->in($src_path);

            /** @var SplFileInfo $FileInfo */
            foreach ($src_finder as $FileInfo) {
                $dest_path = $this->eccubeConfig['eccube_theme_app_dir'].'/default/'.$plugin_code;

                // コピー元のパスにBlockが含まれている場合は app/template/default/Blockを対象にする
                if (strpos($FileInfo->getPathname(), 'Block')) {
                    $dest_path = $this->eccubeConfig['eccube_theme_app_dir'].'/default/Block';
                }

                // コピー先が未使用の場合
                if (!file_exists($dest_path.'/')) {
                    $data['restore_twig'] = null;
                    // app/templateが未使用な場合はコピー元のパスを保存先に指定する
                    $data['twig'] = $FileInfo->getPathname();
                    $json = json_encode($data);
                    $base64 = base64_encode($json);
                    $projectDir = $this->getParameter('kernel.project_dir');
                    $display_path = str_replace($projectDir.'/', '', $data['twig']);
                    $drop_down_item[$base64] = $FileInfo->getFilename().'（'.$display_path.'）';

                    continue;
                }

                // コピー先を使用している場合
                $dest_finder = Finder::create()
                    ->files()
                    ->in($dest_path)
                    ->name($FileInfo->getFilename());

                foreach ($dest_finder as $file) {
                    // MEMO:
                    //  コピー元、コピー先共にパスがあり かつ コピー先にコピー元のパスが含まれない場合はスキップ
                    //  同一プラグイン内で、同一ファイル名がある場合 restore_twigのパスがずれてしまうため。
                    if ($FileInfo->getRelativePath() && $file->getRelativePath() && strpos($file->getRelativePath(), $FileInfo->getRelativePath()) === false) {
                        continue;
                    }

                    $data['restore_twig'] = $FileInfo->getPathname();
                    $data['twig'] = $file->getPathname();

                    $json = json_encode($data);
                    $base64 = base64_encode($json);
                    $projectDir = $this->getParameter('kernel.project_dir');
                    $display_path = str_replace($projectDir.'/', '', $data['twig']);
                    $drop_down_item[$base64] = $FileInfo->getFilename().'（'.$display_path.'）';
                }
            }
        }

        return $drop_down_item;
    }
}