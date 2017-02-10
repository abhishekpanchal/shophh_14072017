<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Controller\Adminhtml\Slide;

use Magento\MediaStorage\Model\File\UploaderFactory;
use Altima\Lookbookslider\Model\Slide\Image;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Altima\Lookbookslider\Controller\Adminhtml\Slide {

    protected function _beforeSave($model, $request) {
		
    	$bgImage = $this->getRequest()->getFiles('bg_image');
        $fileName = ($bgImage && array_key_exists('name', $bgImage)) ? $bgImage['name'] : null;
        if ($bgImage && $fileName) {
            try {
                /** @var \Magento\Framework\ObjectManagerInterface $uploader */
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'bg_image']
                );

                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

                /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapterFactory */
                $imageAdapterFactory = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')
                    ->create();

                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);

                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                    ->getDirectoryRead(DirectoryList::MEDIA);
                 
                $result = $uploader->save(
                    $mediaDirectory
                        ->getAbsolutePath('Altima/Lookbookslider/Slide/image')
                );
                $model->setBgImage($result['file']);
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

    }
}