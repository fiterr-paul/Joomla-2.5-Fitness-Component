<?php
/**
* @Copyright Copyright (C) 2010 CodePeople, www.codepeople.net
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*
* This file is part of Multi Calendar for Joomla <www.joomlacalendars.com>.
*
* Multi Calendar for Joomla is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Multi Calendar for Joomla  is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Multi Calendar for Joomla.  If not, see <http://www.gnu.org/licenses/>.
*
**/

defined('_JEXEC') or die('Restricted access');
require_once( JPATH_COMPONENT.'/DC_MultiViewCal/php/functions.php' );
require_once( JPATH_BASE.'/components/com_multicalendar/DC_MultiViewCal/php/list.inc.php' );

$db 	=& JFactory::getDBO();
header('Content-type:text/javascript;charset=UTF-8');
$method = JRequest::getVar( 'method' );
$calid = JRequest::getVar( 'calid' );

switch ($method) {
    case "add":
        $ret = addCalendar($calid,
                JRequest::getVar("CalendarStartTime"), 
                JRequest::getVar("CalendarEndTime"),
                JRequest::getVar("CalendarTitle"),
                JRequest::getVar("IsAllDayEvent"),
                
       
                JRequest::getVar("Location")
    
                );
        break;
    case "list":
        //$ret = listCalendar(JRequest::getVar("showdate"), JRequest::getVar("viewtype"));

        $d1 = js2PhpTime(JRequest::getVar("startdate"));
        $d2 = js2PhpTime(JRequest::getVar("enddate"));
        $client_id = JRequest::getVar("client_id");
        $trainer_id = JRequest::getVar("trainer_id");
        $location = JRequest::getVar("location");
        $appointment = JRequest::getVar("appointment");
        $session_type = JRequest::getVar("session_type");
        $session_focus = JRequest::getVar("session_focus");

        $d1 = mktime(0, 0, 0,  date("m", $d1), date("d", $d1), date("Y", $d1));
        $d2 = mktime(0, 0, 0, date("m", $d2), date("d", $d2), date("Y", $d2))+24*60*60-1;
        $ret = listCalendarByRange($calid, ($d1),($d2), $client_id, $trainer_id, $location, $appointment, $session_type, $session_focus);

        break;
    case "update":
        $ret = updateCalendar(JRequest::getVar("calendarId"), JRequest::getVar("CalendarStartTime"), JRequest::getVar("CalendarEndTime"));  
        break;
    case "remove":
        $ret = removeCalendar( JRequest::getVar("calendarId"),JRequest::getVar("rruleType"));
        break;
    case "get_session_type":
            get_session_type();
        break;
    case "get_session_focus":
            get_session_focus();
        break;
    case "get_trainers":
            get_trainers();
        break;
    case "get_clients":
        get_clients();
    break;
    case "set_event_status":
            set_event_status();
        break;
    case "add_exercise":
            add_exercise();
        break;
    case "delete_exercise":
            delete_exercise();
        break;
    case "set_event_exircise_order":
        set_event_exircise_order();
    case "send_appointment_email":
        send_appointment_email(JRequest::getVar('event_id'), 'workout');
    break;
    case "sendAppointmentEmail":
        sendAppointmentEmail('confirmation');
    break;
    case "sendNotifyEmail":
        sendAppointmentEmail('notify');
    break;
    case "sendNotifyAssessmentEmail":
        sendAppointmentEmail('notifyAssessment');
    break;
    // goal emails
    case "sendNotifyGoalEmail":
        sendGoalEmail('email_notify_goal');
    break;
    case "sendGoalCompleteEmail":
        sendGoalEmail('email_goal_complete');
    break;
    case "sendGoalIncompleteEmail":
        sendGoalEmail('email_goal_incomplete');
    break;
    //

    // appointment status emails
    case "sendAppointmentAttendedEmail":
        sendAppointmentStatusEmail('email_status_attended');
    break;
    case "sendAppointmentCancelledEmail":
        sendAppointmentStatusEmail('email_status_cancelled');
    break;
    case "sendAppointmentLatecancelEmail":
        sendAppointmentStatusEmail('email_status_late_cancel');
    break;
    case "sendAppointmentNoshowEmail":
        sendAppointmentStatusEmail('email_status_no_show');
    break;

    // Recipe approved email
    case "sendRecipeEmail":
        sendRecipeEmail();
    break;
    //

    case "update_exercise_field":
        update_exercise_field();
    break;
    case "get_semi_clients":
        get_semi_clients();
    break;
    case "add_update_group_client":
        add_update_group_client();
    break;
    case "delete_group_client":
        delete_group_client();
    break;
    case "set_group_client_status":
        set_group_client_status();
    break;
    case "generateFormHtml":
        generateFormHtml();
          break;
    case "saveDragedData":
        saveDragedData();
        break;
    case "sendRemindersManually":
        sendRemindersManually();
         break;
    case "deleteEvent":
        deleteEvent(); 
        break;

    case "adddetails":

        $st = JRequest::getVar("stpartdatelast") . " " . JRequest::getVar("stparttimelast");
        $et = JRequest::getVar("etpartdatelast") . " " . JRequest::getVar("etparttimelast");
        if(JRequest::getVar("id")!=""){

            $ret = updateDetailedCalendar(
                        JRequest::getVar("id"),
                        $st,
                        $et,
                        JRequest::getVar("Subject"),
                        (JRequest::getVar("IsAllDayEvent")==1)?1:0, 
                        JRequest::getVar('Description','','POST','STRING',JREQUEST_ALLOWHTML) ,
                        JRequest::getVar('comments','','POST','STRING',JREQUEST_ALLOWHTML) ,
                        JRequest::getVar('session_type','','POST','STRING',JREQUEST_ALLOWHTML) ,
                        JRequest::getVar('session_focus','','POST','STRING',JREQUEST_ALLOWHTML) ,
                        JRequest::getVar("client_id"),
                        JRequest::getVar("trainer_id"),
                        JRequest::getVar("Location"), 
                        JRequest::getVar("colorvalue"), 
                        JRequest::getVar("frontend_published"), 
                        JRequest::getVar("published"), 
                        JRequest::getVar("rrule"),
                        JRequest::getVar("rruleType"),
                        JRequest::getVar("timezone")
                    );
        }else{

            $ret = addDetailedCalendar(
                    $calid,
                    $st,
                    $et,
                    JRequest::getVar("Subject"), 
                    (JRequest::getVar("IsAllDayEvent")==1)?1:0,
                    JRequest::getVar('Description','','POST','STRING',JREQUEST_ALLOWHTML) ,
                    JRequest::getVar('session_type','','POST','STRING',JREQUEST_ALLOWHTML) ,
                    JRequest::getVar('session_focus','','POST','STRING',JREQUEST_ALLOWHTML) ,
                    JRequest::getVar("client_id"),
                    JRequest::getVar("trainer_id"),
                    JRequest::getVar("Location"),
                    JRequest::getVar("colorvalue"),
                    JRequest::getVar("rrule"),
                    0,
                    JRequest::getVar("timezone")
           );
        }
        break;


}
echo json_encode($ret);
function checkIfOverlappingThisEvent($id, $st, $et)
{
    $db 	=& JFactory::getDBO();
    $sql = "select * from `".DC_MV_CAL."` where id=".$id;

    $db->setQuery( $sql );
    $rows = $db->loadObjectList();
    if (count($rows)>0)
        return checkIfOverlapping($rows[0]->calid, $st, $et, $rows[0]->title, $rows[0]->location,$id);
    else
        return true;
}
function checkIfOverlapping($calid, $st, $et, $sub, $loc,$id)
{
    $db 	=& JFactory::getDBO();
    $sd = date("Y-m-d H:i:s",js2PhpTime($st));
    $ed = date("Y-m-d H:i:s",js2PhpTime($et));
    $condition = "";
    if (JC_NO_OVERLAPPING_TIME)
        $condition .= " and ( (`".DC_MV_CAL_FROM."` > '"
      .($sd)."' and `".DC_MV_CAL_FROM."` < '". ($ed)."') or (`".DC_MV_CAL_TO."` > '"
      .($sd)."' and `".DC_MV_CAL_TO."` < '". ($ed)."') or (`".DC_MV_CAL_FROM."` <= '"
      .($sd)."' and `".DC_MV_CAL_TO."` >= '". ($ed)."') )   ";
    if (JC_NO_OVERLAPPING_SUBJECT)
        $condition .= " and ( `".DC_MV_CAL_TITLE."` = '". $sub."' )   ";
    if (JC_NO_OVERLAPPING_LOCATION)
        $condition .= " and ( `".DC_MV_CAL_LOCATION."` = '". $loc."' )   ";
    if ($condition=="")
        $condition = " and 1=0";
    $sql = "select * from `".DC_MV_CAL."` where ".DC_MV_CAL_IDCAL."=".$calid.$condition;

    $db->setQuery( $sql );

    $rows = $db->loadObjectList();
    if (count($rows)==0 || (count($rows)==1 && $rows[0]->id==$id))
        return true;
    else
        return false;

}
function getMessageOverlapping()
{
    $ret = array();
    $ret['IsSuccess'] = false;
    $ret['Msg'] = "OVERLAPPING";
    return $ret;
}
function addCalendar(
        $calid,
        $st,
        $et, 
        $sub,
        $ade,

        $Location
        ){
  $ret = array();
  $db 	=& JFactory::getDBO();
  $user =& JFactory::getUser();
  try{
    if (checkIfOverlapping($calid, $st, $et,$sub, $loc,0))
    {
    $sql = "insert into `".DC_MV_CAL."` (
        `".DC_MV_CAL_IDCAL."`,
        `".DC_MV_CAL_TITLE."`,
        `".DC_MV_CAL_FROM."`,
        `".DC_MV_CAL_TO."`,
        `".DC_MV_CAL_ISALLDAY."`,

        `".DC_MV_CAL_LOCATION."`,
         
        `owner`,
        `published`
        ) values (
        
      ".$calid.","
      .$db->Quote($sub).", '"
      .php2MySqlTime(js2PhpTime($st))."', '"
      .php2MySqlTime(js2PhpTime($et))."', "
      .$db->Quote($ade).", "
      .$db->Quote($loc).", "
      .$user->id
      .",1)";
      

    $db->setQuery( $sql );
    if (!$db->query()){
      $ret['IsSuccess'] = false;
      $ret['Msg'] = $db->stderr();
    }else{
      $ret['IsSuccess'] = true;
      $ret['Msg'] = 'add success';
      $ret['Data'] = $db->insertid();
    }
    }
    else
     $ret = getMessageOverlapping();

	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }

  return $ret;
}


