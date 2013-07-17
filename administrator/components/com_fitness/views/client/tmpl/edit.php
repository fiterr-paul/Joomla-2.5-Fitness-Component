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
        //jQuery = jQuery.noConflict();
        $(document).ready(function(){

            Joomla.submitbutton = function(task)
            {
                if (task == 'client.cancel') {
                    Joomla.submitform(task, document.getElementById('client-form'));
                }
                else{
                    
                    if (task != 'client.cancel' && document.formvalidator.isValid(document.id('client-form'))) {
                        
                        Joomla.submitform(task, document.getElementById('client-form'));
                    }
                    else {
                        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
                    }
                }
            }
            
            //////////////////////////////////////////
            $('#user_group').change(function(){
               var user_group = $(this).find(':selected').val();
               getClientsByGroup(user_group);
            });
            
            
            function getClientsByGroup(user_group) {
                var url = '<?php echo JUri::base() ?>index.php?option=com_fitness&tmpl=component&<?php echo JSession::getFormToken(); ?>=1'
                $.ajax({
                    type : "POST",
                    url : url,
                    data : {
                       view : 'goals',
                       format : 'text',
                       task : 'getClientsByGroup',
                       user_group : user_group
                    },
                    dataType : 'json',
                    success : function(response) {
                        if(!response.status.success) {
                            alert(response.status.message);
                            $("#jform_user_id").html('');
                            return;
                        }
                        var client_id = '<?php echo $this->item->user_id; ?>';
                        
                        var html = '<option  value="">-Select-</option>';
                        $.each(response.data, function(index, value) {
                             if(index) {
                                var selected = '';
                                if(client_id == index) {
                                    selected = 'selected';
                                }
                                html += '<option ' + selected + ' value="' + index + '">' +  value + '</option>';
                            }
                        });
                        $("#jform_user_id").html(html);
                     
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        alert("error");
                    }
                });
            }

            var user_group = $("#user_group").find(':selected').val();
            if(user_group) {
                getClientsByGroup(user_group);
            }
            
        });
    });
</script>

<form action="<?php echo JRoute::_('index.php?option=com_fitness&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="client-form" class="form-validate">
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_FITNESS_LEGEND_CLIENT'); ?></legend>
            <ul class="adminformlist">
                <?php
                $db = JFactory::getDbo();
                $sql = 'SELECT id AS value, title AS text'. ' FROM #__usergroups' . ' ORDER BY id';
                $db->setQuery($sql);
                $grouplist = $db->loadObjectList();

                $userGroup = $this->getUserGroup($this->item->user_id); 
                ?>

                <li>
                    <label id="jform_user_id-lbl" class="" for="jform_user_id">User Group</label>
                    <select id="user_group" name="user_group" class="inputbox" >
                        <option value=""><?php echo JText::_('-Select-'); ?></option>
                        <?php 
                        foreach ($grouplist as $option) {
                            if($userGroup == $option->text){ 
                                $selected = 'selected';
                            } else {
                                $selected = '';
                            }
                            echo '<option ' . $selected . ' value="' . $option->value . '">' . $option->text . ' </option>';
                        }
                        ?>
                    </select>
                </li>
                <?php
                    $id = $this->item->id;
                ?>
                <li>
                    <label id="jform_user_id-lbl" class="" for="jform_user_id">Username</label>
                    <select id="jform_user_id" class="inputbox" name="jform[user_id]"></select>
                </li>

				<li><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>
                               
                                <?php
                                if($this->getUserGroup(JFactory::getUser()->id)== 'Super Users') {
                                ?>
                                    <li><?php echo $this->form->getLabel('primary_trainer'); ?>
                                    <?php echo $this->form->getInput('primary_trainer'); ?></li>
                                    <li><?php echo $this->form->getLabel('other_trainers'); ?>
                                    <?php echo $this->getInput($this->item->id); ?></li>
                                <?php
                                } else {
                                ?>
                              <li>
                                  <label id="jform_primary_trainer-lbl" class="" for="jform_primary_trainer">Primary trainer</label>
                                      <select id="jform_primary_trainer" class="inputbox" name="jform[primary_trainer]">
                                          <option value="<?php echo JFactory::getUser()->id?>"><?php echo JFactory::getUser()->name?></option>
                                      </select>
                                  </li>
                                <?php
                                }
                                ?>
                       


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



