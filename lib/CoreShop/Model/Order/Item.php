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

namespace CoreShop\Model\Order;

use CoreShop\Model\Base;
use CoreShop\Model\Order;
use Pimcore\Model\Object\CoreShopOrderItem;

/**
 * Class Item
 * @package CoreShop\Model\Order
 */
class Item extends Base
{
    /**
     * Pimcore Object Class
     *
     * @var string
     */
    public static $pimcoreClass = "Pimcore\\Model\\Object\\CoreShopOrderItem";

    /**
     * Calculate Total of OrderItem without tax
     *
     * @return float
     */
    public function getTotalWithoutTax()
    {
        return $this->getAmount() * $this->getRetailPrice();
    }
    
    /**
     * Get Order for OrderItem
     *
     * @return null|\Pimcore\Model\Object\AbstractObject
     */
    public function getOrder()
    {
        $parent = $this->getParent();

        do {
            if ($parent instanceof Order) {
                return $parent;
            }

            $parent = $parent->getParent();
        } while ($parent != null);

        return null;
    }
}