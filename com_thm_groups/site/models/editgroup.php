<?php
/**
 * @version     v3.0.2
 * @category    Joomla component
 * @package     THM_Groups
 * @subpackage  com_thm_groups.site
 * @name        THMGroupsModelEditGroup
 * @description THMGroupsModelEditGroup file from com_thm_groups
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
defined('_JEXEC') or die();
jimport('joomla.application.component.modelform');

/**
 * THMGroupsModelEditGroup class for component com_thm_groups
 *
 * @category  Joomla.Component.Site
 * @package   com_thm_groups.site
 * @link      www.mni.thm.de
 * @since     Class available since Release 2.0
 */
class THM_GroupsModelEditGroup extends JModelForm
{
    /**
     * Constructor

     * gets form for editgroup
     */
    public function __construct()
    {
        parent::__construct();
        $this->getForm();
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return mixed A JForm object on success, false on failure
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_groups.editgroup', 'editgroup', array());

        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    /**
     * Function to build query
     *
     * @return mysql query
     */
    public function _buildQuery()
    {
        $gsgid = JRequest::getVar('gsgid');
        /*
        $query = "SELECT * FROM #__thm_groups_groups WHERE id=" . $gsgid;
        */
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->qn('#__thm_groups_groups'));
        $query->where('id = ' . $db->quote($gsgid));
        return $query->__toString();
    }

