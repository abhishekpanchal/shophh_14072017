<?php

namespace Hhmedia\Tags\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    protected $_urlRewriteFactory;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(Action\Context $context, PostDataProcessor $dataProcessor, \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory $urlRewriteFactory)
    {
        $this->dataProcessor = $dataProcessor;
        $this->_urlRewriteFactory = $urlRewriteFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Hhmedia_Tags::save');
    }

    /**
     * Save action
     *
     * @return void
     */
    public function execute()
    { 
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $model = $this->_objectManager->create('Hhmedia\Tags\Model\Tags');

            $id = $this->getRequest()->getParam('tags_id');
            if ($id) {
                $model->load($id);
            }
            
            // save image data and remove from data array
            if (isset($data['image'])) {
                $imageData = $data['image'];
                unset($data['image']);
            } else {
                $imageData = array();
            }

            $model->addData($data);

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['tags_id' => $model->getId(), '_current' => true]);
                return;
            }

            try {
                $imageHelper = $this->_objectManager->get('Hhmedia\Tags\Helper\Data');

                if (isset($imageData['delete']) && $model->getImage()) {
                    $imageHelper->removeImage($model->getImage());
                    $model->setImage(null);
                }
                
                $imageFile = $imageHelper->uploadImage('image');
                if ($imageFile) {
                    $model->setImage($imageFile);
                }
                
                $model->save();

                $urlRewriteModel = $this->_urlRewriteFactory->create();
/* set current store id */
$urlRewriteModel->setStoreId(1);
/* this url is not created by system so set as 0 */
$urlRewriteModel->setIsSystem(0);
/* unique identifier - set random unique value to id path */
$urlRewriteModel->setIdPath(rand(1, 100000));
/* set actual url path to target path field */
$urlRewriteModel->setTargetPath("www.example.com/customModule/customController/customAction");
/* set requested path which you want to create */
$urlRewriteModel->setRequestPath("www.example.com/xyz");
/* set current store id */
$urlRewriteModel->save();

                $this->messageManager->addSuccess(__('The Data has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['tags_id' => $model->getId(), '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', ['tags_id' => $this->getRequest()->getParam('tags_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }
}