function addDetailedCalendar(
        $calid,
        $st,
        $et,
        $sub,
        $ade,
        $dscr,
        $session_type,
        $session_focus,
        $client_id,
        $trainer_id,
        $loc,
        $color,
        $rrule,
        $uid,
        $tz){
                                
  $ret = array();

  $db 	=& JFactory::getDBO();
  $user =& JFactory::getUser();
  try{
    if (checkIfOverlapping($calid, $st, $et,$sub, $loc,0))
    {
    $sql = "insert into `".DC_MV_CAL."` (
        `".DC_MV_CAL_IDCAL."`,
        `".DC_MV_CAL_TITLE."`,
        `".DC_MV_CAL_FROM."`, 
        `".DC_MV_CAL_TO."`, 
        `".DC_MV_CAL_ISALLDAY."`,
        `".DC_MV_CAL_DESCRIPTION."`,
        `session_type`,
        `session_focus`,
        `client_id`,
        `trainer_id`,
        `".DC_MV_CAL_LOCATION."`, 
        `".DC_MV_CAL_COLOR."`,
        `rrule`,`uid`,`owner`, `published`) values (
        
       ".$calid.","
      .$db->Quote($sub).", '"
      .php2MySqlTime(js2PhpTime($st))."', '"
      .php2MySqlTime(js2PhpTime($et))."', "
      .$db->Quote($ade).", "
      .$db->Quote($dscr).", "
      .$db->Quote($session_type).", "
      .$db->Quote($session_focus).", "
      .$db->Quote($client_id).", "
      .$db->Quote($trainer_id).", "
      .$db->Quote($loc).", "
      .$db->Quote($color).", ".$db->Quote($rrule).", ".$db->Quote($uid).", ".$user->id.",1 )";

    $db->setQuery( $sql );
    if (!$db->query()){
      $ret['IsSuccess'] = false;
      $ret['Msg'] = $db->stderr();
    }else{
      $ret['IsSuccess'] = true;
      $ret['Msg'] = 'add success';
      $ret['Data'] = $db->insertid();
    }
    }
    else
     $ret = getMessageOverlapping();
	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  return $ret;
}

function listCalendarByRange($calid,$sd, $ed, $client_id, $trainer_id, $location, $appointment, $session_type, $session_focus){
  
  
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;
  $db 	=& JFactory::getDBO();
  try{
    $sql = "select * from `".DC_MV_CAL."` where ".DC_MV_CAL_IDCAL."=".$calid;
    $client_ids = implode($client_id, ',');
    if($client_id[0]) {
        $sql .= " and (client_id IN ($client_ids) ";
        $sql .= " or id IN (SELECT  DISTINCT event_id FROM #__fitness_appointment_clients WHERE client_id IN ($client_ids))) ";
    }
    
    $user = &JFactory::getUser();
    if($user->guest) {
    $ret['error'] = 'Login please in Front End to proceed here.';
    return $ret;
    }
    if (getUserGroup($user->id) != 'Super Users') {
        $sql .= " and trainer_id='$user->id' ";
    } else {
        $trainer_ids = implode($trainer_id, ',');
        if($trainer_id[0]) {
            $sql .= " and trainer_id IN ($trainer_ids) ";
        }
    }
    

    $locations = "'" . implode("','", $location) . "'";
    if($location[0]) {
        $sql .= " and location IN ($locations) ";
    }

    
    $appointments = "'" . implode("','", $appointment) . "'";
    if($appointment[0]) {
        $sql .= " and title IN ($appointments) ";
    }
    

    $session_types = "'" . implode("','", $session_type) . "'";
    if($session_type[0]) {
        $sql .= " and session_type IN ($session_types) ";
    }
    
    
    $session_focuses = "'" . implode("','", $session_focus) . "'";
    if($session_focus[0]) {
        $sql .= " and session_focus IN ($session_focuses) ";
    }
    //$sql .= " AND published='1'";
            
    $sql .=  " and ( (`".DC_MV_CAL_FROM."` between '"
        
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."') or (`".DC_MV_CAL_TO."` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."') or (`".DC_MV_CAL_FROM."` <= '"
      .php2MySqlTime($sd)."' and `".DC_MV_CAL_TO."` >= '". php2MySqlTime($ed)."') or rrule<>'') order by uid desc,  ".DC_MV_CAL_FROM."  ";
    
    
    $db->setQuery( $sql );
    if (!$db->query()){
          $ret['IsSuccess'] = false;
          $ret['Msg'] = $db->stderr();
    }
    $rows = $db->loadObjectList();


    $str = "";

    
    
    for ($i=0;$i<count($rows);$i++)
    {
            $clients = array();
            $clients_names = array();
            $row = $rows[$i];
            if (strlen($row->exdate) > 0)
                $row->rrule .= ";exdate=" . $row->exdate;

            $id = $row->id;
            $query = "SELECT  client_id FROM #__fitness_appointment_clients WHERE event_id='$id'";
            $db->setQuery($query);
            if (!$db->query()) {
                $ret['IsSuccess'] = false;
                $ret['Msg'] = $db->stderr();
            }
            if ($row->client_id) {
                $clients = $db->loadResultArray(0);
            }
            $clients[] = $row->client_id;

            $clients = array_unique($clients);
            foreach ($clients as $client) {
                $clients_names[] = JFactory::getUser($client)->name;
            }



            $ev = array(
            $row->id,
            $row->title,
            php2JsTime(mySql2PhpTime($row->starttime)),
            php2JsTime(mySql2PhpTime($row->endtime)),
            $row->isalldayevent,
            0, //more than one day event
            //$row->InstanceType,
            ((is_numeric($row->uid) && $row->uid>0)?$row->uid:$row->rrule),//Recurring event rule,
            $row->color,
            1,//editable
            $row->location,
            '',//$attends
            $row->description,
            $row->owner,
            $row->published,
            JFactory::getUser($row->client_id)->name,
            JFactory::getUser($row->trainer_id)->name,
            $clients_names
        );
        $ret['events'][] = $ev;
    }
	}catch(Exception $e){
     $ret['error'] = $e->getMessage();
  }
  return $ret;
}
function listCalendar($day, $type){
  $phpTime = js2PhpTime($day);
  //echo $phpTime . "+" . $type;
  switch($type){
    case "month":
      $st = mktime(0, 0, 0, date("m", $phpTime), 1, date("Y", $phpTime));
      $et = mktime(0, 0, -1, date("m", $phpTime)+1, 1, date("Y", $phpTime));
      break;
    case "week":
      //suppose first day of a week is monday
      $monday  =  date("d", $phpTime) - date('N', $phpTime) + 1;
      //echo date('N', $phpTime);
      $st = mktime(0,0,0,date("m", $phpTime), $monday, date("Y", $phpTime));
      $et = mktime(0,0,-1,date("m", $phpTime), $monday+7, date("Y", $phpTime));
      break;
    case "day":
      $st = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
      $et = mktime(0, 0, -1, date("m", $phpTime), date("d", $phpTime)+1, date("Y", $phpTime));
      break;
  }
  //echo $st . "--" . $et;
  return listCalendarByRange($st, $et, '', '', '', '', '', '');
}

function updateCalendar($id, $st, $et){
  $ret = array();
  $db 	=& JFactory::getDBO();
  try{
    if (checkIfOverlappingThisEvent($id, $st, $et))
    {
        $sql = "update `".DC_MV_CAL."` set"
          . " `".DC_MV_CAL_FROM."`='" . php2MySqlTime(js2PhpTime($st)) . "', "
          . " `".DC_MV_CAL_TO."`='" . php2MySqlTime(js2PhpTime($et)) . "' "
          . "where `id`=" . $id;
        $db->setQuery( $sql );
        if (!$db->query()){
          $ret['IsSuccess'] = false;
          $ret['Msg'] = $db->stderr();
        }else{
          $ret['IsSuccess'] = true;
          $ret['Msg'] = 'Succefully';
        }
    }
    else
         $ret = getMessageOverlapping();
	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  return $ret;
}

function updateDetailedCalendar(
        $id,
        $st, 
        $et, 
        $sub, 
        $ade, 
        $dscr,
        $comments,
        $session_type,
        $session_focus,
        $client_id,
        $trainer_id,
        $loc, 
        $color,
        $frontend_published,
        $published,
        $rrule,
        $rruleType,
        $tz
        ){
 
  $ret = array();
  $db 	=& JFactory::getDBO();

  try{
    if (checkIfOverlapping(
            JRequest::getVar( 'calid' ),
            $st,
            $et,
            $sub,
            $loc,
            $id
        ))
    {
        if ($rruleType=="only")
        {
            return addDetailedCalendar(
                    JRequest::getVar( 'calid' ),
                    $st,
                    $et,
                    $sub,
                    $ade,
                    $dscr,
                    $session_type,
                    $session_focus,
                    $client_id,
                    $trainer_id,
                    $loc,
                    $color,
                    "",
                    $id,
                    $tz
               );   
    

        }        
        else if ($rruleType=="all")
        {
            $sql = "update `".DC_MV_CAL."` set"
              . " `".DC_MV_CAL_FROM."`='" . php2MySqlTime(js2PhpTime($st)) . "', "
              . " `".DC_MV_CAL_TO."`='" . php2MySqlTime(js2PhpTime($et)) . "', "
              . " `".DC_MV_CAL_TITLE."`=" . $db->Quote($sub) . ", "
              . " `".DC_MV_CAL_ISALLDAY."`=" . $db->Quote($ade) . ", "
              . " `".DC_MV_CAL_DESCRIPTION."`=" . $db->Quote($dscr) . ", "
              . " `comments`=" . $db->Quote($comments) . ", "
              . " `session_type`=" . $db->Quote($session_type) . ", "
              . " `session_focus`=" . $db->Quote($session_focus) . ", "
              . " `client_id`=" . $db->Quote($client_id) . ", "
              . " `trainer_id`=" . $db->Quote($trainer_id) . ", "
              . " `".DC_MV_CAL_LOCATION."`=" . $db->Quote($loc) . ", "
              . " `".DC_MV_CAL_COLOR."`=" . $db->Quote($color) . ", "
              . " `frontend_published`=" . $db->Quote($frontend_published) . ", "
              . " `published`=" . $db->Quote($published) . ", "
              . " `rrule`=" . $db->Quote($rrule) . " "
              . "where `id`=" . $id;
            $db->setQuery( $sql );
            if (!$db->query()){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $db->stderr();
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
              $ret['Data'] = $id;
            }
        }        
        else if (substr($rruleType,0,5)=="UNTIL")
        {
            $sql = "select * from `".DC_MV_CAL."` where id=".$id;

            $db->setQuery( $sql );
            $rows = $db->loadObjectList();
            $pre_rrule = $rows[0]->rrule;
            //remove until
            $tmp = explode(";UNTIL=",$pre_rrule);
            if (count($tmp)>1)
            {
                $pre_rrule = $tmp[0];
                $tmp2 = explode(";",$tmp[1]); 
                if (count($tmp2)>1)
                    $pre_rrule .= ";".$tmp2[1]; 
            }
            //add
            $pre_rrule .= ";".$rruleType;
            $sql = "update `".DC_MV_CAL."` set"
              . " `rrule`=" . $db->Quote($pre_rrule) . " "
              . "where `id`=" . $id;
            $db->setQuery( $sql );
            $db->query();
            return addDetailedCalendar(
                    JRequest::getVar( 'calid' ),
                    $st,
                    $et,
                    $sub,
                    $ade,
                    $dscr,
                    $session_type,
                    $session_focus,
                    $client_id,
                    $trainer_id,
                    $loc,
                    $color,
                    "",
                    $id,
                    $tz
             );
        }
        else 
        {
            $sql = "update `".DC_MV_CAL."` set"
              . " `".DC_MV_CAL_FROM."`='" . php2MySqlTime(js2PhpTime($st)) . "', "
              . " `".DC_MV_CAL_TO."`='" . php2MySqlTime(js2PhpTime($et)) . "', "
              . " `".DC_MV_CAL_TITLE."`=" . $db->Quote($sub) . ", "
              . " `".DC_MV_CAL_ISALLDAY."`=" . $db->Quote($ade) . ", "
              . " `".DC_MV_CAL_DESCRIPTION."`=" . $db->Quote($dscr) . ", "
              . " `comments`=" . $db->Quote($comments) . ", "
              . " `session_type`=" . $db->Quote($session_type) . ", "
              . " `session_focus`=" . $db->Quote($session_focus) . ", "
              . " `client_id`=" . $db->Quote($client_id) . ", "
              . " `trainer_id`=" . $db->Quote($trainer_id) . ", "
              . " `".DC_MV_CAL_LOCATION."`=" . $db->Quote($loc) . ", "
              . " `".DC_MV_CAL_COLOR."`=" . $db->Quote($color) . ", "
              . " `frontend_published`=" . $db->Quote($frontend_published) . ", "
              . " `published`=" . $db->Quote($published) . ", "
              . " `rrule`=" . $db->Quote($rrule) . " "
              . "where `id`=" . $id;
            $db->setQuery( $sql );
            if (!$db->query()){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $db->stderr();
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
              $ret['Data'] = $id;
            }
        }
    }
    else
         $ret = getMessageOverlapping();
	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  
  if(JRequest::getVar('assessment_form')) {
      $retAss = updateAssessmentData();
      if(!$retAss['IsSuccess']) $ret = $retAss;
  }

  return $ret;
}

function removeCalendar($id,$rruleType){
  $ret = array();
  $db 	=& JFactory::getDBO();
  try{
        if (substr($rruleType,0,8)=="del_only")
        {
            $sql = "select * from `".DC_MV_CAL."` where id=".$id;

            $db->setQuery( $sql );
            $rows = $db->loadObjectList();
            $exdate = $rows[0]->exdate.substr($rruleType,8);
            
            $sql = "update `".DC_MV_CAL."` set"
              . " `exdate`=" . $db->Quote($exdate) . " "
              . "where `id`=" . $id;
              
            $db->setQuery( $sql );            
            if (!$db->query()){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $db->stderr();
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
        }  
        else if (substr($rruleType,0,9)=="del_UNTIL")
        {
            $sql = "select * from `".DC_MV_CAL."` where id=".$id;

            $db->setQuery( $sql );
            $rows = $db->loadObjectList();
            $pre_rrule = $rows[0]->rrule;
            //remove until
            $tmp = explode(";UNTIL=",$pre_rrule);
            if (count($tmp)>1)
            {
                $pre_rrule = $tmp[0];
                $tmp2 = explode(";",$tmp[1]); 
                if (count($tmp2)>1)
                    $pre_rrule .= ";".$tmp2[1]; 
            }
            //add
            $pre_rrule .= ";".substr($rruleType,4);
            $sql = "update `".DC_MV_CAL."` set"
              . " `rrule`=" . $db->Quote($pre_rrule) . " "
              . "where `id`=" . $id;
            $db->setQuery( $sql );            
            if (!$db->query()){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $db->stderr();
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
            
        }
        else  // $rruleType = "del_all" or ""
        {
            $sql = "delete from `".DC_MV_CAL."` where `id`=" . $id;
	        $db->setQuery( $sql );
            if (!$db->query()){
              $ret['IsSuccess'] = false;
              $ret['Msg'] = $db->stderr();
            }else{
              $ret['IsSuccess'] = true;
              $ret['Msg'] = 'Succefully';
            }
        }
	}catch(Exception $e){
     $ret['IsSuccess'] = false;
     $ret['Msg'] = $e->getMessage();
  }
  return $ret;
}

/** get appointment type by category
 * npkorban
 * @param type $catid
*/
function  get_session_type() {
    $catid = JRequest::getVar("catid");
    $db = & JFactory::getDBO();
    $query = "SELECT id, name FROM #__fitness_session_type WHERE category_id='$catid' AND state='1'";
    $db->setQuery($query);
    $id = $db->loadResultArray(0);
    $name = $db->loadResultArray(1);
    $result = array_combine($id, $name);
    echo  json_encode($result);
    die();
}


/** get appointment type by category
 * npkorban
 * @param type $catid
*/
function  get_session_focus() {
    $catid = JRequest::getVar("catid");
    $session_type = JRequest::getVar("session_type");
    $db = & JFactory::getDBO();
    $query = "SELECT id, name FROM #__fitness_session_focus WHERE category_id='$catid' AND session_type_id='$session_type' AND state='1'";
    $db->setQuery($query);
    $id = $db->loadResultArray(0);
    $name = $db->loadResultArray(1);
    $result = array_combine($id, $name);
    echo  json_encode($result);
    die();
}

/** get appointment type by category
 * npkorban
 * @param type $catid
*/
function  get_trainers() {
    $client_id = JRequest::getVar("client_id");
    $secondary_only = JRequest::getVar("secondary_only");
    $db = & JFactory::getDBO();
    $user = &JFactory::getUser();
    if (getUserGroup($user->id) == 'Super Users') {
        $query = "SELECT primary_trainer, other_trainers FROM #__fitness_clients WHERE user_id='$client_id' AND state='1'";
        $db->setQuery($query);
            $status['success'] = 1;
        if (!$db->query()) {
            $status['success'] = 0;
            $status['message'] = $db->stderr();
        }
        $primary_trainer= $db->loadResultArray(0);
        $other_trainers = $db->loadResultArray(1);
        $other_trainers = explode(',', $other_trainers[0]);
        $all_trainers_id = array_unique(array_merge($primary_trainer, $other_trainers));
    } else {
        $all_trainers_id = array($user->id);
    }
    
    if($secondary_only) {
        $all_trainers_id = $other_trainers;
    }
    
    if(!$all_trainers_id) {
        $status['success'] = 0;
        $status['message'] = 'No trainers assigned to this client.';
    }
    
    foreach ($all_trainers_id as $user_id) {
        $user = &JFactory::getUser($user_id);
        $all_trainers_name[] = $user->name;
    }
    
    $result = array( 'status' => $status, 'data' => array_combine($all_trainers_id, $all_trainers_name));
    echo  json_encode($result);
    die();
}

/** get appointment type by category
 * npkorban
 * @param type $catid
*/
function  get_clients() {
    $trainer_id = JRequest::getVar("trainer_id");
    $db = & JFactory::getDBO();
    $query = "SELECT user_id FROM #__fitness_clients WHERE primary_trainer='$trainer_id' AND state='1'";
    $db->setQuery($query);
    $status['success'] = 1;
    if (!$db->query()) {
        $status['success'] = 0;
        $status['message'] = $db->stderr();
    }
    
    $clients= $db->loadResultArray(0);
    
    if(!$clients) {
        $status['success'] = 0;
        $status['message'] = 'No clients assigned to this trainer.';
    }

    
    foreach ($clients as $user_id) {
        $user = &JFactory::getUser($user_id);
        $clients_name[] = $user->name;
    }
    
    $result = array( 'status' => $status, 'data' => array_combine($clients, $clients_name));
    echo  json_encode($result);
    die();
}


/**
 * set event status, on  click status button
 */
function set_event_status() {
    $event_id = JRequest::getVar("event_id");
    $event_status = JRequest::getVar("event_status");
    $db = & JFactory::getDBO();
    $query = "UPDATE #__dc_mv_events SET status='$event_status' WHERE id='$event_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        echo $db->stderr();
    } else {
        echo $event_status;
    }
    die();
}


/**
 * add event exercise
 * @return type
 */
function add_exercise() {
    $post = JRequest::get('post');
    $db = & JFactory::getDBO();
    $no_fields = array('method', 'layout', 'view', 'option');
    $obj = new stdClass();
    foreach ($post as $key=>$value) {
        if(!in_array($key, $no_fields)) {
            $obj->$key = $value;
        }
    }

    $post['success'] = 1;
    $insert = $db->insertObject('#__fitness_events_exercises', $obj, 'id');
    if(!$insert) {
        $post['success'] = 0;
        $post['message'] = $db->stderr();
    }

    $post['id'] = $db->insertid();
    echo json_encode($post);
    die();
}


/**
 * delete event exercise
 */
function delete_exercise() {
    $exercise_id = JRequest::getVar('exercise_id');
    $db = & JFactory::getDBO();
    $query = "DELETE FROM #__fitness_events_exercises WHERE id='$exercise_id'";
    $db->setQuery($query);
    $post['exercise_id'] = $exercise_id;
    $post['success'] = 1;
    if (!$db->query()) {
        $post['success'] = 0;
        $post['message'] = $db->stderr();
    }
    echo json_encode($post);
    die();
}

/**
 * change event exercises order on drag and drop
 */
function set_event_exircise_order() {
    $row_id = JRequest::getVar('row_id');
    $order = JRequest::getVar('order');
    $db = & JFactory::getDBO();
    $query = "UPDATE `#__fitness_events_exercises` SET `order` = '$order' WHERE `id` ='$row_id'";
    $db->setQuery($query);
        if (!$db->query()) {
        $status['success'] = 0;
        $status['message'] = $db->stderr();
    }
    $status['success'] = 1;
    echo json_encode($status);
    die();
}

/**
 * sends email to the client with appointment content, exercises , etc..
 */
function send_appointment_email($event_id, $type) {
    
    $client_ids = getClientsByEvent($event_id);
    
    switch ($type) {
        case 'confirmation':
            $subject = 'Appointment Confirmation';
            $layout = '&layout=email_reminder';
            break;
        case 'notify':
            $subject = 'Review Your Feedback';
            $layout = '&layout=email_notify';
            break;
        case 'notifyAssessment':
            $subject = 'Assessment Complete';
            $layout = '&layout=email_notify_assessment';
            break;

        default:
            $layout = '';
            $subject = 'Workout/Training Session';
            break;
    }
    

    foreach ($client_ids as $client_id) {
        if(!$client_id) continue;
        
        $url = JURI::base() .'index.php?option=com_multicalendar&view=pdf' . $layout . '&tpml=component&event_id=' . $event_id . '&client_id=' . $client_id;
        
        $contents = getContentCurl($url);
        
        $email = JFactory::getUser($client_id)->email;
        
        $emails[] = $email;
        
        $send = sendEmail($email, $subject, $contents);

        if($send != '1') {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = 'Email function error';
            echo json_encode($ret);
            die();
        }
        if($type == 'confirmation') {
            setSentEmailStatus($event_id, $client_id);
        }
    }

    return $emails;
}


function setSentEmailStatus($event_id, $client_id) {
    $db = & JFactory::getDBO();
    $query = "INSERT INTO #__fitness_email_reminder SET event_id='$event_id', client_id='$client_id', sent='1', confirmed='0'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] =  $db->stderr();
        echo json_encode($ret);
        die();
    } 
}
        
        
function getContentCurl($url) {
        if(!function_exists('curl_version')) {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = 'cURL not anabled';
            echo json_encode($ret);
            die();
        }

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec ($ch);
        curl_close ($ch);
        return $contents;
}

/**
 * standard send email function
 * @param type $recipient
 * @param type $Subject
 * @param type $body
 */
function sendEmail($recipient, $Subject, $body) {

    $mailer = & JFactory::getMailer();

    $config = new JConfig();

    $sender = array($config->mailfrom, $config->fromname);

    $mailer->setSender($sender);

    //$recipient = 'npkorban@mail.ru';

    $mailer->addRecipient($recipient);

    $mailer->setSubject($Subject);

    $mailer->isHTML(true);

    $mailer->setBody($body);

    $send = &$mailer->Send();

    return $send;

}


/** get client email be event id
 * 
 * @param type $event_id
 * @return type
 */
function getClientsByEvent($event_id) {
    
    $db = & JFactory::getDBO();
    $query = "SELECT DISTINCT client_id FROM #__dc_mv_events WHERE id='$event_id' AND client_id !='0'";
    $query .= " UNION ";
    $query .= "SELECT DISTINCT client_id FROM #__fitness_appointment_clients WHERE event_id='$event_id' AND client_id !='0'";

    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] =  $db->stderr();
        echo json_encode($ret);
        die();
    }
    $client_ids = $db->loadResultArray(0);
    $client_ids = array_unique($client_ids);
    return $client_ids;
}

/**
 * 
 * @return type
 */
function update_exercise_field() {
    $exercise_id = &JRequest::getVar('exercise_id');
    $exercise_column = &JRequest::getVar('exercise_column');
    $new_value = &JRequest::getVar('new_value');
    
    switch ($exercise_column) {
        case 1:
            $column = 'title';
            break;
        case 2:
            $column = 'speed';
            break;
        case 3:
            $column = 'weight';
            break;
        case 4:
            $column = 'reps';
            break;
        case 5:
            $column = 'time';
            break;
        case 6:
            $column = 'sets';
            break;
        case 7:
            $column = 'rest';
            break;

        default:
            return;
            break;
    }
    $db = & JFactory::getDBO();
    $query = "UPDATE `#__fitness_events_exercises` SET `$column` = '$new_value' WHERE `id` ='$exercise_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $status['success'] = 0;
        $status['message'] = $db->stderr();
    }
    $status['success'] = 1;
    echo json_encode($status);
    die();
}


