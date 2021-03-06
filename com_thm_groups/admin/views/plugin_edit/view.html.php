<?php
/**
 * @version     v1.0.0
 * @category    Joomla component
 * @package     THM_Groups
 * @subpackage  com_thm_groups.admin
 * @name        THM_GroupsViewPlugin_Edit
 * @description THM_GroupsViewPlugin_Edit file from com_thm_groups
 * @author      Florian Kolb, <florian.kolb@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die('Restricted access');
jimport('thm_core.edit.view');

/**
 * THM_GroupsViewDynamic_Type_Edit class for component com_thm_groups
 *
 * @category  Joomla.Component.Admin
 * @package   com_thm_groups.admin
 * @link      www.mni.thm.de
 * @since     Class available since Release 2.0
 */
class THM_GroupsViewPlugin_Edit extends THM_CoreViewEdit
{
    /**
     * loads model data into view context
     *
     * @param	string  $tpl  the name of the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

	
    /**
     * AddToolbar //TODO comment
     * 
     * @return void
     */
    protected function addToolbar()
    {
        $title = $this->form->getValue('extension_id') == 0 ? 'New' : 'Edit';

        JToolBarHelper::title($title, 'test');

        JToolBarHelper::apply('plugin.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::save('plugin.save', 'JTOOLBAR_SAVE');
        
        // JToolBarHelper::custom('profile.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        
        JToolBarHelper::cancel('plugin.cancel', 'JTOOLBAR_CLOSE');
    }
}
