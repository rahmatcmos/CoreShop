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

namespace CoreShop\IndexService\Condition;

use CoreShop\IndexService\Condition as IndexCondition;
use Pimcore\Db;

/**
 * Class Mysql
 * @package CoreShop\IndexService\Condition
 */
class Mysql extends AbstractRenderer
{
    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * Condition constructor.
     */
    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * @param IndexCondition $condition
     * @return string
     */
    protected function renderIn(IndexCondition $condition)
    {
        $inValues = [];

        foreach ($condition->getValues() as $c => $value) {
            $inValues[] = $this->db->quote($value);
        }

        return 'TRIM(`'.$condition->getFieldName().'`) IN ('.implode(',', $inValues).')';
    }

    /**
     * @param IndexCondition $condition
     * @return string
     */
    protected function renderLike(IndexCondition $condition)
    {
        $values = $condition->getValues();
        $pattern = $values["pattern"];

        $value = $values["value"];
        $patternValue = '';

        switch ($pattern) {
            case "left":
                $patternValue = '%' . $value;
                break;
            case "right":
                $patternValue = $value . '%';
                break;
            case "both":
                $patternValue = '%' . $value . '%';
                break;
        }

        return 'TRIM(`'.$condition->getFieldName().'`) LIKE ' . $this->db->quote($patternValue);
    }

    /**
     * @param IndexCondition $condition
     * @return string
     */
    protected function renderRange(IndexCondition $condition)
    {
        $values = $condition->getValues();

        return 'TRIM(`'.$condition->getFieldName().'`) >= '.$values['from'].' AND TRIM(`'.$condition->getFieldName().'`) <= '.$values['to'];
    }

    /**
     * @param IndexCondition $condition
     * @return string
     */
    protected function renderConcat(IndexCondition $condition)
    {
        $values = $condition->getValues();
        $conditions = [];

        foreach ($values["conditions"] as $cond) {
            $conditions[] = $this->render($cond);
        }

        return "(" . implode(" " . trim($values['operator']) . " ", $conditions) . ")";
    }

    /**
     * @param IndexCondition $condition
     * @return string
     */
    protected function renderCompare(IndexCondition $condition)
    {
        $values = $condition->getValues();
        $value = $values['value'];
        $operator = $values['operator'];

        return 'TRIM(`'.$condition->getFieldName().'`) '.$operator.' '.$this->db->quote($value);
    }
}