function get_semi_clients() {
    $event_id = JRequest::getVar("event_id");
    $db = & JFactory::getDBO();
    $query = "SELECT id, client_id, status FROM #__fitness_appointment_clients WHERE event_id='$event_id'";
    $db->setQuery($query);
    $ids = $db->loadResultArray(0);
    $clients = $db->loadResultArray(1);
    $status= $db->loadResultArray(2);
    
    foreach ($clients as $user_id) {
        $user = &JFactory::getUser($user_id);
        $clients_name[] = $user->name;
    }
    
    $result = array('ids' => $ids, 'clients'=>$clients, 'clients_name'=>$clients_name, 'status'=>$status);
    echo  json_encode($result);
    die();
}

/**
 * 
 * @return type
 */
function add_update_group_client() {
    $event_id = JRequest::getVar("event_id");
    $client_id = JRequest::getVar("client_id");
    $id = JRequest::getVar("id");
    

        
    $db = & JFactory::getDBO();
    $query = "SELECT client_id FROM #__fitness_appointment_clients WHERE event_id='$event_id' AND client_id='$client_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $status['success'] = 0;
        $status['message'] = $db->stderr();
    }
    $client = $db->loadResult();
    
    if($client == $client_id) {
        $user = &JFactory::getUser($client_id);
        $status['success'] = 0;
        $status['message'] = $user->username . ' already added for this appointment';
        echo json_encode($status);
        die();
    }
    
    if($id) {
        $query = "UPDATE `#__fitness_appointment_clients` SET `client_id` = '$client_id' WHERE `id` ='$id'";
        $db->setQuery($query);
        $status['success'] = 1;
        if (!$db->query()) {
            $status['success'] = 0;
            $status['message'] = $db->stderr();
        }
    } else {

        $query = "INSERT  INTO `#__fitness_appointment_clients` (`client_id`,`event_id`,`status`) VALUES ('$client_id', '$event_id', '1')";
        $db->setQuery($query);
        $status['success'] = 1;
        if (!$db->query()) {
            $status['success'] = 0;
            $status['message'] = $db->stderr();
        }
        $status['id'] = $db->insertid();
    }
    echo json_encode($status);
    die();
}

