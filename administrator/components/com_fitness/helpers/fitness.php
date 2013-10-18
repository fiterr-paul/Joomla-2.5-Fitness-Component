<?php
/**
 * @version     1.0.0
 * @package     com_fitness
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nikolay Korban <niklug@ukr.net> - http://
 */

// No direct access
defined('_JEXEC') or die;

class FitnessFactory {
    
    const SUPERUSER_GROUP_ID = 8;
    const MANAGER_GROUP_ID = 6;
    const REGISTERED_GROUP_ID = 2;
    
    public static $trainers_group_id = null;
    public static $group_id = null;
    public static $is_superuser = null;
    public static $is_trainer = null;
    public static $is_primary_administrator = null;
    public static $is_secondary_administrator = null;
    
    
   
    public static function getTrainersGroupId() {
        
        if (!self::$trainers_group_id) {
            self::$trainers_group_id = self::createTrainersGroupId();
        }

        return self::$trainers_group_id;
    }
    
     public static function getCurrentGroupId($user_id) {
        
        if (!self::$group_id) {
            self::$group_id = self::createCurrentGroupId($user_id);
        }

        return self::$group_id;
    }
    
    public static function is_superuser($user_id) {
        if(self::getCurrentGroupId($user_id) == self::SUPERUSER_GROUP_ID) {
            return true;
        }
        return false;
    }
    
    public static function is_trainer($user_id) {
        if(self::$is_trainer == null) {
            $group_id = self::getCurrentGroupId($user_id);
            $parent_group_id =  self::MANAGER_GROUP_ID;
            self::$is_trainer = self::isChildGroup($group_id, $parent_group_id);
            return self::$is_trainer;
        }
        return self::$is_trainer;
    }
    
    public static function is_primary_administrator($user_id) {
        if(self::$is_primary_administrator == null) {
            if(!$user_id) {
                $user_id = &JFactory::getUser()->id;
            }
            $primary_administrator_id = self::getAdministratorId('primary_administrator');

            if($user_id == $primary_administrator_id) {
                self::$is_primary_administrator = $primary_administrator_id;
            } else {
                self::$is_primary_administrator = false;
            }
            return self::$is_primary_administrator;
        }
        return self::$is_primary_administrator;
    }
    
    
    public static function is_secondary_administrator($user_id) {
        if(self::$is_secondary_administrator == null) {
            if(!$user_id) {
                $user_id = &JFactory::getUser()->id;
            }
            $primary_administrator_id = self::getAdministratorId('secondary_administrator');

            if($user_id == $primary_administrator_id) {
                self::$is_secondary_administrator = $primary_administrator_id;
            } else {
                self::$is_secondary_administrator = false;
            }
            return self::$is_secondary_administrator;
        }
        return self::$is_secondary_administrator;
    }
    
    public function getAdministratorId($administrator_type, $user_id) {
        $group_id = self::getCurrentGroupId($user_id);
        $db = JFactory::getDBO();
        $query = "SELECT $administrator_type FROM #__fitness_business_profiles WHERE group_id='$group_id'  AND state='1'";
        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        return $db->loadResult();
    }
    
    
    public static function createTrainersGroupId() {
        $db = & JFactory::getDBO();
        $user = &JFactory::getUser();
        $groups = $user->get('groups');
        $user_group_id = array_shift(array_values($groups));


        $query = "SELECT bp.group_id AS trainers_group_id from #__fitness_user_groups AS ug "
                . " INNER JOIN #__fitness_business_profiles AS bp ON bp.id=ug.business_profile_id "
                . " WHERE ug.group_id = '$user_group_id'"
                . " AND ug.state='1'"
                . " AND bp.state='1'";
        $db->setQuery($query);
        
        if (!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        
        $trainers_group_id = $db->loadResult();
        
        
        if (!$trainers_group_id) {
            $trainers_group_id = $user_group_id;
        }
        
        if (!$trainers_group_id) {
            JError::raiseWarning( 100, 'No Trainers Group assigned!' );
        }

        return $trainers_group_id;
    }
    
    public static function createCurrentGroupId($user_id) {
        if(!$user_id) {
            $user_id = &JFactory::getUser()->id;
        }
        $db = JFactory::getDBO();
        $query = "SELECT group_id FROM #__user_usergroup_map WHERE user_id='$user_id'";
        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $group_id = $db->loadResult();
        
        if (!$group_id) {
            JError::raiseWarning( 100, 'User Group not found!' );
        }
        return $group_id;
    }
    
      
    
    public static function isChildGroup($group_id, $parent_group_id) {
        if(!$user_id) {
            $user_id = &JFactory::getUser()->id;
        }
        $db = JFactory::getDBO();
        $query = "SELECT id FROM #__usergroups WHERE id='$group_id'  AND parent_id='$parent_group_id'";
        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $group_id = $db->loadResult();
        
        return $group_id;
    }
    
    
}

/**
 * Fitness helper.
 */
class FitnessHelper extends FitnessFactory
{
    const PENDING_GOAL_STATUS = '1';
    const COMPLETE_GOAL_STATUS = '2';
    const INCOMPLETE_GOAL_STATUS = '3';
    const EVELUATING_GOAL_STATUS = '4';
    const INPROGRESS_GOAL_STATUS = '5';
    const ASSESSING_GOAL_STATUS = '6';

