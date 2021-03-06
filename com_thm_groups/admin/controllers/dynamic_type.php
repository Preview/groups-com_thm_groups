<?php
/**
 * @version     v1.0.0
 * @category    Joomla component
 * @package     THM_Groups
 * @subpackage  com_thm_groups.admin
 * @name        THMGroupsControllerDynamic_Type
 * @description THMGroupsControllerDynamic_Type class from com_thm_groups
 * @author      Ilja Michajlow, <ilja.michajlow@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');


/**
 * THMGroupsControllerDynamic_Type_Manager class for component com_thm_groups
 *
 * @category  Joomla.Component.Admin
 * @package   com_thm_groups.admin
 * @link      www.mni.thm.de
 * @since     Class available since Release 3.5
 */
class THM_GroupsControllerDynamic_Type extends JControllerLegacy
{
    /**
     * constructor (registers additional tasks to methods)
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Redirects to the dynamic_type_edit view for the creation of new element
     *
     * @return object
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $input = JFactory::getApplication()->input;
        $input->set('view', 'dynamic_type_edit');
        $input->set('id', '0');
        parent::display();
    }

    /**
     * Apply - Save button
     *
     * @return void
     */
    public function apply()
    {
        $model = $this->getModel('dynamic_type');

        $success = $model->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_GROUPS_DATA_SAVED');
            $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_edit&cid[]=' . $success, $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_GROUPS_SAVE_ERROR');
            $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_edit&cid[]=0', $msg);
        }
    }

    /**
     * Redirects to the category manager view without making any persistent changes
     *
     * @param   Integer  $key  contains the key
     *
     * @return  void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function cancel($key = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_manager');
    }

    /**
     * Deletes the selected category and redirects to the category manager
     *
     * @return void
     */
    public function delete()
    {
        $model = $this->getModel('dynamic_type');

        if ($model->delete())
        {
            $msg = JText::_('COM_THM_GROUPS_DATA_DELETED');
        }
        else
        {
            $msg = JText::_('COM_THM_GROUPS_SAVE_DELETED');
        }
        $this->setRedirect("index.php?option=com_thm_groups&view=dynamic_type_manager", $msg);
    }

    /**
     * Redirects to the category_edit view for the editing of existing categories
     *
     * @return void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $this->input->set('view', 'dynamic_type_edit');
        $this->input->set('hidemainmenu', 1);
        parent::display();
    }

    /**
     * Save&Close button
     *
     * @param   Integer  $key     contain key
     * @param   String   $urlVar  contain url
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save($key = null, $urlVar = null)
    {
        $model = $this->getModel('dynamic_type');
        //$isValid = $model->validateForm();
        $isValid = true;

        if ($isValid)
        {
            $success = $model->save();
            if ($success)
            {
                $msg = JText::_('COM_THM_GROUPS_DATA_SAVED');
                $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_manager', $msg);
            }
            else
            {
                $msg = JText::_('COM_THM_GROUPS_SAVE_ERROR');
                $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_manager' . $success, $msg);
            }
        }
        else
        {
            $msg = JText::_('COM_THM_GROUPS_VALIDATION_ERROR');
            $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_manager', $msg, 'warning');
        }
    }

    /**
     * Save2new
     *
     * @return void
     */
    public function save2new()
    {
        $model = $this->getModel('dynamic_type');

        //$isValid = $model->validateForm();
        $isValid = true;
        if ($isValid)
        {
            $success = $model->save();
            if ($success)
            {
                $msg = JText::_('COM_THM_GROUPS_DATA_SAVED');
                $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_edit&cid[]=0', $msg);
            }
            else
            {
                $msg = JText::_('COM_THM_GROUPS_SAVE_ERROR');
                $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_edit&cid[]=0', $msg);
            }
        }
        else
        {
            $msg = JText::_('COM_THM_GROUPS_VALIDATION_ERROR');
            $this->setRedirect('index.php?option=com_thm_groups&view=dynamic_type_edit&cid[]=0', $msg, 'warning');
        }
    }
}