function delete_group_client() {
    $id = JRequest::getVar("id");
    $db = & JFactory::getDBO();
    $query = "DELETE FROM #__fitness_appointment_clients WHERE id='$id'";
    $db->setQuery($query);
    $status['id'] = $id;
    if (!$db->query()) {
        $status['success'] = 0;
        $status['message'] = $db->stderr();
    }
    $status['success'] = 1;
    echo json_encode($status);
    die();
}


/**
 * set event status, on  click status button
 */
function set_group_client_status() {
    $id = JRequest::getVar("id");
    $client_status = JRequest::getVar("client_status");
    $db = & JFactory::getDBO();
    $query = "UPDATE #__fitness_appointment_clients SET status='$client_status'  WHERE id='$id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $status['success'] = 0;
        $status['message'] = $db->stderr();
    }
    $status['ids'] = $id;
    $status['success'] = 1;
    echo json_encode($status);
    die();
}


/** insert / update assessment, foreign key events id
 * 
 * @return type
 */
function updateAssessmentData() {
    $ret['IsSuccess'] = true;
    $post = JRequest::get('post','','POST','STRING',JREQUEST_ALLOWHTML);
    $db = & JFactory::getDBO();
    $fields = array('event_id', 'as_height', 'as_weight', 'as_age', 
        'as_body_fat', 'as_lean_mass', 'as_comments', 'ha_blood_pressure',
        'ha_body_mass_index', 'ha_sit_reach', 'ha_lung_function', 
        'ha_aerobic_fitness', 'ha_comments', 'am_height', 'am_bicep_l',
        'am_weight', 'am_thigh_r', 'am_waist', 'am_thigh_l', 'am_hips', 
        'am_calf_r', 'am_chest', 'am_calf_l', 'am_bicep_r', 'am_comments',
        'bia_body_fat', 'bia_body_water', 'bia_muscle_mass', 'bia_bone_mass', 
        'bia_visceral_fat', 'bio_comments', 'bsm_height', 'bsm_weight', 'bsm_chin',
        'bsm_check', 'bsm_pec', 'bsm_tricep', 'bsm_subscapularis', 'bsm_sum10',
        'bsm_sum12', 'bsm_midaxillary', 'bsm_supraillac', 'bsm_umbilical',
        'bsm_knee', 'bsm_calf', 'bsm_quadricep', 'bsm_hamstring', 'bsm_body_fat',
        'bsm_lean_mass', 'bsm_comments', 'nutrition_protocols', 'supplementation_protocols', 'training_protocols'
    );
    
    $obj = new stdClass();
    
    foreach ($post as $key=>$value) {
        if(in_array($key, $fields)) {
            $obj->$key = trim($value);
        }
    }

    $event_id = $post['event_id'];
    $query = "SELECT assessment_id FROM #__fitness_assessments WHERE event_id='$event_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        return $ret;
    }
    $id = $db->loadResult();
    

    
    if(!$id) {
        $insert = $db->insertObject('#__fitness_assessments', $obj, 'assessment_id');
        if(!$insert) $ret['IsSuccess'] = false;

    } else {
        $obj->assessment_id = $id;
        $update= $db->updateObject('#__fitness_assessments', $obj, 'assessment_id');
        if(!$update) $ret['IsSuccess'] = false;
    }

    return $ret;
}