    const CLIENTS_USERGROUP = 'Registered';
    const ADMINISTRATOR_USERGROUP = 'Super Users';
    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($vName = '', $view)
    {
        if($view == 'calendar') {
                            JSubMenuHelper::addEntry(
                    $vName,
                    'index.php?option=com_multicalendar&view=admin&task=admin',
                    $vName == $vName
            );
            return;
        }
        JSubMenuHelper::addEntry(
                $vName,
                'index.php?option=com_fitness&view='. $view,
                $vName == $vName
        );

    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return	JObject
     * @since	1.6
     */
    public static function getActions()
    {
            $user	= JFactory::getUser();
            $result	= new JObject;

            $assetName = 'com_fitness';

            $actions = array(
                    'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
            );

            foreach ($actions as $action) {
                    $result->set($action, $user->authorise($action, $assetName));
            }

            return $result;
    }


    ////////////////////////////////////////////////////////////////////////
    public function sendEmail($recipient, $Subject, $body) {

        $mailer = & JFactory::getMailer();

        $config = new JConfig();

        $sender = array($config->mailfrom, $config->fromname);

        $mailer->setSender($sender);

        //$recipient = 'npkorban@mail.ru';

        $mailer->addRecipient($recipient);

        $mailer->setSubject($Subject);

        $mailer->isHTML(true);

        $mailer->setBody($body);

        $send = & $mailer->Send();

        return $send;
     }


    function getContentCurl($url) {
        $ret['success'] = true;
        if(!function_exists('curl_version')) {
            $ret['success'] = false;
            $ret['message'] = 'cURL not anabled';
            return $ret;
        }

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec ($ch);
        curl_close ($ch);
        $ret['data'] = $contents;
        return $ret;
    }

    /**
     * 
     * @param type $client_id
     * @param type $type :  primary, secondary, all
     * @return type
     */
    function  getClientTrainers($client_id, $type) {
        $status['success'] = 1;
        $db = & JFactory::getDBO();
        $user = &JFactory::getUser();

        $query = "SELECT primary_trainer, other_trainers FROM #__fitness_clients WHERE user_id='$client_id' AND state='1'";
        $db->setQuery($query);
        
        if (!$db->query()) {
            $status['success'] = 0;
            $status['message'] = $db->stderr();
            return $status;
        }
        $primary_trainer= $db->loadResultArray(0);
        $other_trainers = $db->loadResultArray(1);
        $other_trainers = explode(',', $other_trainers[0]);
        $all_trainers_id = array_unique(array_merge($primary_trainer, $other_trainers));

        if($type == 'secondary') {
            $all_trainers_id = $other_trainers;
        }
        
        if($type == 'primary') {
            $all_trainers_id = $primary_trainer;
        }

        if(!$all_trainers_id) {
            $status['success'] = 0;
            $status['message'] = 'No trainers assigned to this client.';
        }

        $result = array( 'status' => $status, 'data' => $all_trainers_id);
        
        return $result;
    }
    
    /**
     * 
     * @param type $id
     * @param type $goal_type : 1 -primary, 2 - mini
     * @return array
     */
    function getClientIdByGoalId($id, $goal_type) {
        $result['success'] = true;
        $db = & JFactory::getDBO();
        $query = "SELECT user_id, (SELECT primary_trainer FROM #__fitness_clients WHERE user_id=#__fitness_goals.user_id ) trainer_id FROM #__fitness_goals WHERE id='$id' AND state='1'";
        if($goal_type == '2') {
            $query = "SELECT pg.user_id, c.primary_trainer AS trainer_id FROM #__fitness_mini_goals AS mg
                LEFT JOIN #__fitness_goals AS pg ON pg.id=mg.primary_goal_id
                LEFT JOIN #__fitness_clients AS c ON c.user_id=pg.user_id
                WHERE mg.id='$id' AND mg.state='1'
            ";
        }
        $db->setQuery($query);
        if (!$db->query()) {
            $result['success'] = false;
            $result['message'] = $db->stderr();
            return $result;
        }
        $client_id = $db->loadResultArray(0);
        $trainer_id = $db->loadResultArray(1);

        $result['data'] = array('client_id' => $client_id[0], 'trainer_id' => $trainer_id[0]);
        
        return $result;
    }
    
    public function getGoal($id, $table) {
        $ret['success'] = true;
        $db = &JFactory::getDBo();
        $query = "SELECT * FROM $table WHERE id='$id'";
        $db->setQuery($query);
        if (!$db->query()) {
            $ret['success'] = false;
            $ret['message'] = $db->stderr();
            return $ret;
        }
        $ret['data'] = $db->loadObject();
        return $ret;
    }
    
    public function getUserGroup($user_id) {
        $ret['success'] = true;
        if(!$user_id) {
            $user_id = &JFactory::getUser()->id;
        }
        $db = JFactory::getDBO();
        $query = "SELECT title FROM #__usergroups WHERE id IN 
            (SELECT group_id FROM #__user_usergroup_map WHERE user_id='$user_id')";
        $db->setQuery($query);
        if (!$db->query()) {
            $ret['success'] = false;
            $ret['message'] = $db->stderr();
            return $ret;
        }
        $ret['data'] = $db->loadResult();
        return $ret;
    }
    
    
    public function sendEmailToTrainers($client_id, $type, $subject, $contents) {
        $ret['success'] = 1;
        $trainers_data = $this->getClientTrainers($client_id, $type);
        if(!$trainers_data['status']['success']) {
            $ret['success'] = 0;
            $ret['message'] = $trainers_data['status']['message'];
            return $ret;
        }
        $trainers = $trainers_data['data'];
        
        $emails = array();
        
        foreach ($trainers as $trainer_id) {
            if(!$trainer_id) continue;
            
            $trainer_email = &JFactory::getUser($trainer_id)->email;
            
            $emails[] = $trainer_email;
                
            $send = $this->sendEmail($trainer_email, $subject, $contents);
            
            if($send != '1') {
                $ret['success'] = false;
                $ret['message'] =  'Email function error';
                return $ret;
            }
        }
        
        $ret['message'] =  $emails;
        
        return $ret;
    }
    
    public function sendEmailToOtherTrainers($client_id, $user_id, $subject, $contents) {
        $ret['success'] = 1;
        $all_trainers = $this->getClientTrainers($client_id, 'all');
        if(!$all_trainers['status']['success']) {
            $ret['success'] = 0;
            $ret['message'] = $all_trainers['status']['message'];
            return $ret;
        }
        $all_trainers = $all_trainers['data'];

        $other_trainers = array_diff($all_trainers, array($user_id));

        foreach ($other_trainers as $trainer_id) {
            if(!$trainer_id) continue;

            $trainer_email = &JFactory::getUser($trainer_id)->email;

            $send = $this->sendEmail($trainer_email, $subject, $contents);

            if($send != '1') {
                $ret['success'] = 0;
                $ret['message'] =  'Email function error';
                return $ret;
            }
        }
        return $ret;
    }
    
    
    public function sendEmailToClient($client_id, $subject, $contents) {
        $ret['success'] = 1;
        $client_email = &JFactory::getUser($client_id)->email;

        $send = $this->sendEmail($client_email, $subject, $contents);

        if($send != '1') {
            $ret['success'] = false;
            $ret['message'] = 'Email function error';
            return $ret;
        }
        
        $ret['message'] = array($client_email);
                
        return $ret;
    }
    
     public function checkUniqueTableItem($table, $column, $value) {
        $ret['success'] = 1;
        $db = JFactory::getDBO();
        $query = "SELECT * FROM $table WHERE $column='$value'";
        $db->setQuery($query);
        if (!$db->query()) {
            $ret['success'] = 0;
            $ret['message'] = $db->stderr();
        }
        $ret['data'] = $db->loadResult();
        return $ret;
    }
    
    
    public function getUsersByGroup($group_id) {
        $db = &JFactory::getDBo();
        $query = "SELECT u.id FROM #__users AS u 
            INNER JOIN #__user_usergroup_map AS g ON g.user_id=u.id WHERE g.group_id='$group_id' AND u.block='0'";
        $db->setQuery($query);
        $ret['success'] = 1;
        if (!$db->query()) {
            $ret['success'] = 0;
            $ret['message'] = $db->stderr();
            return $ret;
        }

        $clients= $db->loadResultArray(0);

        if(!$clients) {
            $ret['success'] = 0;
            $ret['message'] = 'No users assigned to this usergroup.';
            return $ret;
        }


        foreach ($clients as $user_id) {
            $user = &JFactory::getUser($user_id);
            $clients_name[] = $user->name;
        }
        
        $ret['data'] = array_combine($clients, $clients_name);
        
        return $ret;
    }
    
    
    public function getTrainersByUsergroup($trainers_group_id) {
        
        if(!$trainers_group_id) {
            $trainers_group_id = self::getTrainersGroupId();
        }
        $db = &JFactory::getDBo();
        $query = "SELECT id AS value, username AS text FROM #__users "
                . "INNER JOIN #__user_usergroup_map ON #__user_usergroup_map.user_id=#__users.id"
                . " WHERE #__user_usergroup_map.group_id='$trainers_group_id'";
        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $trainers = $db->loadObjectList();
        
        return $trainers;
    }
    
    public function getTrainersClientsTable($trainers_group_id) {
        
        if(!$trainers_group_id) {
            $trainers_group_id = self::getTrainersGroupId();
        }
        $db = &JFactory::getDBo();
        $query = "SELECT DISTINCT c.primary_trainer AS value, u.username AS text FROM #__fitness_clients AS c"
                . " LEFT JOIN #__users AS u on u.id=c.primary_trainer"
                . " INNER JOIN #__user_usergroup_map AS m ON m.user_id=u.id"
                . " WHERE c.state='1'";
        
        if(!self::is_superuser()) {
            $query .= " AND m.group_id='$trainers_group_id'";
        }


        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $trainers = $db->loadObjectList();
        
        return $trainers;
    }
    
    
    /**
     * 
     * @param type $items - options array
     * @param type $name - select tag name
     * @param type $id - select tag id
     * @param type $selected - option selected value
     * @param type $select - empty option name
     * @param type $required - 'true' if is field requered
     * @param type $class - select tag class
     * @return string
     */
    public function generateSelect($items, $name, $id, $selected, $select, $required, $class) {
        $html = '<select id="' . $id . '" name="' . $name . '" class="' . $class;
        
        if($required) {
            $html .= 'required="required"';
        }
        
        $html .=  '">';
        
        $html .= '<option value="">-Select ' . $select . '-</option>';
        $html .= JHtml::_('select.options', $items , 'value', 'text', $selected, true);
        $html .= '</select>';
        return $html;
    }
    
    function generateMultipleSelect($items, $name, $id, $selected, $select, $required, $class) {

        $selected = explode(',', $selected);
        $html = '<select size="10" id="' . $id . '" class="' . $class . '" multiple="multiple" name="' . $name . '[]">';
        $html .= '<option value="">none</option>';
        if(isset($items)) {
            foreach ($items as $item) {
                if(in_array($item->id, $selected)){
                    $selected_option = 'selected="selected"';
                } else {
                    $selected_option = '';
                }
                $html .= '<option ' . $selected_option . ' value="' . $item->id . '">' . $item->name . ' </option>';
            }
        }
        $html .= '</select>';
        
        return $html;
    }
    
    function getOtherTrainersSelect($item_id, $table, $trainers_group_id) {
        if(!$trainers_group_id) {
            $trainers_group_id = self::getTrainersGroupId();
        }
        $db = &JFactory::getDbo();
        $query = "SELECT id, username FROM #__users"
                . " INNER JOIN #__user_usergroup_map ON #__user_usergroup_map.user_id=#__users.id "
                . "WHERE #__user_usergroup_map.group_id='$trainers_group_id'";
        $db->setQuery($query);
        $result = $db->loadObjectList();
        $query = "SELECT other_trainers FROM $table WHERE id='$item_id'";
        $db->setQuery($query);
        if(!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $other_trainers = explode(',', $db->loadResult());
        $drawField = '<select size="10" id="jform_other_trainers" class="inputbox" multiple="multiple" name="jform[other_trainers][]">';
        $drawField .= '<option value="">none</option>';
        if(isset($result)) {
            foreach ($result as $item) {
                if(in_array($item->id, $other_trainers)){
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                $drawField .= '<option ' . $selected . ' value="' . $item->id . '">' . $item->username . ' </option>';
            }
        }
        $drawField .= '</select>';
        
         return $drawField;
    }
    
        
    public function getGroupList() {
        $db = JFactory::getDbo();
        $sql = 'SELECT id AS value, title AS text'. ' FROM #__usergroups' . ' ORDER BY id';
        $db->setQuery($sql);
        if(!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $grouplist = $db->loadObjectList();
        return $grouplist;
    }
    
    public function getBusinessProfileList() {
        $db = JFactory::getDbo();
        $sql = "SELECT id AS value, name AS text FROM #__fitness_business_profiles WHERE state='1' ";
        
        if(self::is_trainer()) {
            $trainers_group_id = self::getTrainersGroupId();
             $sql .= "  AND group_id='$trainers_group_id'";
        }
        
        $sql .= "  ORDER BY id";
        
        $db->setQuery($sql);
        if(!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $result = $db->loadObjectList();
        return $result;
    }
    
    public function getBusinessProfile($id) {
        $ret['success'] = 1;
        $db = JFactory::getDbo();
        $sql = "SELECT * FROM #__fitness_business_profiles WHERE id='$id' AND state='1'";
        $db->setQuery($sql);
        if(!$db->query()) {
            $ret['success'] = 0;
            $ret['message'] = $db->getErrorMsg();
        }
        $ret['data'] = $db->loadObject();
        
        return $ret;
    }
    
    public function getUserGroupByBusiness($business_profile_id) {
        $ret['success'] = 1;
        $db = JFactory::getDbo();
        $sql = "SELECT * FROM #__fitness_user_groups WHERE business_profile_id='$business_profile_id' AND state='1'";
        $db->setQuery($sql);
        if(!$db->query()) {
            $ret['success'] = 0;
            $ret['message'] = $db->getErrorMsg();
        }
        $ret['data'] = $db->loadObject();
        
        return $ret;
    }
    
    
    public function getBusinessByUserGroup($group_id) {
        $ret['success'] = 1;
        $db = JFactory::getDbo();
        $sql = "SELECT * FROM #__fitness_user_groups WHERE group_id='$group_id' AND state='1'";
        $db->setQuery($sql);
        if(!$db->query()) {
            $ret['success'] = 0;
            $ret['message'] = $db->getErrorMsg();
        }
        $ret['data'] = $db->loadObject();
        
        return $ret;
    }
    
    public function getRecipeTypes() {
        $ret['success'] = 1;
        $db = JFactory::getDbo();
        $sql = "SELECT id, name FROM #__fitness_recipe_types WHERE state='1'";
        $db->setQuery($sql);
        if(!$db->query()) {
            $ret['success'] = 0;
            $ret['message'] = $db->getErrorMsg();
        }
        $ret['data'] = $db->loadObjectList();
        
        return $ret;
    }
    
    
    
    
 
}

