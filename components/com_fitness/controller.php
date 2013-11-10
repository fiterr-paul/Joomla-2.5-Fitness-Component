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

jimport('joomla.application.component.controller');

class FitnessController extends JController {

    public function __construct() {
        parent::__construct();

        //connect administrator models
        require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'models' . DS . 'nutrition_plan.php';
        $this->admin_nutrition_plan_model = new FitnessModelnutrition_plan();
        
        require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'models' . DS . 'nutrition_recipe.php';
        $this->admin_nutrition_recipe_model = new FitnessModelnutrition_recipe();
    }

    public function display($tpl = null) {
        $user = JFactory::getUser();

        if ($user->guest) {
            $this->setRedirect(JRoute::_(JURI::base() . 'index.php', false));
            $this->setMessage('Login please to proceed');
            return false;
        }
        parent::display();
    }
    
    //nutrition_recipe
    function getSearchIngredients() {
        $search_text = JRequest::getVar('search_text');
        
        echo $this->admin_nutrition_recipe_model->getSearchIngredients($search_text);
    }


    function getIngredientData() {
        $id = JRequest::getVar('id');
        
        echo $this->admin_nutrition_recipe_model->getIngredientData($id);
    }

    function saveMeal() {
        $ingredient_encoded = JRequest::getVar('ingredient_encoded');
        
        echo $this->admin_nutrition_recipe_model->saveMeal($ingredient_encoded);
    }


     function deleteMeal() {
        $id= JRequest::getVar('id');
        
        echo $this->admin_nutrition_recipe_model->deleteMeal($id);
    }


     function populateTable() {
        $recipe_id = JRequest::getVar('recipe_id');
        
        echo $this->admin_nutrition_recipe_model->populateTable($recipe_id);
    }
    // end nutrition_recipe

    // nutrition plan

    function getTargetsData() {
        $data_encoded = JRequest::getVar('data_encoded');
        echo $this->admin_nutrition_plan_model->getTargetsData($data_encoded);
    }

    function saveIngredient() {
        $table = JRequest::getVar('table');
        $ingredient_encoded = JRequest::getVar('ingredient_encoded');
        
        echo $this->admin_nutrition_plan_model->saveIngredient($ingredient_encoded, $table);
    }

    function deleteIngredient() {
        $table = JRequest::getVar('table');
        $id = JRequest::getVar('id');
        
        echo $this->admin_nutrition_plan_model->deleteIngredient($id, $table);
    }

    function populateItemDescription() {
        $table = JRequest::getVar('table');
        $data_encoded = JRequest::getVar('data_encoded','','POST','STRING',JREQUEST_ALLOWHTML);
        echo $this->admin_nutrition_plan_model->populateItemDescription($data_encoded, $table);
    }

    function savePlanMeal() {
        $table = JRequest::getVar('table');
        $meal_encoded = JRequest::getVar('meal_encoded');
        
        echo $this->admin_nutrition_plan_model->savePlanMeal($meal_encoded, $table);
    }

    function deletePlanMeal() {
        $table = JRequest::getVar('table');
        $id = JRequest::getVar('id');
        
        echo $this->admin_nutrition_plan_model->deletePlanMeal($id, $table);
    }

    function populatePlanMeal() {
        $table = JRequest::getVar('table');
        $nutrition_plan_id = JRequest::getVar('nutrition_plan_id');
        
        echo $this->admin_nutrition_plan_model->populatePlanMeal($nutrition_plan_id, $table);
    }

    function savePlanComment() {
        $table = JRequest::getVar('table');
        $data_encoded = JRequest::getVar('data_encoded','','POST','STRING',JREQUEST_ALLOWHTML);
        echo $this->admin_nutrition_plan_model->savePlanComment($data_encoded, $table);
    }

    function deletePlanComment() {
        $table = JRequest::getVar('table');
        $id = JRequest::getVar('id');
        echo $this->admin_nutrition_plan_model->deletePlanComment($id, $table);
    }

    function populatePlanComments() {
        $table = JRequest::getVar('table');
        $item_id = JRequest::getVar('item_id');
        $sub_item_id = JRequest::getVar('sub_item_id');
        echo $this->admin_nutrition_plan_model->populatePlanComments($item_id, $sub_item_id, $table);
    }

