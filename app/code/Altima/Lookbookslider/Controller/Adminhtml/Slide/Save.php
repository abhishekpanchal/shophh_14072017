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
		$data = $this->getRequest()->getPostValue();

    	$bgImage = $this->getRequest()->getFiles('bg_image');
        $fileName = ($bgImage && array_key_exists('name', $bgImage)) ? $bgImage['name'] : null;

        $imageOne = $this->getRequest()->getFiles('image_one');
        $fileOne = ($imageOne && array_key_exists('name', $imageOne)) ? $imageOne['name'] : null;

        $imageTwo = $this->getRequest()->getFiles('image_two');
        $fileTwo = ($imageTwo && array_key_exists('name', $imageTwo)) ? $imageTwo['name'] : null;

        $imageThree = $this->getRequest()->getFiles('image_three');
        $fileThree = ($imageThree && array_key_exists('name', $imageThree)) ? $imageThree['name'] : null;

        $imageFour = $this->getRequest()->getFiles('image_four');
        $fileFour = ($imageFour && array_key_exists('name', $imageFour)) ? $imageFour['name'] : null;

        $color = $this->getRequest()->getParam('color');
        $model->setColor($color);
        //Section 1 Data
        $model->setTitleOne($data['title_one']);
        $model->setLinkOne($data['link_one']);
        $model->setDescriptionOne($data['description_one']);
        //Section 2 Data
        $model->setTitleTwo($data['title_two']);
        $model->setLinkTwo($data['link_two']);
        $model->setDescriptionTwo($data['description_two']);
        //Section 3 Data
        $model->setTitleThree($data['title_three']);
        $model->setLinkThree($data['link_three']);
        $model->setDescriptionThree($data['description_three']);
        //Section 4 Data
        $model->setTitleFour($data['title_four']);
        $model->setLinkFour($data['link_four']);
        $model->setDescriptionFour($data['description_four']);
        //products
        $model->setSkuOne($data['sku_one']);
        $model->setSkuTwo($data['sku_two']);
        $model->setSkuThree($data['sku_three']);
        $model->setSkuFour($data['sku_four']);
        $model->setCollectionLink($data['collection_link']);
        $model->setCollectionTitle($data['collection_title']);
        
        // For Background Image
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

        // For Section 1 Image
        if ($imageOne && $fileOne) {
            try {
                /** @var \Magento\Framework\ObjectManagerInterface $uploader */
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'image_one']
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
                $model->setImageOne($result['file']);
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        // For Section 2 Image
        if ($imageTwo && $fileTwo) {
            try {
                /** @var \Magento\Framework\ObjectManagerInterface $uploader */
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'image_two']
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
                $model->setImageTwo($result['file']);
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        // For Section 3 Image
        if ($imageThree && $fileThree) {
            try {
                /** @var \Magento\Framework\ObjectManagerInterface $uploader */
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'image_three']
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
                $model->setImageThree($result['file']);
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        // For Section 4 Image
        if ($imageFour && $fileFour) {
            try {
                /** @var \Magento\Framework\ObjectManagerInterface $uploader */
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'image_four']
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
                $model->setImageFour($result['file']);
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

    }
}