function getCategoryNameColorById($id) {
    $result['IsSuccess'] = true;
    $db = & JFactory::getDBO();
    $query = "SELECT name, color FROM #__fitness_categories WHERE id='$id' AND state='1'";
    $db->setQuery($query);
    if (!$db->query()) {
        $result['IsSuccess'] = false;
        $result['Msg'] = $db->stderr();
    }
    $result['name'] = $db->loadResultArray(0);
    $result['color'] = $db->loadResultArray(1);

    
    return $result;
}

function saveDragedData() {
    $ret['IsSuccess'] = true;
    $post = JRequest::get('post');
    $starttime = $post['starttime'];
    $field = $post['field'];
    $value = $post['value'];
    $event_id = $post['event_id'];
    $db = & JFactory::getDBO();
    $query = "SELECT id, title,  starttime FROM #__dc_mv_events WHERE id='$event_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
    }

    $event_name = $db->loadResultArray(1);
    $exists = $db->loadResultArray(2);
    
   
    if($field == 'title') {

        $category_name = getCategoryNameColorById($value);

    
        if (!$category_name['IsSuccess']) {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = $category_name['Msg'];
        }
        $value = $category_name['name'][0];
        $color = $category_name['color'][0];

    }

    if($exists) {
        $query = "UPDATE #__dc_mv_events SET $field='$value' WHERE starttime='$starttime'";
        if($field == 'title') {
            $query = "UPDATE #__dc_mv_events SET $field='$value', color='$color' WHERE starttime='$starttime'";
        }
        $db->setQuery($query);
        if (!$db->query()) {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = $db->stderr();
        }

        if (($field == 'client_id') AND !(in_array($event_name[0], array('Personal Training', 'Assessment')))) { // for all categories except Personal Training and Assessment
            $client_id = $value;
            $insertGroupClient = insertGroupClient($event_id, $client_id);
        }
        

    } else {
        if($field == 'title') {
            $post['title'] = $value;
            $post['color'] = $color;
            insertEvent($post);
            if (!(in_array($value, array('Personal Training', 'Assessment')))) { // for all categories except Personal Training and Assessment
                $event_id = $db->insertid();
                $client_id = $post['client_id'];
                if(is_int($client_id)) {
                    $insertGroupClient = insertGroupClient($event_id, $client_id);
                }
            }
            
        } else {
                $ret['IsSuccess'] = false;
                $ret['Msg'] = 'Place appointment first';
        }
    }
    
    echo json_encode($ret);
    die();
}



