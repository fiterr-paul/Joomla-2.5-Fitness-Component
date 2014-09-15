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

jimport('joomla.application.component.view');

/**
 * View class for a list of Fitness.
 */
class FitnessViewNutrition_diaries extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        $input = JFactory::getApplication()->input;
        $view = $input->getCmd('view', '');
        FitnessHelper::addSubmenu('Dashboard', 'dashboard');
        FitnessHelper::addSubmenu('Clients', 'clients');
        FitnessHelper::addSubmenu('Client Overview', 'client_overview');
        FitnessHelper::addSubmenu('Client Planning', 'goals');
        FitnessHelper::addSubmenu('Client Progress', 'client_progress');
        FitnessHelper::addSubmenu('Assessments', 'assessments');
        FitnessHelper::addSubmenu('Calendar', 'calendar');
        FitnessHelper::addSubmenu('Programs', 'programs');
        FitnessHelper::addSubmenu('Program Templates', 'programs_templates');
        FitnessHelper::addSubmenu('Exercise Library', 'exercise_library');
        FitnessHelper::addSubmenu('Nutrition Plans', 'nutrition_plans');
        FitnessHelper::addSubmenu('Nutrition Diary', 'nutrition_diaries');
        FitnessHelper::addSubmenu('Nutrition Database', 'nutritiondatabases');
        FitnessHelper::addSubmenu('Recipe Database', 'nutrition_recipes');
        FitnessHelper::addSubmenu('Settings', 'settings');

        $document = &JFactory::getDocument();
                
        $document -> addscript( JUri::root() . 'administrator/components/com_fitness/assets/js/lib/require.js');

        $document -> addscript( JUri::root() . 'administrator/components' . DS . 'com_fitness' . DS .'assets'. DS .'js'. DS . 'lib' . DS . 'underscore-min.js');
        
        $document->addStyleSheet(JURI::root() . 'administrator/components' . DS . 'com_fitness' . DS . 'assets' . DS . 'css' . DS . 'jquery.cleditor.css');
        
        include_once JPATH_COMPONENT_ADMINISTRATOR . DS .'assets'. DS .'js'. DS . 'underscore_templates.html';

        
        $document->addStyleSheet('components/com_fitness/assets/css/fitness.css');
        $document->addStyleSheet(JUri::root() . 'administrator/components/com_fitness/assets/css/jquery-ui.css');
        $document->addStyleSheet( JUri::root() . 'administrator/components' . DS . 'com_fitness' . DS .'assets'. DS .'css'. DS . 'jquery.timepicker.css');
        


        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {
        require_once JPATH_COMPONENT . '/helpers/fitness.php';

        $state = $this->get('State');
        $canDo = FitnessHelper::getActions($state->get('filter.category_id'));

        JToolBarHelper::title(JText::_('COM_FITNESS_TITLE_NUTRITION_DIARIES'), 'nutrition_diaries.png');

        /*
        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/nutrition_diary';

        if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::custom('nutrition_diaries.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
                JToolBarHelper::custom('nutrition_diaries.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'nutrition_diaries.delete', 'JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::archiveList('nutrition_diaries.archive', 'JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
                JToolBarHelper::custom('nutrition_diaries.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
        }

        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
            if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
                JToolBarHelper::deleteList('', 'nutrition_diaries.delete', 'JTOOLBAR_EMPTY_TRASH');
                JToolBarHelper::divider();
            } else if ($canDo->get('core.edit.state')) {
                JToolBarHelper::trash('nutrition_diaries.trash', 'JTOOLBAR_TRASH');
                JToolBarHelper::divider();
            }
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_fitness');
        }
         * 
         */
    }

}
