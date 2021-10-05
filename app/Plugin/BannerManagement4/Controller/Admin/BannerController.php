<?php

/*
 * This file is part of BannerManagement4
 *
 * Copyright(c) U-Mebius Inc. All Rights Reserved.
 *
 * https://umebius.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BannerManagement4\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\BannerManagement4\Form\Type\Admin\BannerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * バナーのコントローラクラス
 */
class BannerController extends AbstractController
{
    /**
     * バナー一覧を表示する。
     *
     * @Route("/%eccube_admin_route%/content/banner", name="admin_content_banner")
     * @Template("@BannerManagement4/admin/banner.twig")
     */
    public function index(Application $app, Request $request)
    {
        $BannerFields = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\BannerField')
	        ->createQueryBuilder('dt')
	        ->orderBy('dt.sort_no', 'ASC')
	        ->getQuery()
	        ->getResult()
        ;

        if ($request->get('field')) {
	        $BannerField = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\BannerField')
		        ->find($request->get('field'));
        } else {
	        $BannerField = $BannerFields[0];
        }

	    $BannerList = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->getBanners($BannerField);

        $builder = $this->formFactory->createBuilder();

        $builder->add('BannerField', EntityType::class, array(
            'class' => 'Plugin\BannerManagement4\Entity\BannerField',
            'property_path' => 'name',
            'placeholder' => null,
            'required' => false,
	        'data' => $BannerField,
	        'choices' => $BannerFields,
        ));

        $form = $builder->getForm();

        return array(
            'form' => $form->createView(),
            'BannerList' => $BannerList,
            'BannerField' => $BannerField,
        );
    }

    /**
     * バナーを登録・編集する。
     *
     * @Route("/%eccube_admin_route%/banner/new", name="admin_content_banner_new")
     * @Route("/%eccube_admin_route%/banner/{id}/edit",
     *     name="admin_content_banner_edit",
     *     requirements={"id" = "\d+"}
     *     )
     * @Template("@BannerManagement4/admin/banner_edit.twig")
     * @throws NotFoundHttpException
     */
    public function edit(Application $app, Request $request, $id = null)
    {
        if ($id) {
            $Banner = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->find($id);
            if (!$Banner) {
                throw new NotFoundHttpException();
            }
        } else {
            $Banner = new \Plugin\BannerManagement4\Entity\Banner();
        }


	    if ($request->get('field')) {
		    $BannerField = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\BannerField')
			    ->find($request->get('field'));
		    $Banner->setField($BannerField);
	    }

        $builder = $this->formFactory
            ->createBuilder(BannerType::class, $Banner);

        $form = $builder->getForm();


        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $dir_path = $this->eccubeConfig['eccube_save_image_dir'].'/banner';
                if (!is_dir($dir_path)) {
                    mkdir($dir_path, 0775);
                }

                $file = $Banner->getFile();

                if ($file instanceof UploadedFile) {
                    $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                    $file->move(
                        $dir_path,
                        $fileName
                    );

                    $Banner->setFileName($fileName);
                }



                $data = $form->getData();
                if (empty($data['url'])) {
                    $Banner->setLinkMethod(Constant::DISABLED);
                }

                $status = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->save($Banner);

                if ($status) {
                    $this->addSuccess('admin.banner.save.complete', 'admin');

                    return $this->redirectToRoute('admin_content_banner', array('field' => $Banner->getField()->getId()));
                }
                $this->addError('admin.common.save_error', 'admin');
            }
        }

        return array(
            'form' => $form->createView(),
            'Banner' => $Banner,
        );
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    /**
     * 指定したバナーの表示順を1つ上げる。
     *
     * @Route("/%eccube_admin_route%/banner/{id}/up",
     *     name="admin_content_banner_up",
     *     requirements={"id" = "\d+"},
     *     methods={"PUT"}
     *     )
     *  @return RedirectResponse
     */
    public function up(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetBanner = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->find($id);
        if (!$TargetBanner) {
            throw new NotFoundHttpException();
        }

        $status = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->up($TargetBanner);

        if ($status) {
            $this->addSuccess('admin.common.save_complete', 'admin');
        } else {
            $this->addError('admin.common.save_error', 'admin');
        }

        return $this->redirectToRoute('admin_content_banner', array('field' => $TargetBanner->getField()->getId()));
    }

    /**
     * 指定したバナーの表示順を1つ下げる。
     *
     * @Route("/%eccube_admin_route%/banner/{id}/down",
     *     name="admin_content_banner_down",
     *     requirements={"id" = "\d+"},
     *     methods={"PUT"}
     *     )
     *  @return RedirectResponse
     */
    public function down(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetBanner = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->find($id);
        if (!$TargetBanner) {
            throw new NotFoundHttpException();
        }

        $status = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->down($TargetBanner);

        if ($status) {
            $this->addSuccess('admin.common.save_complete', 'admin');
        } else {
            $this->addError('admin.common.save_error', 'admin');
        }

	    return $this->redirectToRoute('admin_content_banner', array('field' => $TargetBanner->getField()->getId()));
    }


    /**
     * 指定したバナーの表示順を最初にする。
     * @Route("/%eccube_admin_route%/banner/{id}/top",
     *     name="admin_content_banner_top",
     *     requirements={"id" = "\d+"},
     *     methods={"PUT"}
     *     )
     *  @return RedirectResponse
     */
    public function top(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetBanner = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->find($id);
        if (!$TargetBanner) {
            throw new NotFoundHttpException();
        }

        $status = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->top($TargetBanner);

        if ($status) {
            $this->addSuccess('admin.common.save_complete', 'admin');
        } else {
            $this->addError('admin.common.save_error', 'admin');
        }

        return $this->redirectToRoute('admin_content_banner', array('field' => $TargetBanner->getField()->getId()));
    }



    /**
     * 指定したバナーの表示順を最後にする。
     * @Route("/%eccube_admin_route%/banner/{id}/last",
     *     name="admin_content_banner_last",
     *     requirements={"id" = "\d+"},
     *     methods={"PUT"}
     *     )
     *  @return RedirectResponse
     */
    public function last(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetBanner = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->find($id);
        if (!$TargetBanner) {
            throw new NotFoundHttpException();
        }

        $status = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->last($TargetBanner);

        if ($status) {
            $this->addSuccess('admin.common.save_complete', 'admin');
        } else {
            $this->addError('admin.common.save_error', 'admin');
        }

        return $this->redirectToRoute('admin_content_banner', array('field' => $TargetBanner->getField()->getId()));
    }


    /**
     * 指定したバナーを削除する。
     *
     * @Route("/%eccube_admin_route%/banner/{id}/delete",
     *     name="admin_content_banner_delete",
     *     requirements={"id" = "\d+"},
     *     methods={"DELETE"}
     *     )
     *
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetBanner = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->find($id);
        if (!$TargetBanner) {
            throw new NotFoundHttpException();
        }

        $status = $this->entityManager->getRepository('Plugin\BannerManagement4\Entity\Banner')->delete($TargetBanner);

        if ($status) {
            $this->addSuccess('admin.banner.delete.complete', 'admin');
        } else {
            $this->addSuccess('admin.banner.delete.error', 'admin');
        }

	    return $this->redirectToRoute('admin_content_banner', array('field' => $TargetBanner->getField()->getId()));
    }
}
