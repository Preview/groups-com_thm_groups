<?php
/**
 * @version     v3.1.0
 * @category    Joomla component
 * @package     THM_Groups
 * @subpackage  com_thm_groups.site
 * @name        THMGroupsViewEditGroup
 * @description THMGroupsViewEditGroup file from com_thm_groups
 * @author      Dennis Priefer, <dennis.priefer@mni.thm.de>
 * @author      Markus Kaiser,  <markus.kaiser@mni.thm.de>
 * @author      Daniel Bellof,  <daniel.bellof@mni.thm.de>
 * @author      Jacek Sokalla,  <jacek.sokalla@mni.thm.de>
 * @author      Niklas Simonis, <niklas.simonis@mni.thm.de>
 * @author      Peter May,      <peter.may@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die ('Restricted access');
$user = JFactory::getUser();
$userid = $user->id;

?>
    <form action="index.php" method="POST" name="adminForm" enctype='multipart/form-data'>
    <div>
        <fieldset class="adminform">
            <legend>
                <?php
                if (JRequest::getVar('view_back', 'keinPost', 'post') == 'keinPost')
                {
                    $view_back = JRequest :: getVar('view_back', 0);
                }
                else
                {
                    $view_back = JRequest :: getVar('view_back', 0, 'post');
                }

                if (JRequest::getVar('layout_back', 'keinPost', 'post') == 'keinPost')
                {
                    $layout_back = JRequest :: getVar('layout_back', 0);
                }
                else
                {
                    $layout_back = JRequest :: getVar('layout_back', 0, 'post');
                }
                echo   JText::_('COM_THM_GROUPS_DETAILS_VIEW_LABEL');
                ?>
            </legend>
            <table class="admintable">
                <tr>
                    <td width="110" class="key">
                        <label for="title">
                              ID:
                        </label>
                    </td>
                    <td>
                        <label for="title">
                              <?php echo $this->item[0]->id;?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td width="110" class="key">
                        <label for="title">
                              Name:
                        </label>
                    </td>
                    <td>
                        <input class="inputbox" type="text" name="gr_name" id="gr_name" size="60" value="<?php echo $this->item[0]->name;?>"/>
                    </td>
                </tr>
                <tr>
                    <td width="110" class="key">
                        <label for="title">
                              <?php echo JText::_('COM_THM_GROUPS_PARENT'); ?>
                        </label>
                    </td>
                    <td>
                        <select name="gr_parent" aria-required="true" required="required" aria-invalid="false">
                            <?php
                                $gap = 0;
                                foreach ($this->groups as $group)
                                {
                                    // Finde die Anzahl der parents
                                    $tempgroup = $group;
                                    $gap = 0;
                                    while ($tempgroup->parent_id != 0)
                                    {
                                        $gap++;
                                        foreach ($this->groups as $actualgroup)
                                        {
                                            if ($tempgroup->parent_id == $actualgroup->id )
                                            {
                                                $tempgroup = $actualgroup;
                                            }
                                        }
                                    }
                                    if ($group->id != $this->item[0]->id)
                                    {
                                        echo "<option value=$group->id " . ($this->item_parent_id == $group->id ? "selected='selected'" : "") . ">";
                                        while ($gap > 0)
                                        {
                                            $gap--;
                                            echo "- ";
                                        }
                                        echo "$group->title </option>";
                                    }
                                }
                            ?>
                        </select>
                    </td>
                </tr>


                <tr>
                    <td width="110" class="key">
                        <label for="title">
                              Info:
                        </label>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('groupinfo'); ?>
                    </td>
                </tr>
                <tr>
                    <td width="110" class="key">
                        <label for="title">
                              <?php echo JText::_('COM_THM_GROUPS_PICTURE'); ?>
                        </label>
                    </td>
                    <td>
                        <?php echo "<img src='" . JURI::root(true) . "/components/com_thm_groups/img/portraits/" . $this->item[0]->picture . "' />" ?>
                        <br />
                        <input type='file' accept='image' name='gr_picture' id='gr_picture' />
                        <br />
                        <input type='submit' id="gs_editView_buttons"
                        onclick='return confirm("<?php
                            echo JText::_('COM_THM_GROUPS_REALLY_DELETE');
                            ?>>"), document.forms["adminForm"].elements["task"].value = "editgroup.delPic"'
                        value='<?php echo JText::_('COM_THM_GROUPS_PICTURE_DELETE'); ?>' name='delPic' task='editgroup.delPic' />
                    </td>
                </tr>
                <tr>
                    <td width="110" class="key">
                        <label for="title">
                              <?php echo JText::_('COM_THM_GROUPS_MODE'); ?>
                        </label>
                    </td>
                    <td>
                    <?php $arrMode = explode(";", $this->item[0]->mode);?>
                        <SELECT MULTIPLE size='3' name='gr_mode[]' id='gr_mode' >
                            <?php
                            $sel = "";
                                foreach ($arrMode as $mode)
                                {
                                    if ($mode == 'profile')
                                    {
                                        $sel = "selected";
                                    }
                                }
                            ?>
                            <OPTION VALUE='profile' <?php
                                echo $sel;
                            ?>
                            >
                            <?php
                                echo JText::_('COM_THM_GROUPS_PROFILE');
                            ?>
                            </option>
                            <?php
                            $sel = "";
                                foreach ($arrMode as $mode)
                                {
                                    if ($mode == 'quickpage')
                                    {
                                        $sel = "selected";
                                    }
                                }
                            ?>
                            <OPTION VALUE='quickpage' <?php
                                echo $sel;
                            ?>
                            >
                            <?php
                                echo JText::_('COM_THM_GROUPS_QUICKPAGE');
                            ?>
                            </option>
                            <?php
                            $sel = "";
                                foreach ($arrMode as $mode)
                                {
                                    if ($mode == 'acl')
                                    {
                                        $sel = "selected";
                                    }
                                }
                            ?>
                            <OPTION VALUE='acl' <?php
                                echo $sel;
                            ?>
                            >
                            <?php
                                echo JText::_('COM_THM_GROUPS_ACL');
                            ?>
                            </option>
                        </SELECT>
                    </td>
                </tr>
                <tr>
                    <td>
                          <input type="submit" name="save" value="<?php echo JText::_('COM_THM_GROUPS_SAVE'); ?>" id="gs_editView_buttons"/>
                    </td>
                    <td>
                        <input
                        type='submit' id="gs_editView_buttons"
                        onclick='document.forms["adminForm"].elements["task"].value = "editgroup.backToRefUrl"'
                        value=<?php echo JText::_('COM_THM_GROUPS_BACK'); ?> name='backToRefUrl' task='editgroup.backToRefUrl' />
                    </td>
                </tr>
            </table>
            <input type="hidden" name="option" value="com_thm_groups" />
            <input type="hidden" name="task"   value="editgroup.save" />
            <input type="hidden" name="gsgid"   value="<?php echo $this->item[0]->id;?>" />
            <input type="hidden" name="controller" value="editgroup" />
            <input type="hidden" name="view" value="editgroup" />
            <input type="hidden" name="layout" value="default" />
            <input type="hidden" name="Itemid" value="<?php echo JRequest::getVar('Itemid', '0');?>" />
            <input type="hidden" name="gsuid" value="<?php echo $userid; ?>" />
            <input type='hidden' name="option_back" value=" <?php echo JRequest::getVar('option_back', 0, 'post');  ?> " />
            <input type='hidden' name="view_back" value="<?php echo  $view_back; ?>"/>
            <input type='hidden' name="layout_back" value="<?php echo  $layout_back; ?>" />
        </fieldset>
    </div>
</form>
