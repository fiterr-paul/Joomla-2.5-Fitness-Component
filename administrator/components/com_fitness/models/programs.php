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
class FitnessModelprograms extends JModelList {

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
                'starttime', 'a.starttime',
                'client_id', 'a.client_id',
                'primary_trainer', 'a.trainer_id',
                'location', 'a.location',
                'category', 'a.title',
                'session_type', 'a.session_type',
                'session_focus', 'a.session_focus',
                'event_status', 'a.status',
                'frontend_published', 'a.frontend_published',
                'published', 'a.published',
                'calid', 'a.calid',
                'endtime', 'a.endtime',
                'description', 'a.description',
                'isalldayevent', 'a.isalldayevent',
                'color', 'a.color',
                'owner', 'a.owner',
                'rrule', 'a.rrule',
                'uid', 'a.uid',
                'exdate', 'a.exdate',

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

        // Filter by published
        $published = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
        $this->setState('filter.published', $published);
        
        // Filter by frontend published
        $frontend_published = $app->getUserStateFromRequest($this->context . '.filter.frontend_published', 'filter_frontend_published', '', 'string');
        $this->setState('filter.frontend_published', $frontend_published);

        //Filtering date
        $this->setState('filter.date.from', $app->getUserStateFromRequest($this->context.'.filter.date.from', 'filter_from_date', '', 'string'));
        $this->setState('filter.date.to', $app->getUserStateFromRequest($this->context.'.filter.date.to', 'filter_to_date', '', 'string'));

        // Filter by primary trainer
        $primary_trainer = $app->getUserStateFromRequest($this->context . '.filter.primary_trainer', 'filter_primary_trainer', '', 'string');
        $this->setState('filter.primary_trainer', $primary_trainer);
        
        // Filter by location
        $location = $app->getUserStateFromRequest($this->context . '.filter.location', 'filter_location', '', 'string');
        $this->setState('filter.location', trim($location));
        
        // Filter by category
        $category = $app->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '', 'string');
        $this->setState('filter.category', $category);

        // Filter by session type
        $session_type = $app->getUserStateFromRequest($this->context . '.filter.session_type', 'filter_session_type', '', 'string');
        $this->setState('filter.session_type', $session_type);
        
        // Filter by session focus
        $session_focus = $app->getUserStateFromRequest($this->context . '.filter.session_focus', 'filter_session_focus', '', 'string');
        $this->setState('filter.session_focus', $session_focus);
        
        // Filter by event status
        $event_status = $app->getUserStateFromRequest($this->context . '.filter.event_status', 'filter_event_status', '', 'string');
        $this->setState('filter.event_status', $event_status);
        
                 
        // Load the parameters.
        $params = JComponentHelper::getParams('com_fitness');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.starttime', 'asc');
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
        $id.= ':' . $this->getState('filter.published');

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
        $query->select(
                $this->getState(
                        'list.select', 'a.*'
                )
        );
        $query->from('`#__dc_mv_events` AS a');
        
        $query->leftJoin('#__users AS u ON u.id = a.client_id');

        // filter only for Super Users
        $user = &JFactory::getUser();
        if ($this->getUserGroup($user->id) != 'Super Users') {
            $query->where('a.trainer_id = ' . (int) $user->id);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');

                $query->where('
                    u.name LIKE ' . $search . '
                        or a.id IN
                        ( 
                            SELECT  DISTINCT event_id FROM #__fitness_appointment_clients WHERE client_id IN 
                                (
                                    SELECT id FROM #__users WHERE name LIKE ' . $search . '
                                )
                        ) 
                ');
            }
        }

        $query->where("a.title NOT IN ('Assessment')");
         
        //Filtering date
        $filter_date_from = $this->state->get("filter.date.from");
        if ($filter_date_from) {
                $query->where("a.starttime >= '".$db->escape($filter_date_from)."'");
        }
        $filter_date_to = $this->state->get("filter.date.to");
        if ($filter_date_to) {
                $query->where("a.starttime <= '".$db->escape($filter_date_to)."'");
        }
        
        // Filter by primary trainer
        $primary_trainer = $this->getState('filter.primary_trainer');
        if (is_numeric($primary_trainer)) {
            $query->where('a.trainer_id = '.(int) $primary_trainer);
        } 
        
                
        // Filter by location
        $location = $this->getState('filter.location');
        if ($location) {
           $query->where("a.location = ".$db->Quote($location));
        } 
        
        // Filter by category
        $category= $this->getState('filter.category');
        if ($category) {
           $query->where("a.title = ".$db->Quote($category));
        } 
          
        
        // Filter by session type
        $session_type = $this->getState('filter.session_type');
        if ($session_type) {
           $query->where("a.session_type = ".$db->Quote($session_type));
        } 
             
        // Filter by session focus
        $session_focus = $this->getState('filter.session_focus');
        if ($session_focus) {
           $query->where("a.session_focus = ".$db->Quote($session_focus));
        } 
        
        
        // Filter by event status
        $event_status = $this->getState('filter.event_status');
        if (is_numeric($event_status)) {
            $query->where('a.status = '.(int) $event_status);
        } 
        
        // Filter by event published
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.published = '.(int) $published);
        } 
        
        
        // Filter by event frontend published
        $frontend_published = $this->getState('filter.frontend_published');
        if (is_numeric($frontend_published)) {
            $query->where('a.frontend_published = '.(int) $frontend_published);
        } 
        
        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

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
