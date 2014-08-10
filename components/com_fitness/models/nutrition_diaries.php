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

require_once  JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_fitness' . DS .'helpers' . DS . 'fitness.php';

/**
 * Methods supporting a list of Fitness records.
 */
class FitnessModelNutrition_diaries extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        $this->helper = new FitnessHelper();
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState($ordering = null, $direction = null) {

        // Initialise variables.
        $app = JFactory::getApplication();

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);

        $limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);
        
        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);
        

        // List state information.
        parent::populateState($ordering, $direction);
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

        $query->from('`#__fitness_nutrition_diary` AS a');

        // Filter by published state
        $published = $this->getState('filter.state');
        if(($published != '0') && ($published != '1') ) $published = '1';
        $query->where('a.state = '.(int) $published);
        
        $user = &JFactory::getUser();

        $query->where('a.client_id = '.(int) $user->id);
        
        $query->order('a.entry_date DESC');

        return $query;
    }

    public function getItems() {
        return parent::getItems();
    }
    
    function status_html($status) {
        switch($status) {
            case '1' :
                $class = 'status_inprogress';
                $text = 'IN PROGRESS';
                break;
            case '2' :
                $class = 'status_pass';
                $text = 'PASS';
                break;
            case '3' :
                $class = 'status_fail';
                $text = 'FAIL';
                break;
            case '4' :
                $class = 'status_distinction';
                $text = 'DISTINCTION';
                break;
            case '5' :
                $class = 'status_submitted';
                $text = 'SUBMITTED';
                break;
            default :
                $class = 'status_inprogress';
                $text = 'IN PROGRESS';
                break;
        }

        $html = '<div class="status_button ' . $class . '">' . $text . '</div>';

        return $html;
    }
    
     function status_html_stamp($status) {
        switch($status) {
            case '2' :
                $class = 'status_pass_stamp';
                $text = 'PASS';
                break;
            case '3' :
                $class = 'status_fail_stamp';

                break;
            case '4' :
                $class = 'status_distinction_stamp';
                break;
            case '5' :
                $class = 'status_submitted_stamp';
                break;
            default :
                break;
        }

        $html = '<div class=" status_button_stamp ' . $class . '"></div>';

        return $html;
    }
    
    
    public function updateDiary($table, $data_encoded) {
        $status['success'] = 1;

        $helper = $this->helper;
        
        $data = json_decode($data_encoded);
        
        $id_list = $data->ids;
        
        $ids = explode(",", $id_list);
        
        unset($data->ids);

        foreach ($ids as $id) {
            $data->id = $id;
            try {
                $helper->insertUpdateObj($data, $table);
            } catch (Exception $e) {
                $status['success'] = 0;
                $status['message'] = '"' . $e->getMessage() . '"';
                return array( 'status' => $status);
            }
    
        }
        
        $result = array( 'status' => $status, 'data' => $id_list);
        
        return $result;
    }
    
    public function deleteDiary($table, $data_encoded) {
        $status['success'] = 1;
        
        $data = json_decode($data_encoded);
        
        $id_list = $data->ids;
        
        $db = $this->getDbo();
        
        $query = "DELETE FROM $table WHERE id IN ($id_list)";
        
        $db->setQuery($query);
        if (!$db->query()) {
            $status['success'] = false;
            $status['message'] = $db->stderr();
            return $ret;
        }
        
        $result = array( 'status' => $status, 'data' => $id_list);
        
        return $result;
    }
    
    public function getDiaryDays() {

        $helper = $this->helper;
        
        $user = &JFactory::getUser();
 
        $user_id = $user->id;
        
        $client_id = JRequest::getVar('client_id');
        
        if(!$client_id) {
            $client_id = $user_id;
        }
        
        if(!$client_id) {
            throw new Exception('No client_id'); 
        }
        
        $query = "SELECT entry_date FROM #__fitness_nutrition_diary WHERE client_id='$client_id' AND state='1'";
        
        $data = FitnessHelper::customQuery($query, 3);
        
        return $data;
    }
    
    
    public function getActivePlanData() {
        $user = &JFactory::getUser();
 
        $user_id = $user->id;
        
        $client_id = JRequest::getVar('client_id');
        
        if(!$client_id) {
            $client_id = $user_id;
        }
        
        if(!$client_id) {
            throw new Exception('No client_id'); 
        }
        
        require_once JPATH_COMPONENT_ADMINISTRATOR .  '/models/nutrition_plans.php';
        $nutrition_plans_model  = new FitnessModelnutrition_plans();
        
        $active_plan_id = $nutrition_plans_model->getUserActivePlanId($user_id);

        
        if(!$active_plan_id) {
            throw new Exception('No Active Plan'); 
        }

        $helper = $this->helper;
        
        $active_plan_data = $helper->getPlanData($active_plan_id);

        return $active_plan_data;
    }
    
    
    function getNutritionTarget($table, $data_encoded) {
        $status['success'] = 1;
        
        $data = json_decode($data_encoded);
        
        $nutrition_plan_id = $data->nutrition_plan_id;
        $type = $data->type;
        
        $query = "SELECT * FROM #__fitness_nutrition_plan_targets WHERE
            nutrition_plan_id='$nutrition_plan_id'
            AND type='$type'";
  
        
        try {
            $data = FitnessHelper::customQuery($query, 2);
        } catch (Exception $e) {
            $status['success'] = 0;
            $status['message'] = '"' . $e->getMessage() . '"';
            return array( 'status' => $status);
        }
        
        $result = array( 'status' => $status, 'data' => $data);
        
        return $result;
    }
    
    public function updateDiaryItem($table, $data_encoded) {
        $status['success'] = 1;

        $helper = $this->helper;
        
        $data = json_decode($data_encoded);
          
        try {
            $helper->insertUpdateObj($data, $table);
        } catch (Exception $e) {
            $status['success'] = 0;
            $status['message'] = '"' . $e->getMessage() . '"';
            return array( 'status' => $status);
        }
        
        $result = array( 'status' => $status, 'data' => $data->id);
        
        return $result;
    }
    
    
    public function getDiaryItem($table, $data_encoded) {
        $status['success'] = 1;

        $data = json_decode($data_encoded);
        
        $id = $data->id;
        
        $query = "SELECT a.*,"
                . " (SELECT name FROM #__users WHERE id=a.client_id) client_name,"
                . " (SELECT name FROM #__users WHERE id=a.trainer_id) trainer_name,"
                . " (SELECT name FROM #__users WHERE id=a.assessed_by) assessed_by_name,"
                . " (SELECT name FROM #__fitness_nutrition_focus WHERE id=a.nutrition_focus) nutrition_focus_name"
                . " FROM $table AS a"
                . " WHERE id='$id'";
     

        try {
            $item = FitnessHelper::customQuery($query, 2);
        } catch (Exception $e) {
            $status['success'] = 0;
            $status['message'] = '"' . $e->getMessage() . '"';
            return array( 'status' => $status);
        }
        
        $helper = $this->helper;
        
        
        try {
            $secondary_trainers = $helper->get_client_trainers_names($item->client_id, 'secondary');
            $item->secondary_trainers = $secondary_trainers;
        } catch (Exception $e) {
            $status['success'] = 0;
            $status['message'] = '"' . $e->getMessage() . '"';
            return array( 'status' => $status);
        }

        $result = array( 'status' => $status, 'data' => $item);
        
        return $result;
    }
    
    
     public function saveAsRecipe($table, $data_encoded) {
        $status['success'] = 1;
        
        $helper = $this->helper;
        
        $data = json_decode($data_encoded);

        $meal_id = $data->meal_id;
        $type = $data->type;
        
        $user = &JFactory::getUser();


        // save recipe 
        $created = FitnessHelper::getTimeCreated();
            
        $recipe->id = null;
        $recipe->status = '1';
        $recipe->created_by = $user->id;
        $recipe->created = $created;
        $recipe->assessed_by = null;
        
 
        $recipe->recipe_name = $data->recipe_name;
        $recipe->recipe_type = $data->recipe_type;
        $recipe->recipe_variation = $data->recipe_variation;
        $recipe->number_serves = $data->number_serves;
   
        
        try {
            $new_recipe_id = $helper->insertUpdateObj($recipe, '#__fitness_nutrition_recipes');
        } catch (Exception $e) {
            $status['success'] = 0;
            $status['message'] = '"' . $e->getMessage() . '"';
            return array( 'status' => $status);
        }
        
        
        

        
        // get recipe meals
        try {
            $recipe_meals = $helper->getDiaryIngredients($meal_id, $type);
        } catch (Exception $e) {
            $status['success'] = 0;
            $status['message'] = '"' . $e->getMessage() . '"';
            return array( 'status' => $status);
        }
  

        // save recipe meals

        foreach ($recipe_meals as $meal) {
            try {
                unset($meal->nutrition_plan_id);
                unset($meal->meal_id);
                unset($meal->type);
                $meal->id = null;
                $meal->recipe_id = $new_recipe_id;
                $inserted_id = $helper->insertUpdateObj($meal, '#__fitness_nutrition_recipes_meals');
            } catch (Exception $e) {
                $status['success'] = 0;
                $status['message'] = '"' . $e->getMessage() . '"';
                return array( 'status' => $status);
            }
        }
        
        $result = array( 'status' => $status, 'data' => $recipe);
        
        return $result;
    }
    
    public function diaries() {
            
        $method = JRequest::getVar('_method');

        if(!$method) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        $model = json_decode(JRequest::getVar('model'));
        
        $id = JRequest::getVar('id', 0, '', 'INT');

        $table = '#__fitness_nutrition_diary';

        $helper = new FitnessHelper();

        switch ($method) {
            case 'GET': // Get Item(s)
                $sort_by = JRequest::getVar('sort_by'); 
                $order_dirrection = JRequest::getVar('order_dirrection'); 
                $page = JRequest::getVar('page'); 
                $limit = JRequest::getVar('limit'); 
                $state = JRequest::getVar('state'); 

                $start = ($page - 1) * $limit;


                $user = &JFactory::getUser();
                $user_id = $user->id;

                $query .= " SELECT a.*, u.name AS assessed_by_name,"
         
                . " (SELECT name FROM #__users WHERE id=a.client_id) client_name,"
                . " (SELECT name FROM #__users WHERE id=a.trainer_id) trainer_name,"
                . " (SELECT name FROM #__users WHERE id=a.assessed_by) assessed_by_name,"
                . " (SELECT name FROM #__fitness_nutrition_focus WHERE id=a.nutrition_focus) nutrition_focus_name, ";
                
                //get total number
                $query .= " (SELECT COUNT(*) FROM $table AS a ";
                $query .= " WHERE a.client_id='$user_id' ";
        
                if($state) {
                    $query .= " AND a.state='$state'";
                }
                $query .= " ) items_total ";
                //
                $query .= " FROM $table AS a";
                $query .= " LEFT JOIN #__users AS u ON u.id=a.assessed_by";
                $query .= " WHERE a.client_id='$user_id' ";
                
                if($id) {
                    $query .= " AND a.id='$id' ";
                }
                
                if($state) {
                    $query .= " AND a.state='$state' ";
                }
                
                if($sort_by) {
                    $query .= " ORDER BY " . $sort_by;
                }
                
                if($order_dirrection) {
                    $query .=  " " . $order_dirrection;
                }
                
                if($limit) {
                    $query .= " LIMIT $start, $limit";
                }

                $query_method = 1;
                
                if($id) {
                    $query_method = 2;
                }
                
                $data = FitnessHelper::customQuery($query, $query_method);
                

                if(!$id) {
                    $i = 0;
                    foreach ($data as $item) {
                        $client_trainers = $helper->get_client_trainers_names($data->client_id, 'secondary');

                        $data[$i]->secondary_trainers = $client_trainers;

                        $i++;
                    }
                } else {
                    $client_trainers = $helper->get_client_trainers_names($data->client_id, 'secondary');

                    $data->secondary_trainers = $client_trainers;
                }

                return $data;
                break;
            case 'PUT': 
                //update
                $id = $helper->insertUpdateObj($model, $table);
                break;
            case 'POST': // Create
                $id = $helper->insertUpdateObj($model, $table);
                break;
            case 'DELETE': // Delete Item
                $id = JRequest::getVar('id', 0, '', 'INT');
                $id = $helper->deleteRow($id, $table);
                break;

            default:
                break;
        }

        $model->id = $id;

        return $model;
    }
    
    public function meal_entries() {
        $method = JRequest::getVar('_method');

        if(!$method) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        $model = json_decode(JRequest::getVar('model'));
        
        $id = JRequest::getVar('id', 0, '', 'INT');

        $table = '#__fitness_nutrition_diary_meal_entries';

        $helper = new FitnessHelper();

        switch ($method) {
            case 'GET': // Get Item(s)
                $nutrition_plan_id = JRequest::getVar('nutrition_plan_id'); 
                $diary_id = JRequest::getVar('diary_id'); 
                
                $query .= "SELECT a.* FROM $table AS a";
                
                $query .= " WHERE 1 ";
   
                if($id) {
                    $query .= " AND a.id='$id' ";
                }
                
                if($nutrition_plan_id) {
                    $query .= " AND a.nutrition_plan_id='$nutrition_plan_id' ";
                }
                
                if($diary_id) {
                    $query .= " AND a.diary_id='$diary_id' ";
                }
                
                $query .= " ORDER BY a.meal_time";
               
                $query_method = 1;
                
                if($id) {
                    $query_method = 2;
                }
                
                $data = FitnessHelper::customQuery($query, $query_method);

                return $data;
                break;
            case 'PUT': 
                //update
                $id = $helper->insertUpdateObj($model, $table);
                break;
            case 'POST': // Create
                $id = $helper->insertUpdateObj($model, $table);
                break;
            case 'DELETE': // Delete Item
                $id = JRequest::getVar('id', 0, '', 'INT');
                $id = $helper->deleteRow($id, $table);
                break;

            default:
                break;
        }

        $model->id = $id;

        return $model;
    }
    
    public function diary_meals() {
        $method = JRequest::getVar('_method');

        if(!$method) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        $model = json_decode(JRequest::getVar('model'));
        
        $id = JRequest::getVar('id', 0, '', 'INT');

        $table = '#__fitness_nutrition_diary_meals';

        $helper = new FitnessHelper();

        switch ($method) {
            case 'GET': // Get Item(s)
                $nutrition_plan_id = JRequest::getVar('nutrition_plan_id'); 
                $diary_id = JRequest::getVar('diary_id'); 
                $meal_entry_id = JRequest::getVar('meal_entry_id'); 
                
                $query .= "SELECT a.* FROM $table AS a";
                
                $query .= " WHERE 1 ";
   
                if($id) {
                    $query .= " AND a.id='$id' ";
                }
                
                if($nutrition_plan_id) {
                    $query .= " AND a.nutrition_plan_id='$nutrition_plan_id' ";
                }
                
                if($diary_id) {
                    $query .= " AND a.diary_id='$diary_id' ";
                }
                
                if($meal_entry_id) {
                    $query .= " AND a.meal_entry_id='$meal_entry_id' ";
                }

               
                $query_method = 1;
                
                if($id) {
                    $query_method = 2;
                }
                
                $data = FitnessHelper::customQuery($query, $query_method);

                return $data;
                break;
            case 'PUT': 
                //update
                $id = $helper->insertUpdateObj($model, $table);
                break;
            case 'POST': // Create
                $id = $helper->insertUpdateObj($model, $table);
                break;
            case 'DELETE': // Delete Item
                $id = JRequest::getVar('id', 0, '', 'INT');
                $id = $helper->deleteRow($id, $table);
                break;

            default:
                break;
        }

        $model->id = $id;

        return $model;
    }
}
