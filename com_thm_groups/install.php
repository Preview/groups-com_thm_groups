<?php
/**
 * @version     v3.4.5
 * @category    Joomla component
 * @package     THM_Groups
 * @subpackage  com_thm_groups.general
 * @name        Script
 * @description Script file from com_thm_groups
 * @author      Ilja Michajlow, <ilja.michajlow@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die('Restricted access');
if (!defined('_JEXEC'))
{
    define('_JEXEC', 1);
}
if (!defined('DS'))
{
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * THM_Groups_Install_Script
 *
 * @category    Joomla.Component.General
 * @package     thm_groups
 * @subpackage  com_thm_groups
 * @since       v3.5.0
 */
class THM_Groups_Install_Script
{
    /**
     * Install script for THM Groups
     *
     * If THM Groups will be installed for the first time
     * than the script copies all users and users to groups mapping
     * from Joomla into THM Groups tables
     *
     * @return void
     */
    public static function install()
    {
        if (self::copyUsers() && self::copyGroupsRolesMapping()) //&& self::copyMenutoProfile() && self::copyModuletoProfile())
        {
            return true;
        }


        JFactory::getApplication()->enqueueMessage('install', 'error');
        return false;
    }

    /**
     * Copy users information from Joomla into THM Groups
     *
     * @return bool
     */
    private static function copyUsers()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('id, name, username, email')
            ->from('#__users');

        $db->setQuery($query);
        try
        {
            $users = $db->loadObjectList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        // If there are no users in Joomla
        if (empty($users))
        {
            JFactory::getApplication()->enqueueMessage('There are no users in Joomla', 'error');
            return false;
        }

        if (self::copyUserId($users) && self::copyUserAttributes($users))
        {
            return true;
        }

        JFactory::getApplication()->enqueueMessage('copyUsers', 'error');
        return false;
    }

