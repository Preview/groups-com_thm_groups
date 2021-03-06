<?php
/**
 * @category    Joomla component
 * @package     THM_Groups
 * @subpackage  com_thm_groups.admin
 * @name        THM_GroupsHelperGroup_Manager
 * @author      Ilja Michajlow, <ilja.michajlow@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Class providing helper functions for group manager
 *
 * @category  Joomla.Component.Admin
 * @package   thm_groups
 */
class THM_GroupsHelperDatabase_Compare
{
    /**
     * Filters insert values before save
     * Compare two arrays and delete repeating elements
     * This algorithm sucks -> comment form Ilja
     *
     * @param   Array  &$insertValues  An array with values to save
     * @param   Array  $valuesFromDB   An array with values from DB
     *
     * @return  void
     */
    public static function filterInsertValues(&$insertValues, $valuesFromDB)
    {
        foreach ($valuesFromDB as $key => $value)
        {
            if (array_key_exists($key, $insertValues))
            {
                foreach ($value as $data)
                {
                    $idx = array_search($data, $insertValues[$key]);
                    if (!is_bool($idx))
                    {
                        unset($insertValues[$key][$idx]);
                    }
                }
            }
        }
    }

}