    /**
     * Method to get data.
     *
     * @return data object from database
     */
    public function getData()
    {
        $query       = $this->_buildQuery();
        $this->_data = $this->_getList($query);
        return $this->_data;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function store()
    {
        $gr_name = JRequest::getVar('gr_name');
        $gr_info = JRequest::getVar('groupinfo', '', 'post', 'string', JREQUEST_ALLOWHTML);

        $gr_mode   = JRequest::getVar('gr_mode');
        $gr_parent = JRequest::getVar('gr_parent');
        $gr_mode   = implode(';', $gr_mode);
        $gid       = JRequest::setVar('gsgid');

        $db =& JFactory::getDBO();
        $err = 0;

        /*
        $query = "UPDATE #__thm_groups_groups SET" . " name='" . $gr_name . "'" . ", info='"
        . $gr_info . "'" . ", mode='" . $gr_mode . "'" . " WHERE id=" . $gid;
        */
        $query = $db->getQuery(true);
        $query->update($db->qn('#__thm_groups_groups'));
        $query->set("`name` = '" . $gr_name . "'");
        $query->set("`info` = '" . $gr_info . "'");
        $query->set("`mode` = '" . $gr_mode . "'");
        $query->where('id = ' . $db->quote($gid));

        $db->setQuery($query);
        if (!$db->query())
        {
            $err = 1;
        }

        if ($_FILES['gr_picture']['name'] != "")
        {
            if (!$this->updatePic($gid, 'gr_picture'))
            {
                $err = 1;
            }
        }

        /*
        $query = "SELECT injoomla " . "FROM `#__thm_groups_groups` " . "WHERE id = " . $gid;
        */
        $query = $db->getQuery(true);
        $query->select('injoomla');
        $query->from($db->qn('#__thm_groups_groups'));
        $query->where('id = ' . $db->quote($gid));
        $db->setQuery($query);
        $injoomla = $db->loadObject();
        $injoomla = $injoomla->injoomla;

        // Joomla Gruppe nur anpassen wenn sie da auch exisitiert
        if ($injoomla == 1)
        {
            // Gruppe anpassen

            /*
            $query = "UPDATE #__usergroups " . "SET parent_id = " . $gr_parent . ", title = '" . $gr_name . "' " . "WHERE id = " . $gid;
            */
            $query = $db->getQuery(true);
            $query->update($db->qn('#__usergroups'));
            $query->set("`parent_id` = '" . $gr_parent . "'");
            $query->set("`title` = '" . $gr_name . "'");
            $query->where('id = ' . $db->quote($gid));
            $db->setQuery($query);
            $db->query();

            // Gruppe aus Datenbank lesen

            /*
            $query = "SELECT * " . "FROM `#__usergroups` " . "WHERE id = " . $gid;
            */
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from($db->qn('#__usergroups'));
            $query->where('id = ' . $db->quote($gid));
            $db->setQuery($query);
            $jgroup = $db->loadObject();

            // Elterngruppe aus Datenbank lesen

            /*
            $query = "SELECT * " . "FROM `#__usergroups` " . "WHERE id = " . $gr_parent;
            */
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from($db->qn('#__usergroups'));
            $query->where('id = ' . $db->quote($gr_parent));
            $db->setQuery($query);
            $parent = $db->loadObject();

            // Gruppe einsortieren

            /*
            $query = "SELECT * " . "FROM `#__usergroups` " . "WHERE parent_id = " . $gr_parent . " " . "ORDER BY title";
            */
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from($db->qn('#__usergroups'));
            $query->where('parent_id = ' . $db->quote($gr_parent));
            $query->order('title');
            $db->setQuery($query);
            $jsortgrps = $db->loadObjectlist();

            // Finde neuen linken Index
            $leftneighbor = null;
            foreach ($jsortgrps as $grp)
            {
                if ($grp->id == $gid)
                {
                    break;
                }
                else
                {
                    $leftneighbor = $grp;
                }
            }
            if ($leftneighbor == null)
            {
                $new_lft = $parent->lft + 1;
            }
            else
            {
                $new_lft = $leftneighbor->rgt + 1;
            }
            $jgrouprange = $jgroup->rgt - $jgroup->lft + 1;

            // Platz schaffen
            // Rechten Index aktualisieren

            /*
            $query = "UPDATE `#__usergroups` " . "SET rgt = rgt + " . $jgrouprange . " " . "WHERE rgt >= " . $new_lft;
            */
            $query = $db->getQuery(true);
            $query->update($db->qn('#__usergroups'));
            $query->set("rgt = rgt + " . $jgrouprange);
            $query->where('rgt >= ' . $db->quote($new_lft));
            $db->setQuery($query);
            $db->query();

            // Linken Index aktualisieren

            /*
            $query = "UPDATE `#__usergroups` " . "SET lft = lft + " . $jgrouprange . " " . "WHERE lft >= " . $new_lft;
            */
            $query = $db->getQuery(true);
            $query->update($db->qn('#__usergroups'));
            $query->set("lft = lft + " . $jgrouprange);
            $query->where('lft >= ' . $db->quote($new_lft));
            $db->setQuery($query);
            $db->query();

            // Gruppe neu aus Datenbank lesen

            /*
            $query = "SELECT * " . "FROM `#__usergroups` " . "WHERE id = " . $gid;
            */
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from($db->qn('#__usergroups'));
            $query->where('id = ' . $db->quote($gid));
            $db->setQuery($query);
            $jgroup = $db->loadObject();

            // Daten zwischenspeichern
            $old_lft = $jgroup->lft;
            $old_rgt = $jgroup->rgt;
            $jgroupspan = $new_lft - $old_lft;

            // Gruppe verschieben

            /*
            $query = "UPDATE `#__usergroups` " . "SET rgt = rgt + " . $jgroupspan . " " . "WHERE rgt >= " . $old_lft . " AND rgt <= " . $old_rgt;
            */
            $query = $db->getQuery(true);
            $query->update($db->qn('#__usergroups'));
            $query->set("rgt = rgt + " . $jgroupspan);
            $query->where('rgt >= ' . $db->quote($old_lft));
            $query->where('rgt <= ' . $db->quote($old_rgt));
            $db->setQuery($query);
            $db->query();
            /*
            $query = "UPDATE `#__usergroups` " . "SET lft = lft + " . $jgroupspan . " " . "WHERE lft >= " . $old_lft . " AND lft <= " . $old_rgt;
            */
            $query = $db->getQuery(true);
            $query->update($db->qn('#__usergroups'));
            $query->set("lft = lft + " . $jgroupspan);
            $query->where('lft >= ' . $db->quote($old_lft));
            $query->where('lft <= ' . $db->quote($old_rgt));
            $db->setQuery($query);
            $db->query();

            // Luecke schliessen

            /*
            $query = "UPDATE `#__usergroups` " . "SET rgt = rgt - " . $jgrouprange . " " . "WHERE rgt >= " . $old_lft;
            */
            $query = $db->getQuery(true);
            $query->update($db->qn('#__usergroups'));
            $query->set("rgt = rgt - " . $jgrouprange);
            $query->where('rgt >= ' . $db->quote($old_lft));
            $db->setQuery($query);
            $db->query();
            /*
            $query = "UPDATE `#__usergroups` " . "SET lft = lft - " . $jgrouprange . " " . "WHERE lft >= " . $old_lft;
            */
            $query = $db->getQuery(true);
            $query->update($db->qn('#__usergroups'));
            $query->set("lft = lft - " . $jgrouprange);
            $query->where('lft >= ' . $db->quote($old_lft));
            $db->setQuery($query);
            $db->query();
        }

        if (!$err)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Method to update a picture
     *
     * @param   Int     $gid       StructID
     * @param   Object  $picField  Picturefield
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function updatePic($gid, $picField)
    {
        require_once JPATH_ROOT . DS . "components" . DS . "com_thm_groups" . DS . "helper" . DS . "thm_groups_pictransform.php";
        try
        {
            $pt = new THMPicTransform($_FILES[$picField]);
            $compath = "com_thm_groups";

/* 			$pt->safeSpecial(
                     JPATH_ROOT . DS . $this->getPicPath($structid) . DS,//"components" . DS . "com_thm_groups" . DS . "img" . DS . "portraits" . DS,
                     "g" . $gid, 200, 200, "JPG"); */

            $pt->safeSpecial(JPATH_ROOT . DS . "components" . DS . $compath . DS . "img" . DS . "portraits" . DS, "g" . $gid, 200, 200, "JPG");
            if (JModuleHelper::isEnabled('mod_thm_groups')->id != 0)
            {
                $pt->safeSpecial(JPATH_ROOT . DS . "modules" . DS . "mod_thm_groups" . DS . "images" . DS, "g" . $gid, 200, 200, "JPG");
            }
            if (JModuleHelper::isEnabled('mod_thm_groups_smallview')->id != 0)
            {
                $pt->safeSpecial(JPATH_ROOT . DS . "modules" . DS . "mod_thm_groups_smallview" . DS . "images" . DS, "g" . $gid, 200, 200, "JPG");
            }
        }
        catch (Exception $e)
        {
            return false;
        }
        $db = JFactory::getDBO();
        /*
        $query = "UPDATE #__thm_groups_groups SET picture='g" . $gid . ".jpg' WHERE id = $gid ";
        */
        $query = $db->getQuery(true);
        $query->update($db->qn('#__thm_groups_groups'));
        $query->set("`picture` = 'g" . $gid . ".jpg'");
        $query->where('id = ' . $db->quote($gid));
        $db->setQuery($query);
        if ($db->query())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Get extra path from db (for picture)
     *
     * @param   Int  $structid  StructID
     *
     * @access	public
     * @return	String value
     */
    public function getPicPath($structid)
    {
        $db = JFactory::getDBO();
        /*
         $query = "SELECT path FROM #__thm_groups_" . strtolower($type) . "_extra WHERE structid=" . $structid;
        */
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from("#__thm_groups_picture_extra");
        $query->where("`structid` = '" . $db->quote($structid) . "'");
        $db->setQuery($query);
        $res = $db->loadObject();
        if (isset($res->path))
        {
            return $res->path;
        }
        else
        {
            return "";
        }
    }

    /**
     * Method to delete a picture
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function delPic()
    {
        $db = JFactory::getDBO();
        $gid = JRequest::getVar('gsgid');

        /*
        $query = "UPDATE #__thm_groups_groups SET picture='anonym.jpg' WHERE id = $gid ";
        */
        $query = $db->getQuery(true);
        $query->update($db->qn('#__thm_groups_groups'));
        $query->set("`picture` = 'anonym.jpg'");
        $query->where('id = ' . $db->quote($gid));
        $db->setQuery($query);

        if ($db->query())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Method to get all groups
     *
     * @access	public
     * @return	database object
     */
    public function getAllGroups()
    {
        $db = JFactory::getDBO();
        /*
        $query = "SELECT * FROM #__usergroups ORDER BY lft";
        */
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->qn('#__usergroups'));
        $query->order('lft');
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Method to get parent id
     *
     * @access	public
     * @return	database object
     */
    public function getParentId()
    {
        $gid = JRequest::getVar('gsgid', 0, 'get', 'INTEGER');
        if (!isset($gid))
        {
            $gid = JRequest::getVar('gsgid', 0, 'post', 'INTEGER');
        }
        $db = JFactory::getDBO();
        /*
        $query = "SELECT parent_id FROM #__usergroups WHERE id=" . $gid;
        */
        $query = $db->getQuery(true);
        $query->select('parent_id');
        $query->from($db->qn('#__usergroups'));
        $query->where('id = ' . $db->quote($gid));
        $db->setQuery($query);
        return $db->loadObject()->parent_id;
    }
}