    /**
     * copy Advanced Menu in Profile
     */
    public static function  copyMenutoProfile()
    {
        try {
            $db = JFactory::getDbo();
            $searchAllQuery = $db->getQuery(true);
            $searchAllQuery->select('id as menuid, title as menutitle, params as menuparams')
                ->from('#__menu')
                ->where("link like 'index.php?option=com_thm_groups&view=advanced'")
                ->where("type like component");

            $db->setQuery($searchAllQuery);
            $searchAllData = $db->loadObjectList();
            $allProfilData = [];
            foreach($searchAllData as $menuData){
                $profilItem = new stdClass();
                $menuparams = json_decode($menuData->menuparams);
                $menuGroupID = $menuparams->selGroup;
                $profilItem->gid = $menuGroupID;
                $menuAttrStruct = $menuparams->struct;
                $profilItem->attrData = self::getOldAttrToProfileData($menuAttrStruct);
                $profilItem->name = $menuparams->menutitle;
                $allProfilData []= $profilItem;
            }
            //Insert Profile in the Database
            $insertProfileQuery = $db->getQuery(true);
            $profileColumn = array("name", "order");
            $insertProfileQuery->insert('#__thm_groups_profile')
                ->columns($profileColumn);
            $profileorder =1;
            foreach($allProfilData as $profilData){
                $insertProfileQuery->values($profilData->name,$profileorder);
                $profileorder++;
            }
            $db->setQuery($insertProfileQuery);
            $db->execute();
            $firstProfileID = $db->insertid()- count($allProfilData);

            //Insert Profile and Groups in the Database
            $insertProfileGroupQuery = $db->getQuery(true);
            $profileGroupColumn = array("profileID", "usergroupsID");
            $insertProfileGroupQuery->insert('#__thm_groups_profile_usergroups')
                ->columns($profileGroupColumn);
            $profilIdcounter = $firstProfileID;
            foreach($allProfilData as $profilData){
                $insertProfileGroupQuery->values($profilIdcounter, $profilData->gid);
                $profilData->id = $profilIdcounter;
                $profilIdcounter++;
            }
            $db->setQuery($insertProfileGroupQuery);
            $db->execute();

            //Insert Profile and Attribute in the Database
            $insertProfileAttrQuery = $db->getQuery(true);
            $profileAttrColumn = array("profileID", "attributeID", "order", "params");
            $insertProfileAttrQuery->insert('#__thm_groups_profile_attribute')
                ->columns($profileAttrColumn);
            foreach($allProfilData as $profilData)
            {
                foreach($profilData->attrData as $attritem)
                {
                    $insertProfileAttrQuery->values($profilData->id,
                        $attritem->attrID, $attritem->order, json_encode($attritem->params));
                }


            }
            $db->setQuery($insertProfileAttrQuery);
            $db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        return true;

}
    /**
     * copy Thm Groups Module in Profile
     */
    public static function  copyModuletoProfile()
    {
        if (self::copyModuleTypetoProfile('mod_thm_groups_smallview')
            && self::copyModuleTypetoProfile("mod_thm_groups_members"))
        {
            return true;
        }
        return false;
    }

    /**
     * copy Thm Groups Module in Profile
     * @param  String $moduletype
     */
    public static function  copyModuleTypetoProfile($moduletype)
    {
        try {
            $db = JFactory::getDbo();
            $searchAllQuery = $db->getQuery(true);
            $searchAllQuery->select('id as modid, title as modtitle, params as modparams')
                ->from('#__modules')
                ->where("module like '$moduletype'");

            $db->setQuery($searchAllQuery);
            $searchAllData = $db->loadObjectList();
            $allProfilData = [];
            foreach ($searchAllData as $modData) {
                $profilItem = new stdClass();
                $modparams = json_decode($modData->menuparams);
                $modGroupID = $modparams->selGroup;
                $profilItem->gid = $modGroupID;
                if (strcmp($moduletype, 'mod_thm_groups_smallview') == 0) {
                    $modAttrStruct = $modparams->structid;
                }
                else {
                    $modAttrStruct = $modparams->struct;
                }

                $profilItem->attrData = self::getOldAttrToProfileData($modAttrStruct);
                $profilItem->name = 'mod_' . $modparams->menutitle;
                $allProfilData [] = $profilItem;
            }
            //Insert Profile in the Database
            $insertProfileQuery = $db->getQuery(true);
            $profileColumn = array("name", "order");
            $insertProfileQuery->insert('#__thm_groups_profile')
                ->columns($profileColumn);
            $profileorder = 1;
            foreach ($allProfilData as $profilData) {
                $insertProfileQuery->values($profilData->name, $profileorder);
                $profileorder++;
            }
            $db->setQuery($insertProfileQuery);
            $db->execute();
            $firstProfileID = $db->insertid() - count($allProfilData) + 1;

            //Insert Profile and Groups in the Database
            $insertProfileGroupQuery = $db->getQuery(true);
            $profileGroupColumn = array("profileID", "usergroupsID");
            $insertProfileGroupQuery->insert('#__thm_groups_profile_usergroups')
                ->columns($profileGroupColumn);
            $profilIdcounter = $firstProfileID;
            foreach ($allProfilData as $profilData) {
                $insertProfileGroupQuery->values($profilIdcounter, $profilData->gid);
                $profilData->id = $profilIdcounter;
                $profilIdcounter++;
            }
            $db->setQuery($insertProfileGroupQuery);
            $db->execute();

            //Insert Profile and Attribute in the Database
            $insertProfileAttrQuery = $db->getQuery(true);
            $profileAttrColumn = array("profileID", "attributeID", "order", "params");
            $insertProfileAttrQuery->insert('#__thm_groups_profile_attribute')
                ->columns($profileAttrColumn);
            foreach ($allProfilData as $profilData) {
                foreach ($profilData->attrData as $attritem) {
                    $insertProfileAttrQuery->values($profilData->id,
                        $attritem->attrID, $attritem->order, json_encode($attritem->params));
                }


            }
            $db->setQuery($insertProfileAttrQuery);
            $db->execute();
        }
        catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
        return true;
    }


    /**
     * Transform the old Attribute Structure to the new
     *
     */
    public static function  getOldAttrToProfileData($oldAttrStruct){
        $result = [];
        $count = 1;
        foreach($oldAttrStruct as $id=> $oldItem){
            $newItem = new stdClass();
            $stringFormOld = "" . $oldItem;
            $wrap = substr($stringFormOld, -1);
            $label = substr($stringFormOld, -2);
            $attrID = substr($stringFormOld, 0, -2);
            $newItem->attrID = $attrID;
            $newItem->order = $count;
            $newItem->params = [];
            $newItem->params['label'] = $label;
            $newItem->params['wrap'] = $wrap;
            $result []= $newItem;
            $count++;
        }
        return $result;
    }
    /**
     * Copy user id and save it into THM Groups table with additional attributes like:
     * published is 1, it means that the user is activated in the THM Groups component
     * injoomla does not matter
     * canEdit is 1, because all users can edit their profile by default
     * qpPublished is 0, because all users are not allowed to edit their Quickpages by default
     *
     * @param   array  &$users  An array with users
     *
     * @return bool
     *
     * @throws Exception
     */
    private static function copyUserId(&$users)
    {
        $db = JFactory::getDbo();
        foreach ($users as $user)
        {
            $query = $db->getQuery(true);
            $columns = array('id', 'published', 'injoomla', 'canEdit', 'qpPublished');
            $values = array($user->id, 1, 1, 1, 0);
            $query
                ->insert('#__thm_groups_users')
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            $db->setQuery($query);

            try
            {
                $db->execute();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
        }
        return true;
    }

    /**
     * Copy user attribute like first name, last name, username and email into
     * THM Groups table
     *
     * @param   array  &$users  An array with users
     *
     * @return bool
     *
     * @throws Exception
     */
    private static function copyUserAttributes(&$users)
    {
        $db = JFactory::getDbo();
        $attributes = self::getInstalledAttributes();

        foreach ($users as $user)
        {
            $query = $db->getQuery(true);

            // Prepare first and second name
            $nameArray = explode(" ", $user->name);
            $lastName = end($nameArray);
            array_pop($nameArray);

            $deleteFromName = array("(", ")", "Admin", "Webmaster");
            $nameSplit = explode(" ", str_replace($deleteFromName, '', $user->name));
            array_pop($nameSplit);
            $firstName = implode(" ", $nameArray);

            // Prepare data
            $columns = array('usersID', 'attributeID', 'value', 'published');
            $firstName = array($user->id, 1, $db->quote($firstName), 1);
            $lastName = array($user->id, 2, $db->quote($lastName), 1);
            $username = array($user->id, 3, $db->quote($user->username), 1);
            $email = array($user->id, 4, $db->quote($user->email), 1);

            $query
                ->insert('#__thm_groups_users_attribute')
                ->columns($db->quoteName($columns))
                ->values(implode(',', $firstName))
                ->values(implode(',', $lastName))
                ->values(implode(',', $username))
                ->values(implode(',', $email));

            foreach ($attributes as $attribute)
            {
                $values = array($user->id, $attribute->id, "''", 0);
                $query
                    ->values(implode(',', $values));
            }

            $db->setQuery($query);

            try
            {
                $db->execute();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
        }
        return true;
    }

    /**
     * Returns all installed attributes ID except of 1,2,3,4
     * 1 - first name
     * 2 - second name
     * 3 - username
     * 4 - email
     *
     * @return bool|mixed
     *
     * @throws Exception
     */
    private static function getInstalledAttributes()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('id')
            ->from('#__thm_groups_attribute')
            ->where('id NOT IN (1, 2, 3, 4)');

        $db->setQuery($query);

        try
        {
            $result = $db->loadObjectList();
        }
        catch (Exception $e)
        {
            JFactory::getApplication()->enqueueMessage('getInstalledAttribute ' . $e->getMessage(), 'error');
            return false;
        }

        return $result;
    }

    /**
     * Copy groups from Joomla and assign them a default role
     * with the id 1 and then get saved ID from group to role mapping
     * table and assign it to user to group-role mapping ID
     *
     * @return bool
     */
    private static function copyGroupsRolesMapping()
    {
        if (self::copyGroups() && self::assignRoleGroupMappingToUser())
        {
            return true;
        }

        JFactory::getApplication()->enqueueMessage('copyGroupsRolesMapping', 'error');
        return false;
    }

    /**
     * Copy all groups, then assign to all groups a role with the id 1
     * and assign this mapping to a user
     *
     * @return bool
     *
     * @throws Exception
     */
    private static function copyGroups()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('DISTINCT(group_id)')
            ->from('#__user_usergroup_map');

        $db->setQuery($query);

        try
        {
            $groupIds = $db->loadObjectList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        // If there are no groups
        if (empty($groupIds) || $groupIds == null)
        {
            JFactory::getApplication()->enqueueMessage('There are no groups', 'error');
            return false;
        }

        if (!self::saveDefaultRoleToGroup($groupIds))
        {
            JFactory::getApplication()->enqueueMessage('saveDefaultRoleToGroup', 'error');
            return false;
        }

        return true;
    }

    /**
     * Save default role with id 1 to all groups that are
     * assigned to users
     *
     * @param   array  &$groups  An array with group ids
     *
     * @return bool
     *
     * @throws Exception
     */
    private static function saveDefaultRoleToGroup(&$groups)
    {
        $db = JFactory::getDbo();
        foreach ($groups as $group)
        {
            $query = $db->getQuery(true);
            $columns = array('usergroupsID', 'rolesID');
            $values = array($group->group_id, 1);
            $query
                ->insert('#__thm_groups_usergroups_roles')
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            $db->setQuery($query);

            try
            {
                $db->execute();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
        }
        return true;
    }

    /**
     * Assign a group-role mapping to a user
     *
     * @return bool
     *
     * @throws Exception
     */
    private static function assignRoleGroupMappingToUser()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('user_id, group_id')
            ->from('#__user_usergroup_map');

        $db->setQuery($query);

        try
        {
            $userGroupMap = $db->loadObjectList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        if (!self::saveRoleGroupMappingToUser($userGroupMap))
        {
            JFactory::getApplication()->enqueueMessage('saveRoleGroupMappingToUser', 'error');
            return false;
        }

        return true;
    }

    /**
     * Gets previously saved IDs and assigns this to users
     * It's not the best solution
     *
     * @param   array  &$userGroupMap  An array with user to group mapping
     *
     * @return bool
     *
     * @throws Exception
     */
    private static function saveRoleGroupMappingToUser(&$userGroupMap)
    {
        $db = JFactory::getDbo();

        foreach ($userGroupMap as $map)
        {
            $query = $db->getQuery(true);
            $query
                ->select('ID')
                ->from('#__thm_groups_usergroups_roles')
                ->where('usergroupsID = ' . $map->group_id)
                ->where('rolesID = 1');

            $db->setQuery($query);
            try
            {
                $result = $db->loadObject();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }

            // If there is no result
            if (empty($result) ||$result == null)
            {
                return false;
            }

            $query = $db->getQuery(true);
            $columns = array('usersID', 'usergroups_rolesID');
            $values = array($map->user_id, $result->ID);
            $query
                ->insert('#__thm_groups_users_usergroups_roles')
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            $db->setQuery($query);

            try
            {
                $db->execute();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
        }
        return true;
    }
}