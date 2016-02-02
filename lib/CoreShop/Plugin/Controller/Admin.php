<?php
/**
 * CoreShop
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015 Dominik Pfaffenbauer (http://dominik.pfaffenbauer.at)
 * @license    http://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Plugin\Controller;

class Admin extends \CoreShop\Controller\Action\Admin
{
    public function init()
    {
        parent::init();

        $notRestrictedActions = array();

        if (!in_array($this->getParam("action"), $notRestrictedActions)) {
            $this->checkPermission("coreshop_permission_extensions");
        }
    }
}