function insertGroupClient($event_id, $client_id) {
    $ret['IsSuccess'] = true;
    $db = & JFactory::getDBO();
    $query = "SELECT client_id FROM #__fitness_appointment_clients WHERE event_id='$event_id' AND client_id='$client_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        return json_encode($ret);
    }
    $client = $db->loadResult();

    if ($client == $client_id) {
        $user = &JFactory::getUser($client_id);
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $user->username . ' already added for this appointment';
        echo json_encode($ret);
        die();
     }
    $query = "INSERT  INTO #__fitness_appointment_clients (event_id, client_id)
        VALUES ('$event_id', '$client_id')";

    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        echo json_encode($ret);
        die();
    }

    return $ret;
}


function insertEvent($post) {
    $ret['IsSuccess'] = true;
    $db = & JFactory::getDBO();
    $obj = new stdClass();
    $obj->starttime = $post['starttime'];
    $obj->endtime = $post['endtime'];
    if(in_array($post['title'], array('Personal Training', 'Assessment'))) {
        $obj->client_id = $post['client_id'];
    }
    $obj->trainer_id = $post['trainer_id'];
    $obj->location = $post['location'];
    $obj->title = $post['title'];
    $obj->color = $post['color'];
    $obj->calid = JRequest::getVar('calid');
    $obj->published = 1;
    $insert = $db->insertObject('#__dc_mv_events', $obj, 'id');
    $db->setQuery($query);
    if (!$insert) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        echo json_encode($ret);
        die();
    }
    return $ret;
}
       
        
function sendRemindersManually() {
    $db = & JFactory::getDBO();
    $appointments = JRequest::getVar('appointments');
    $appointments = "'" . implode("','", $appointments) . "'";
    $reminder_from = JRequest::getVar('reminder_from');
    $reminder_from_formated = $reminder_from . ' 00:00';
    $reminder_to = JRequest::getVar('reminder_to');
    $reminder_to_formated = $reminder_to . ' 23:59';

    $query = "SELECT id FROM #__dc_mv_events WHERE title IN ($appointments) ";
    if($reminder_from AND $reminder_to) {
        $query .= " AND starttime BETWEEN" . $db->quote($reminder_from_formated) . "AND" . $db->quote($reminder_to_formated);
    }
    $query .= "
        AND title  IN (
        'Personal Training',
        'Semi-Private Training', 
        'Assessment',
        'Consultation',
        'Special Event') 
    ";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        echo json_encode($ret);
        die();
    }
    $event_ids = $db->loadResultArray(0);
    
    $emails = array();
    foreach ($event_ids as $event_id) {
        $emails = array_merge($emails, send_appointment_email($event_id, 'confirmation'));
    }
    
    $emails = implode(', ', $emails);
    //sendEmail('npkorban@gmail.com', 'Appointment details, elitefit.com.au', $emails);
    $ret['IsSuccess'] = true;
    $ret['Msg'] = $emails;
    echo json_encode($ret);
    die();
}