    function importRecipe() {
        $table = JRequest::getVar('table');
        $data_encoded = JRequest::getVar('data_encoded');
        
        echo $this->admin_nutrition_plan_model->importRecipe($data_encoded, $table);
    }

    function saveShoppingItem() {
        $data_encoded = JRequest::getVar('data_encoded');
        
        echo $this->admin_nutrition_plan_model->saveShoppingItem($data_encoded);
    }

    function deleteShoppingItem() {
        $id = JRequest::getVar('id');
        
        echo $this->admin_nutrition_plan_model->deleteShoppingItem($id);
    }

    function getShoppingItemData() {
        $nutrition_plan_id = JRequest::getVar('nutrition_plan_id');
        
        echo $this->admin_nutrition_plan_model->getShoppingItemData($nutrition_plan_id);
    }
    // end nutrition plan
    
    
    // goals
    function addGoal() {
        $view = $this -> getView('goals_periods', 'json');
        $view->setModel($this->getModel('goals_periods'));
        $view -> addGoal();
    }
    
    function populateGoals() {
        $view = $this -> getView('goals_periods', 'json');
        $view->setModel($this->getModel('goals_periods'));
        $view -> populateGoals(); 
    }
    
    function checkOverlapDate() {
        $view = $this -> getView('goals_periods', 'json');
        $view->setModel($this->getModel('goals_periods'));
        $view -> checkOverlapDate(); 
    }
    
   function commentEmail() {
        $view = $this -> getView('goals_periods', 'json');
        $view->setModel($this->getModel('goals_periods'));
        $view -> commentEmail();
    }
    
    function getClientsByBusiness() {
        $view = $this -> getView('goals_periods', 'json');
        $view->setModel($this->getModel('goals_periods'));
        $view -> getClientsByBusiness();
    }
    
    function onBusinessNameChange() {
            $view = $this -> getView('goals_periods', 'json');
            $view->setModel($this->getModel('goals_periods'));
            $view -> onBusinessNameChange();
	}
    
    // nutrition plan
    function populatePlan() {
        $view = $this -> getView('nutrition_planning', 'json');
        $view->setModel($this->getModel('goals_periods'));
        $view -> populatePlan(); 
    }
    
    // recipe database
    function getRecipes() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> getRecipes(); 
    }
    
    function getRecipe() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> getRecipe(); 
    }
    
    
    function getRecipeTypes() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> getRecipeTypes(); 
    }
    
    function copyRecipe() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> copyRecipe(); 
    }
    
    function addFavourite() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> addFavourite(); 
    }
    
    function removeFavourite() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> removeFavourite(); 
    }
    
    function deleteRecipe() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> deleteRecipe(); 
    }
    
    function updateRecipe() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> updateRecipe(); 
    }
    
    function uploadImage() {
        $filename = $_FILES['file']['name'];

        $upload_folder = $_GET['upload_folder'];

        $task = $_POST['method'];


        if($task == 'clear') {
            $filename = $_POST['filename'];
            unlink($upload_folder . $filename);
            echo $filename;
            return false;
        }


        if($_FILES['file']['size']/1024 > 5024) {
            echo 'too big file'; 
            header("HTTP/1.0 404 Not Found");
            return false;
        }

        $fileType="";

        if(strstr($_FILES['file']['type'],"jpeg")) $fileType="jpg";

        if(strstr($_FILES['file']['type'],"png")) $fileType="png";

        if(strstr($_FILES['file']['type'],"gif")) $fileType="gif";

        if(strstr($_FILES['file']['type'],"gif")) $fileType="bmp";

        if(strstr($_FILES['file']['type'],"gif")) $fileType="jpeg";


        if (!$fileType) {
            echo 'Invalid file type';
            header("HTTP/1.0 404 Not Found");
            return false;
        } 

        if (file_exists($upload_folder .$filename) && $filename) {
            echo 'Image with such name already exists!';
            header("HTTP/1.0 404 Not Found");
            return false;
         }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_folder . $filename)) {
            echo "ok";
        } else {
            header("HTTP/1.0 404 Not Found");
        }
    }
    
    
    function getIngredients() {
        $view = $this -> getView('recipe_database', 'json');
        $view->setModel($this->getModel('recipe_database'));
        $view -> getIngredients(); 
    }
}