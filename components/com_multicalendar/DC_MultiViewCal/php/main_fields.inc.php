<?php
require_once  JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_fitness' . DS .'helpers' . DS . 'fitness.php';

$helper = new FitnessHelper();

$cid = JRequest::getVar( 'cid' );
    
$user = &JFactory::getUser($cid);

$business_profile_id = $helper->JErrorFromAjaxDecorator($helper->getBusinessProfileId($user->id));

$is_simple_trainer = FitnessFactory::is_simple_trainer($user->id);


var_dump($is_simple_trainer);

?>
<table border="0">
    <tbody>
        <tr>
            <td>
                <table border="0"  style="margin-right:17px;">
                    <tbody>
                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <td>Appointment:</td>
                            <td>
                                <?php
                                if (isset($appointments[0])) {
                                    echo '<select style="float:left;" id="Subject" name="Subject" class="required safe inputtext" ">';
                                    echo '<option  value="" >-Select-</option>';
                                    for ($i = 0; $i < count($appointments[0]); $i++) {
                                        echo '<option data-catid="' . $appointments[2][$i] . '" id="' . $appointments[1][$i] . '" value="' . ($appointments[0][$i]) . '" ' . ((isset($event) && (trim($event->title) == trim($appointments[0][$i]))) ? "selected" : "") . '>' . $appointments[0][$i] . '</option>';
                                    }
                                    echo '</select>';
                                }

                                ?>  
                            </td>
                        </tr>
                       <tr>
                            <td>Session Type:</td>
                            <td> 
                                <select  id="session_type" name="session_type" class="required safe inputtext" ></select> 
                            </td>
                        </tr>
                        <tr>
                            <td>Session Focus:</td>
                            <td> 
                                <select  id="session_focus" name="session_focus" class="required safe inputtext" ></select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>


            <td>
                <table border="0">
                    <tbody>
                        <tr>
                            <td>Business Name:</td>
                            <td>
                                <?php echo $helper->generateSelect($helper->getBusinessProfileList($user->id), 'business_profile_id', 'business_profile_id', $business_profile_id , '', true, "required safe inputtext"); ?>
                            </td>
                        </tr>
                        <tr id="client_select_tr">
                            <td>Client:</td>
                            <td>
                                <select style="float:left;" id="client" name="client_id" class="required safe inputtext">
                                    <option>-Select-</option>
                                </select>
                            </td>
                        </tr>
                        <tr id="trainer_select_tr">
                            <td>Trainer:</td>
                            <td>
                                <select  id="trainer" name="trainer_id" class="required safe inputtext" ></select>
                            </td>
                        </tr>
                        <tr style="display:none;" id="trainers_select_tr">
                            <td>Trainer:</td>
                            <td>
                                <select  id="trainers" name="trainer_id" class="required safe inputtext" >
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Location:</td>
                            <td> <?php
                                if (isset($dc_locations)) {
                                    echo '<select  id="Location" name="Location" class="required safe inputtext" >';
                                    echo '<option>-Select-</option>';
                                    for ($i = 0; $i < count($dc_locations); $i++) {
                                        echo '<option value="' . ($dc_locations[$i]) . '" ' . ((isset($event) && ($event->location == trim($dc_locations[$i]))) ? "selected" : "") . '>' . $dc_locations[$i] . '</option>';
                                    }
                                    echo '</select>';
                                }

                                ?>  </td>
                        </tr>
                    </tbody>
                </table>
            </td>

        </tr>
    </tbody>
  </table>

<input id="colorvalue" name="colorvalue" type="hidden" value="<?php echo isset($event)?$event->color:"" ?>" />
<input type="hidden" id="rrule" name="rrule" value="<?php echo $event->rrule?>" size=55 />
<input type="hidden" id="rruleType" name="rruleType" value="" size=55 />


<script type="text/javascript">
    (function($) {
        
        // connect helper class
        var helper_options = {
            'ajax_call_url' : '<?php echo JURI::root();?>index.php?option=com_fitness&tmpl=component&<?php echo JSession::getFormToken(); ?>=1',
        }
        var fitness_helper = $.fitness_helper(helper_options);
        
        $("#business_profile_id").on('change', function() {
            var business_profile_id = $(this).val();
            businessLogic(business_profile_id);
        });
        
        function businessLogic(business_profile_id) {
            
            if(!business_profile_id) {
                return;
            }
            
            // populate clients select
            fitness_helper.populateClientsSelectOnBusiness('getClientsByBusiness', 'goals_periods', business_profile_id, '#client', '<?php echo trim($event->client_id);?>', '<?php echo $user->id;?>');
            
            fitness_helper.populateTrainersSelectOnBusiness('goals_periods', business_profile_id, '#trainers', '<?php echo trim($event->trainer_id) ?>', '<?php echo $user->id;?>');
        }
        
        var business_profile_id = '<?php echo $business_profile_id;?>';

        
        if(business_profile_id) {
            $("#business_profile_id").val(business_profile_id);
            businessLogic(business_profile_id);
        }
        
    })($);


</script>