function deleteEvent() {
    $ret['IsSuccess'] = true;
    $event_id = JRequest::getVar('event_id');
    $db = & JFactory::getDBO();
    $query = "DELETE FROM #__dc_mv_events WHERE id='$event_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
    }
    echo json_encode($ret);
    die();
}



function getUserGroup($user_id) {
    if(!$user_id) {
        $user_id = &JFactory::getUser()->id;
    }
    $db = JFactory::getDBO();
    $query = "SELECT title FROM #__usergroups WHERE id IN 
        (SELECT group_id FROM #__user_usergroup_map WHERE user_id='$user_id')";
    $db->setQuery($query);
    return $db->loadResult();
}


/*
 * administration Programs view
 */
function  sendAppointmentEmail($type) {
    $event_id = JRequest::getVar('event_id');
    $emails =  send_appointment_email($event_id, $type);
    $emails = implode(', ', $emails);
    //sendEmail('npkorban@gmail.com', 'Appointment details, elitefit.com.au', $emails);
    $ret['IsSuccess'] = true;
    $ret['Msg'] = $emails;
    echo json_encode($ret);
    die();
}

function sendGoalEmail($type) {
    $goal_id = JRequest::getVar('goal_id');
    $goal_type = JRequest::getVar('goal_type');// 1-> Primary Goal; 2 -> Mini Goal
    switch ($type) {
        case 'email_goal_complete':
            $subject = 'Complete Goal';
            break;
        case 'email_goal_incomplete':
            $subject = 'Incomplete Goal';
            break;
        default:
            $subject = 'Review Your Feedback';
            break;
    }
    $url = JURI::base() .'index.php?option=com_multicalendar&view=pdf&layout=' . $type . '&tpml=component&goal_id=' . $goal_id . '&goal_type=' . $goal_type ;
    
    $contents = getContentCurl($url);
    
    $email = getEmailByGoalId($goal_id);
    
    $send = sendEmail($email, $subject, $contents);

    if($send != '1') {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = 'Email function error';
        echo json_encode($ret);
        die();
    }
    $ret['IsSuccess'] = true;
    $ret['Msg'] = $email;
    echo json_encode($ret);
    die();
}

function getEmailByGoalId($goal_id) {
    $db = & JFactory::getDBO();
    $query = "SELECT user_id FROM #__fitness_goals WHERE id='$goal_id' AND state='1'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        echo json_encode($ret);
        die();
    }
    $user_id = $db->loadResult();
    
    $user = &JFactory::getUser($user_id);
    
    return $user->email;
}
        
