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

/**
 * Fitness helper.
 */
class FitnessHelper
{
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
}