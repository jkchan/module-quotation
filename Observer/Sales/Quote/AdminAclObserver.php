<?php
namespace Sales\Quote\Observer\Sales\Quote;

use Magento\Backend\Model\Auth;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminAclObserver implements ObserverInterface
{
    /**
     * @param AuthorizationInterface $authorization
     * @param Auth $auth
     * @param ActionFlag $actionFlag
     * @param ViewInterface $view
     * @param array $fullActionNameToResource
     */
    public function __construct(
        protected AuthorizationInterface $authorization,
        protected Auth $auth,
        protected ActionFlag $actionFlag,
        protected ViewInterface $view,
        protected array $fullActionNameToResource = []
    ) {
        $this->fullActionNameToResource = $fullActionNameToResource;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getData('request');
        if (!$this->isAllow($request)) {
            /** @var \Magento\Backend\App\Action $controllerAction */
            $controllerAction = $observer->getEvent()->getData('controller_action');
            $controllerAction->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            $this->view->loadLayout(['default', 'adminhtml_denied'], true, true, false);
            $this->view->renderLayout();
            $request->setDispatched(true);
        }
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    private function isAllow($request)
    {
        $fullActionName = strtolower($request->getFullActionName() ?: "");
        if (!$fullActionName) {
            return true;
        }
        $resource = $this->fullActionNameToResource[$fullActionName] ?? null;
        if (!$resource) {
            return true;
        }
        if ($this->authorization->isAllowed($resource)) {
            return true;
        }
        if (!$this->auth->isLoggedIn()) {
            return true;
        }
        return false;
    }
}
