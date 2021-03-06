<?php
/**
 * CoreShop.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Controller\Plugin;

/**
 * Class TemplateRouter
 * @package CoreShop\Controller\Plugin
 */
class TemplateRouter extends \Zend_Controller_Plugin_Abstract
{
    /**
     * Checks if Controller is available in Template and use it.
     *
     * @param \Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(\Zend_Controller_Request_Abstract $request)
    {
        $coreShopRequest = clone $request;
        if ($request->getModuleName() === 'CoreShop') {
            $frontController = \Zend_Controller_Front::getInstance();
            $coreShopRequest->setModuleName(PIMCORE_FRONTEND_MODULE);

            if ($frontController->getDispatcher()->isDispatchable($coreShopRequest)) {
                $request->setModuleName(PIMCORE_FRONTEND_MODULE);
            }
        }
    }
}
