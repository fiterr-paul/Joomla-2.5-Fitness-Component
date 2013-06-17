<?php
/**
 * @version     1.0.0
 * @package     com_fitness_goals
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nikolay Korban <niklug@ukr.net> - http://
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_fitness/assets/css/fitness.css');
?>
<style type="text/css">
#jform_details-lbl, #jform_comments-lbl {
    float: none;
}

</style>
<script type="text/javascript">
    function getScript(url,success) {
        var script = document.createElement('script');
        script.src = url;
        var head = document.getElementsByTagName('head')[0],
        done = false;
        // Attach handlers for all browsers
        script.onload = script.onreadystatechange = function() {
            if (!done && (!this.readyState
                || this.readyState == 'loaded'
                || this.readyState == 'complete')) {
                done = true;
                success();
                script.onload = script.onreadystatechange = null;
                head.removeChild(script);
            }
        };
        head.appendChild(script);
    }
    getScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',function() {
        js = jQuery.noConflict();
        js(document).ready(function(){
            

            Joomla.submitbutton = function(task)
            {
                if (task == 'goal.cancel') {
                    Joomla.submitform(task, document.getElementById('goal-form'));
                }
                else{
                    
                    if (task != 'goal.cancel' && document.formvalidator.isValid(document.id('goal-form'))) {
                        
                        Joomla.submitform(task, document.getElementById('goal-form'));
                    }
                    else {
                        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
                    }
                }
            }
        });
    });
</script>

<form action="<?php echo JRoute::_('index.php?option=com_fitness&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="goal-form" class="form-validate">
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_FITNESS_GOALS_LEGEND_GOAL'); ?></legend>
            <ul class="adminformlist">

                				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<li><?php echo $this->form->getLabel('user_id'); ?>
				<?php echo $this->form->getInput('user_id'); ?></li>
				<li><?php echo $this->form->getLabel('primary_trainer'); ?>
				<?php echo $this->form->getInput('primary_trainer'); ?></li>
				<li><?php echo $this->form->getLabel('category_id'); ?>
				<?php echo $this->form->getInput('category_id'); ?></li>
				<li><?php echo $this->form->getLabel('deadline'); ?>
				<?php echo $this->form->getInput('deadline'); ?></li>
                                <li><?php echo $this->form->getLabel('completed'); ?>
				<?php echo $this->form->getInput('completed'); ?></li>
				<li><?php echo $this->form->getLabel('details'); ?>
				<?php echo $this->form->getInput('details'); ?></li>
				<li><?php echo $this->form->getLabel('comments'); ?>
				<?php echo $this->form->getInput('comments'); ?></li>
	
				<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
                                
                                <?php $created =  $this->item->created; 
                                if(($created == '0000-00-00 00:00:00') OR ($created == '')) {
                                    $created = JHTML::_('date', $date = null, $format = 'Y-m-d h:m:s', $offset = NULL );
                                }
                                
                                ?>
				<input type="hidden" name="jform[created]" value="<?php echo $created ?>" />
				<input type="hidden" name="jform[modified]" value="<?php echo JHTML::_('date', $date = null, $format = 'Y-m-d h:m:s', $offset = NULL ); ?>" />


            </ul>
        </fieldset>
    </div>

    

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
    <div class="clr"></div>

    <style type="text/css">
        /* Temporary fix for drifting editor fields */
        .adminformlist li {
            clear: both;
        }
    </style>
</form>





