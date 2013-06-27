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

jimport('joomla.application.component.controllerform');

/**
 * Goalfocus controller class.
 */
class FitnessControllerGoalfocus extends JControllerForm
{

    function __construct() {
        $this->view_list = 'goalfocuses';
        parent::__construct();
    }

}