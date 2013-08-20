<?php
/**
 * @version     1.0.0
 * @package     com_fitness
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
    /* Temporary fix for drifting editor fields */
    .adminformlist li {
        clear: both;
    }
</style>

<form action="<?php echo JRoute::_('index.php?option=com_fitness&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="nutrition_plan-form" class="form-validate">
    <div class="width-100 fltlft">
        <table width="100%">
            <tr>
                <td width="30%">
                    <fieldset style="height:410px;" class="adminform">
                        <legend>CLIENT & TRAINER(S)</legend>
                        <?php
                        $db = JFactory::getDbo();

                        $sql = "SELECT id AS value, username AS text FROM #__users INNER JOIN #__user_usergroup_map ON #__user_usergroup_map.user_id=#__users.id WHERE #__user_usergroup_map.group_id=(SELECT id FROM #__usergroups WHERE title='Trainers')";
                        $db->setQuery($sql);
                        if(!$db->query()) {
                            JError::raiseError($db->getErrorMsg());
                        }
                        $primary_trainerlist = $db->loadObjectList();
                        ?>
                        <ul>
                            <li><?php echo $this->form->getLabel('trainer_id'); ?>
                                <div class='filter-select fltrt'>
                                    <select id="jform_trainer_id" class="inputbox" name="jform[trainer_id]">
                                        <option value=""><?php echo JText::_('-Select-');?></option>
                                        <?php echo JHtml::_('select.options', $primary_trainerlist, "value", "text", $this->item->trainer_id, true);?>
                                    </select>
                                </div>
                            </li>
                            <li><?php echo $this->form->getLabel('client_id'); ?>
                                <div class='filter-select fltrt'>
                                    <select id="jform_client_id" class="inputbox" name="jform[client_id]">
                                        <?php
                                        if($this->item->client_id) {
                                            echo '<option value="' . $this->item->client_id . '">'. JFactory::getUser($this->item->client_id)->name .'</option>';
                                        } else {
                                            echo '<option value="">' . JText::_('-Select-')  . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </li>
                            <li>
                                <label id="jform_trainers_id-lbl" class="" for="jform_trainers_id">Secondary Trainers</label>
                                <div class='filter-select fltrt'>
                                    <div id="secondary_trainers"></div>
                                </div>
                            </li>
                        </ul>
                    </fieldset>
                </td>
                <td width="70%">
                    <fieldset  class="adminform">
                        <legend>NUTRITION PLAN (PERIODIZATION)</legend>
                        <table>
                            <tr>
                                <td>
                                    <?php echo $this->form->getLabel('primary_goal'); ?>
                                    <select id="jform_primary_goal" class="inputbox" name="jform[primary_goal]">
                                        <?php
                                        if($this->item->primary_goal) {
                                            echo '<option value="' . $this->item->primary_goal. '">'. $this->getPrimaryGoalName($this->item->primary_goal) .'</option>';
                                        } else {
                                            echo '<option value="">' . JText::_('-Select-')  . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td></td>
                                <td></td>
                                <td>
                                    <?php echo $this->form->getLabel('state'); ?>
                                    <?php echo $this->form->getInput('state'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <?php echo $this->form->getLabel('training_period'); ?>
                                    <?php echo $this->form->getInput('training_period'); ?>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->form->getLabel('active_start'); ?>
                                    <?php echo $this->form->getInput('active_start'); ?>
                                </td>
                                <td>
                                    <?php echo $this->form->getLabel('force_active'); ?>
                                </td>
                                <td>
                                    <?php echo $this->form->getInput('force_active'); ?>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $this->form->getLabel('active_finish'); ?>
                                    <?php echo $this->form->getInput('active_finish'); ?>
                                </td>
                                <td>
                                    <?php echo $this->form->getLabel('no_end_date'); ?>
                                </td>
                                <td>
                                    <?php echo $this->form->getInput('no_end_date'); ?>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <?php echo $this->form->getLabel('nutrition_focus'); ?>
                                    <?php echo $this->form->getInput('nutrition_focus'); ?>
                                </td>
                                <td></td>
                            </tr>
                            
                            <td colspan="4">
                                    <?php echo $this->form->getLabel('trainer_comments'); ?>
                                    <?php echo $this->form->getInput('trainer_comments'); ?>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <fieldset id="daily_micronutrient"  class="adminform">
                        <?php
                        if(!$this->item->id) {
                            echo 'Save form to proceed add Targets';
                        }
                        ?>
                        <legend>DAILY MACRONUTRIENT & CALORIE TARGETS</legend>
                    </fieldset>
                </td>
            </tr>
        </table>
    </div>
        
    <div style="display:none;">
    <?php echo $this->form->getLabel('created'); ?>
    <?php echo $this->form->getInput('created'); ?>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
    <div class="clr"></div>
</form>


<script type="text/javascript">
    
    // set options
    var nutrition_plan_options = {
        'trainer_select' : $("#jform_trainer_id"),
        'client_select' : $("#jform_client_id"),
        'secondary_trainers_wrapper' : $("#secondary_trainers"),
        'primary_goal_select' : $("#jform_primary_goal"),
        'training_period_select' : $("#jform_training_period"),
        'calendar_frontend_url' : '<?php echo JURI::root();?>index.php?option=com_multicalendar&task=load&calid=0',
        'fitness_administration_url' : '<?php echo JURI::root();?>administrator/index.php?option=com_fitness&tmpl=component&<?php echo JSession::getFormToken(); ?>=1',
        'client_selected' : '<?php echo $this->item->client_id;?>',
        'primary_goal_selected' : '<?php echo $this->item->primary_goal;?>',
        'active_start_field' : $("#jform_active_start"),
        'active_finish_field' : $("#jform_active_finish"),
        'active_start_img' : $("#jform_active_start_img"),
        'active_finish_img' : $("#jform_active_finish_img"),
        'force_active_yes' : $("#jform_force_active0"),
        'force_active_no' : $("#jform_force_active1"),
        'force_active_value' : '<?php echo $this->item->force_active;?>',
        'active_finish_value' : '<?php echo $this->item->active_finish;?>',
        'no_end_date_label': $("#jform_no_end_date-lbl"),
        'no_end_fieldset' : $("#jform_no_end_date"),
        'no_end_date_yes' : $("#jform_no_end_date0"),
        'no_end_date_no' : $("#jform_no_end_date1"),
        'max_possible_date' : '9999-12-31'
    }
    
    var macronutrient_targets_options = {
        'main_wrapper' : $("#daily_micronutrient"),
        'fitness_administration_url' : '<?php echo JURI::root();?>administrator/index.php?option=com_fitness&tmpl=component&<?php echo JSession::getFormToken(); ?>=1',
        'protein_grams_coefficient' : 4,
        'fats_grams_coefficient' : 9,
        'carbs_grams_coefficient' : 4,
        'nutrition_plan_id' : '<?php echo $this->item->id;?>',
        'empty_html_data' : {'calories' : "", 'water' : "", 'protein' : "", 'fats' : "", 'carbs' : ""}
    }
    
    // cteate main object
    var nutrition_plan = new NutritionPlan(nutrition_plan_options);
    
    // append targets fieldsets
    var macronutrient_targets_heavy = new MacronutrientTargets(macronutrient_targets_options, 'heavy', 'HEAVY TRAINING DAY');
    
    var macronutrient_targets_light = new MacronutrientTargets(macronutrient_targets_options, 'light', 'LIGHT TRAINING DAY');
    
    var macronutrient_targets_rest = new MacronutrientTargets(macronutrient_targets_options, 'rest', 'RECOVERY / REST DAY');
    
    // attach listeners on document ready
    $(document).ready(function(){
        nutrition_plan.run();

        macronutrient_targets_heavy.run();
        macronutrient_targets_light.run();
        macronutrient_targets_rest.run()

    });


    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    Joomla.submitbutton = function(task)  {
        if (task == 'nutrition_plan.cancel') {
            Joomla.submitform(task, document.getElementById('nutrition_plan-form'));
        }
        else{

            if (task != 'nutrition_plan.cancel' && document.formvalidator.isValid(document.id('nutrition_plan-form'))) {
                
                if(macronutrient_targets_options.nutrition_plan_id) {
                    // Targets
                    var heavy_validation = macronutrient_targets_heavy.validateSum100();
                    if(heavy_validation == false) {
                        alert('<?php echo $this->escape('Protein, Fats and Carbs MUST equal (=) 100%'); ?>');
                        return;
                    }

                    var light_validation = macronutrient_targets_light.validateSum100();
                    if(light_validation == false) {
                        alert('<?php echo $this->escape('Protein, Fats and Carbs MUST equal (=) 100%'); ?>');
                        return;
                    }

                    var rest_validation = macronutrient_targets_rest.validateSum100();
                    if(rest_validation == false) {
                        alert('<?php echo $this->escape('Protein, Fats and Carbs MUST equal (=) 100%'); ?>');
                        return;
                    }
                }

                //save targets data
                if(macronutrient_targets_options.nutrition_plan_id) {     
                    macronutrient_targets_heavy.saveTargetsData(function(output) {
                        macronutrient_targets_light.saveTargetsData(function(output) {
                            macronutrient_targets_rest.saveTargetsData(function(output) {
                                //reset force active fields in database by ajax
                                var force_active = nutrition_plan.options.force_active_yes.is(":checked");
                                if(force_active) {
                                    nutrition_plan.resetAllForceActive(function() {
                                        Joomla.submitform(task, document.getElementById('nutrition_plan-form'));
                                    });
                                } else {
                                    Joomla.submitform(task, document.getElementById('nutrition_plan-form'));
                                }
                            });
                        });

                      });
                } else {
                    //reset force active fields in database by ajax
                    var force_active = nutrition_plan.options.force_active_yes.is(":checked");
                    if(force_active) {
                        nutrition_plan.resetAllForceActive(function() {
                            Joomla.submitform(task, document.getElementById('nutrition_plan-form'));
                        });
                    } else {
                        Joomla.submitform(task, document.getElementById('nutrition_plan-form'));
                    }
                }
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
</script>