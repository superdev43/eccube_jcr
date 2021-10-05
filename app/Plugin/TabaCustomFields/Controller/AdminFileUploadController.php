<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_URI_PREFIX, name=Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_BIND_PREFIX)
 */
class AdminFileUploadController extends AbstractFileUploadController
{
    /**
     * ファイルアップロード
     * @Route("/file_upload", name="file_upload")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function upload(Request $request)
    {
        return $this->fileUpload($request);
    }
}