// Appointments status emails
function sendAppointmentStatusEmail($type) {
    $ret['IsSuccess'] = true;
    $event_id = JRequest::getVar('event_id');
    $appointment_client_id = JRequest::getVar('appointment_client_id');
    
    if($appointment_client_id == 'personal') {
        $client_id = getClientsByEvent($event_id);
        $client_id = $client_id[0];
    } else {
        $client_id = getClientIdByAppointmentId($appointment_client_id);
    }
    
    switch ($type) {
        case 'email_status_attended':
            $subject = 'Appointment Complete';
            break;
        case 'email_status_cancelled':
            $subject = 'Appointment Cancelled';
            break;
        case 'email_status_late_cancel':
            $subject = 'Late Appointment Cancellation';
            break;
        case 'email_status_no_show':
            $subject = 'You Missed Your Appointment';
            break;
        default:
            return;
            break;
    }
    $url = JURI::base() .'index.php?option=com_multicalendar&view=pdf&layout=' . $type . '&tpml=component&event_id=' . $event_id . '&client_id=' . $client_id;
    
    $contents = getContentCurl($url);
    
    $email = JFactory::getUser($client_id)->email;

    $send = sendEmail($email, $subject, $contents);

    if($send != '1') {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = 'Email function error';
        echo json_encode($ret);
        die();
    }
    echo json_encode($ret);
    die();

}

function getClientIdByAppointmentId($appointment_client_id) {
    $db = & JFactory::getDBO();
    $query = "SELECT client_id FROM #__fitness_appointment_clients WHERE id='$appointment_client_id'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        echo json_encode($ret);
        die();
    } 
    $client_id = $db->loadResult();
    return $client_id;
}

function sendRecipeEmail() {
    $recipe_id= JRequest::getVar('recipe_id');
    
    $subject = 'Your Recipe Approved!';
    
    $url = JURI::base() .'index.php?option=com_multicalendar&view=pdf&layout=email_recipe_approved&tpml=component&recipe_id=' . $recipe_id ;
    
    $contents = getContentCurl($url);
    
    $email = getEmailByRecipeId($recipe_id);
    
    $send = sendEmail($email, $subject, $contents);

    if($send != '1') {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = 'Email function error';
        echo json_encode($ret);
        die();
    }
    $ret['IsSuccess'] = true;
    $ret['Msg'] = $email;
    echo json_encode($ret);
    die();
}

function getEmailByRecipeId($recipe_id) {
    $db = & JFactory::getDBO();
    $query = "SELECT created_by FROM #__fitness_nutrition_recipes WHERE id='$recipe_id' AND state='1'";
    $db->setQuery($query);
    if (!$db->query()) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $db->stderr();
        echo json_encode($ret);
        die();
    }
    $user_id = $db->loadResult();
    
    $user = &JFactory::getUser($user_id);
    
    return $user->email; 
}

jexit();
?>