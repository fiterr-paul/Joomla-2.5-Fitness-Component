<?php

/**
 * @version     1.0.0
 * @package     com_fitness
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nikolay Korban <niklug@ukr.net> - http://
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Fitness records.
 */
class FitnessModelclients extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'user_id', 'a.user_id',
                'state', 'a.state',
                'primary_trainer', 'a.primary_trainer',
                'other_trainers', 'a.other_trainers',
                'g.group_id', 'u.name', 'u.username',
                'u.email'

            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);
        
        // Filter by primary trainer
        $primary_trainer = $app->getUserStateFromRequest($this->context . '.filter.primary_trainer', 'filter_primary_trainer', '', 'string');
        $this->setState('filter.primary_trainer', $primary_trainer);
        
                
        // Filter by group
        $group = $app->getUserStateFromRequest($this->context . '.filter.group', 'filter_group', '', 'string');
        $this->setState('filter.group', $group);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_fitness');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.user_id', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     * @return	string		A store id.
     * @since	1.6
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');
        $id.= ':' . $this->getState('filter.primary_trainer');
        $id.= ':' . $this->getState('filter.group');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(     'a.id, u.name, u.username, g.group_id,  ug.title as usergroup, u.email, a.primary_trainer, a.state' );
        

        $query->from('`#__fitness_clients` AS a');
        
        $query->leftJoin('#__users AS u ON u.id = a.user_id');
        
        $query->leftJoin('#__user_usergroup_map AS g ON u.id = g.user_id');
        
        $query->leftJoin('#__usergroups AS ug ON ug.id = g.group_id');
        
        // filter only for Super Users
        $user = &JFactory::getUser();
        if ($this->getUserGroup($user->id) != 'Super Users') {
            $other_trainers = $db->Quote('%' . $db->escape($user->id, true) . '%');
            $query->where('(a.primary_trainer = ' . (int) $user->id . ' OR a.other_trainers LIKE ' . $other_trainers . ' )');
        }

        
        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.state = '.(int) $published);
        } else if ($published === '') {
            $query->where('(a.state IN (0, 1))');
        }


        // Filter by primary trainer
        $primary_trainer = $this->getState('filter.primary_trainer');
        if (is_numeric($primary_trainer)) {
            $query->where('a.primary_trainer = '.(int) $primary_trainer);
        } 
        
        


            // Filter by group
        $group = $this->getState('filter.group');
        if (is_numeric($group)) {
            $query->where('g.group_id = '.(int) $group);
        } 

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(
                    u.username LIKE '.$search.' 
                    OR  u.name LIKE '.$search.' 
                 )');
            }
            
            

            
        }

        


        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    // Get the items, and change the TAG ID FOR TAG NAMES OVER EACH TAG
    public function getItems() {
        $items = parent::getItems();

        

        return $items;
    }
    
    function getUserGroup($user_id) {
        $db = JFactory::getDBO();
        $query = "SELECT title FROM #__usergroups WHERE id IN 
            (SELECT group_id FROM #__user_usergroup_map WHERE user_id='$user_id')";
        $db->setQuery($query);
        return $db->loadResult();
    }

}
