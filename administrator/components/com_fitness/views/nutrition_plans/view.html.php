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
class FitnessViewNutrition_plans extends JView {

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
        $document = &JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . 'components' . DS . 'com_fitness' . DS . 'assets' . DS . 'css' . DS . 'fitness.css');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        $input = JFactory::getApplication()->input;
        $view = $input->getCmd('view', '');
        FitnessHelper::addSubmenu('Dashboard', 'dashboard');
        FitnessHelper::addSubmenu('Clients', 'clients');
        FitnessHelper::addSubmenu('Client Planning', 'goals');
        FitnessHelper::addSubmenu('Calendar', 'calendar');
        FitnessHelper::addSubmenu('Nutrition Diary', 'nutrition_diary');
        FitnessHelper::addSubmenu('Assessments', 'assessments');
        FitnessHelper::addSubmenu('Nutrition Database', 'nutritiondatabases');
        FitnessHelper::addSubmenu('Settings', 'settings');

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {
        JToolBarHelper::title(JText::_('COM_FITNESS_TITLE_NUTRITION_PLANS'), 'nutrition_plans.png');
    }

}
