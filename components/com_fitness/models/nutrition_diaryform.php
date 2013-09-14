<?php
/**
 * @version     1.0.0
 * @package     com_fitness
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nikolay Korban <niklug@ukr.net> - http://
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Fitness model.
 */
class FitnessModelNutrition_diaryForm extends JModelForm
{
    
    var $_item = null;
    
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_fitness');

		// Load state from the request userState on edit or from the passed variable on default
        if (JFactory::getApplication()->input->get('layout') == 'edit') {
            $id = JFactory::getApplication()->getUserState('com_fitness.edit.nutrition_diary.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_fitness.edit.nutrition_diary.id', $id);
        }
		$this->setState('nutrition_diary.id', $id);

		// Load the parameters.
        $params = $app->getParams();
        $params_array = $params->toArray();
        if(isset($params_array['item_id'])){
            $this->setState('nutrition_diary.id', $params_array['item_id']);
        }
		$this->setState('params', $params);

	}
        

	/**
	 * Method to get an ojbect.
	 *
	 * @param	integer	The id of the object to get.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id)) {
				$id = $this->getState('nutrition_diary.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
                
                $user = JFactory::getUser();
                $id = $table->id;
                $canEdit = $user->authorise('core.edit', 'com_fitness') || $user->authorise('core.create', 'com_fitness');
                if (!$canEdit && $user->authorise('core.edit.own', 'com_fitness')) {
                    $canEdit = $user->id == $table->created_by;
                }

                if (!$canEdit) {
                    JError::raiseError('500', JText::_('JERROR_ALERTNOAUTHOR'));
                }
                
				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published) {
						return $this->_item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties = $table->getProperties(1);
				$this->_item = JArrayHelper::toObject($properties, 'JObject');
			} elseif ($error = $table->getError()) {
				$this->setError($error);
			}
		}

		return $this->_item;
	}
    
	public function getTable($type = 'Nutrition_diary', $prefix = 'FitnessTable', $config = array())
	{   
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        return JTable::getInstance($type, $prefix, $config);
	}     

    
	/**
	 * Method to check in an item.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int)$this->getState('nutrition_diary.id');

		if ($id) {
            
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
            if (method_exists($table, 'checkin')) {
                if (!$table->checkin($id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int)$this->getState('nutrition_diary.id');

		if ($id) {
            
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
            if (method_exists($table, 'checkout')) {
                if (!$table->checkout($user->get('id'), $id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
		}

		return true;
	}    
    
	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML 
     * 
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fitness.nutrition_diary', 'nutrition_diaryform', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_fitness.edit.nutrition_diary.data', array());
        if (empty($data)) {
            $data = $this->getData();
        }
        
        return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array		The form data.
	 * @return	mixed		The user id on success, false on failure.
	 * @since	1.6
	 */
	public function save($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('nutrition_diary.id');
        $state = (!empty($data['state'])) ? 1 : 0;
        $user = JFactory::getUser();

        if($id) {
            //Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_fitness') || $authorised = $user->authorise('core.edit.own', 'com_fitness');
            if($user->authorise('core.edit.state', 'com_fitness') !== true && $state == 1){ //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        } else {
            //Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_fitness');
            if($user->authorise('core.edit.state', 'com_fitness') !== true && $state == 1){ //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        }

        if ($authorised !== true) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
        
        $db = JFactory::getDbo();

        $table = '#__fitness_nutrition_diary';
        
        $object = new stdClass();

        foreach ($data as $key => $value)
        {
            $object->$key = $value;
        }

        if($object->id) {
            $insert = $db->updateObject($table, $object, 'id');
        } else {
            $insert = $db->insertObject($table, $object, 'id');
        }
        
        if (!$insert) {
            JError::raiseError($db->getErrorMsg());
        }
        
        $inserted_id = $db->insertid();
        
        if(!$inserted_id) {
            $inserted_id = $data['id'];
        }


            if ($inserted_id) {
                return $inserted_id;
            } else {
                return false;
            }
   
	}
    
     function delete($data)
    {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('nutrition_diary.id');
        if(JFactory::getUser()->authorise('core.delete', 'com_fitness') !== true){
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
        $table = $this->getTable();
        if ($table->delete($data['id']) === true) {
            return $id;
        } else {
            return false;
        }
        
        return true;
    }
    
    

    function  get_own_trainers() {
        $db = & JFactory::getDBO();
        $user = &JFactory::getUser();
        $user_id = $user->id;
        $query = "SELECT  other_trainers FROM #__fitness_clients WHERE user_id='$user_id' AND state='1'";
        $db->setQuery($query);
        if(!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        $other_trainers = $db->loadResultArray(0);
        $all_trainers_id = explode(',', $other_trainers[0]);
        if(!$all_trainers_id[0]) return;
        foreach ($all_trainers_id as $user_id) {
            $user = &JFactory::getUser($user_id);
            $all_trainers_name[] = $user->name;
        }

        $result = array_combine($all_trainers_id, $all_trainers_name);
        
        return $result;
    }
    
    function getActivePlanData() {
        $user = &JFactory::getUser();
        $user_id = $user->id;
        if(!$user_id) return;
        require_once JPATH_COMPONENT_ADMINISTRATOR .  '/models/nutrition_plans.php';
        $nutrition_plans_model  = new FitnessModelnutrition_plans();
        
        $active_plan_id = $nutrition_plans_model->getUserActivePlanId($user_id);
        
        if(!$active_plan_id) return;
        
        $active_plan_data = $this->getPlanData($active_plan_id);
        
        return $active_plan_data;
    }
    
    function getPlanData($id) {
        $db = & JFactory::getDBO();
        $query = "SELECT a.*, gc.id AS primary_goal_id
            FROM #__fitness_nutrition_plan AS a
            LEFT JOIN #__fitness_goals AS g ON g.id = a.primary_goal
            LEFT JOIN #__fitness_goal_categories AS gc  ON g.goal_category_id=gc.id
              
            WHERE a.id='$id' AND a.state='1'";
        $db->setQuery($query);
        if(!$db->query()) {
            JError::raiseError($db->getErrorMsg());
        }
        return $db->loadObject();
    }
    
     function getGoalName($id) {
            $db = JFactory::getDbo();
            $sql = "SELECT name FROM #__fitness_goal_categories WHERE id='$id' AND state='1'";
            $db->setQuery($sql);
            if(!$db->query()) {
                JError::raiseError($db->getErrorMsg());
            }
            $result = $db->loadResult();
            return $result;
    }
    
    function getTrainingPeriodName($id) {
            $db = JFactory::getDbo();
            $sql = "SELECT name FROM #__fitness_training_period WHERE id='$id' AND state='1'";
            $db->setQuery($sql);
            if(!$db->query()) {
                JError::raiseError($db->getErrorMsg());
            }
            $result = $db->loadResult();
            return $result;
    }
    
    function getNutritionFocusName($id) {
            $db = JFactory::getDbo();
            $sql = "SELECT name FROM #__fitness_nutrition_focus WHERE id='$id' AND state='1'";
            $db->setQuery($sql);
            if(!$db->query()) {
                JError::raiseError($db->getErrorMsg());
            }
            $result = $db->loadResult();
            return $result;
    }
    
    function getNutritionTarget($nutrition_plan_id, $type) {
            $db = JFactory::getDbo();
            $sql = "SELECT * FROM #__fitness_nutrition_plan_targets WHERE
                nutrition_plan_id='$nutrition_plan_id'
                AND type='$type'";
            $db->setQuery($sql);
            if(!$db->query()) {
                JError::raiseError($db->getErrorMsg());
            }
            $result = $db->loadObject();
            return json_encode($result);
    }
}