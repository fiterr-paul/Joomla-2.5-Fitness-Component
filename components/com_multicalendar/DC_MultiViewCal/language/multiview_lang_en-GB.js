var i18n = jQuery.extend({}, i18n || {}, {
    dcmvcal: {
        dateformat: {
            "fulldaykey": "MMddyyyy",
            "fulldayshow": "L d yyyy",
            "fulldayvalue": "M/d/yyyy", 
            "Md": "W M/d",
            "nDaysView": "M/d",
            "Md3": "L d",
            "separator": "/",
            "year_index": 2,
            "month_index": 0,
            "day_index": 1,
            "day": "d",
            "sun2": "Su",
            "mon2": "Mo",
            "tue2": "Tu",
            "wed2": "We",
            "thu2": "Th",
            "fri2": "Fr",
            "sat2": "Sa",
            "sun": "Sun",
            "mon": "Mon",
            "tue": "Tue",
            "wed": "Wed",
            "thu": "Thu",
            "fri": "Fri",
            "sat": "Sat",
            "sunday": "Sunday",
            "monday": "Monday",
            "tuesday": "Tuesday",
            "wednesday": "Wednesday",
            "thursday": "Thursday",
            "friday": "Friday",
            "saturday": "Saturday",
            "jan": "Jan",
            "feb": "Feb",
            "mar": "Mar",
            "apr": "Apr",
            "may": "May",
            "jun": "Jun",
            "jul": "Jul",
            "aug": "Aug",
            "sep": "Sep",
            "oct": "Oct",
            "nov": "Nov",
            "dec": "Dec",
            "l_jan": "January",
            "l_feb": "February",
            "l_mar": "March",
            "l_apr": "April",
            "l_may": "May",
            "l_jun": "June",
            "l_jul": "July",
            "l_aug": "August",
            "l_sep": "September",
            "l_oct": "October",
            "l_nov": "November",
            "l_dec": "December"
        },
        "no_implemented": "No implemented yet",
        "to_date_view": "Click to the view of current date",
        "i_undefined": "Undefined",
        "allday_event": "All day event",
        "repeat_event": "Repeat event",
        "time": "Time",
        "event": "Event",
        "location": "Location",
        "participant": "Participant",
        "get_data_exception": "Exception when getting data",
        "new_event": "New event",
        "confirm_delete_event": "Do you confirm to delete this event? ",
        "confrim_delete_event_or_all": "Do you want to delete all repeat events or only this event? \r\nClick [OK] to delete only this event, click [Cancel] delete all events",
        "data_format_error": "Data format error! ",
        "invalid_title": "Event title can not be blank or contains ($<>)",
        "view_no_ready": "View is not ready",
        "example": "",
        "content": "What",
        "create_event": "Create event",
        "update_detail": "Edit details",
        "click_to_detail": "View details",
        "i_delete": "Delete",
        "i_save": "Save",
        "i_close": "Close",
        "day_plural": "days",
        "others": "Others",
        "item": "",
        "loading_data":"Loading data...",
        "request_processed":"The request is being processed ...",
        "success":"Success!",
        "are_you_sure_delete":"WARNING! Deleting this event will also delete the 'Workout Program' or ‘Assessment’\nattached to this event. Are you sure you wish to continue?",
        "ok":"Ok",
        "cancel":"Cancel",
        "manage_the_calendar":"Manage The Calendar",
        "error_occurs":"Error occurs",
        "color":"Color",
        "invalid_date_format":"Invalid date format",
        "invalid_time_format":"Invalid time format",
        "_simbol_not_allowed":"$<> not allowed",
        "subject":"Subject",
        "time":"Time",
        "to":"To",
        "all_day_event":"All Day Event",
        "location":"Location",
        "remark":"Description",
        "click_to_create_new_event":"Click to Create New Event",
        "new_event":"New Event",
        "click_to_back_to_today":"Click to back to today",
        "today":"Today",
        "sday":"Day",
        "week":"Week",
        "month":"Month",
        "ndays":"Days",
        "nmonth":"nMonth",
        "refresh_view":"Refresh view",
        "refresh":"Refresh",
        "prev":"Prev",
        "next":"Next",
        "loading":"Loading",
        "error_overlapping":"This event is overlapping another event",
        "sorry_could_not_load_your_data":"Sorry, could not load your data, please try again later",
        "first":"First",
        "second":"Second",
        "third":"Third",
        "fourth":"Fourth",
        "last":"last",
        "repeat":"Repeat: ",
        "edit":"Edit",
        "edit_recurring_event":"Edit recurring event",
        "would_you_like_to_change_only_this_event_all_events_in_the_series_or_this_and_all_following_events_in_the_series":"Would you like to change only this event, all events in the series, or this and all following events in the series?",
        "only_this_event":"Only this event",
        "all_other_events_in_the_series_will_remain_the_same":"All other events in the series will remain the same.",
        "following_events":"Following events",
        "this_and_all_the_following_events_will_be_changed":"This and all the following events will be changed.",
        "any_changes_to_future_events_will_be_lost":"Any changes to future events will be lost.",
        "all_events":"All events",
        "all_events_in_the_series_will_be_changed":"All events in the series will be changed.",
        "any_changes_made_to_other_events_will_be_kept":"Any changes made to other events will be kept.",
        "cancel_this_change":"Cancel this change",
        "delete_recurring_event":"Delete recurring event",
        "would_you_like_to_delete_only_this_event_all_events_in_the_series_or_this_and_all_future_events_in_the_series":"Would you like to delete only this event, all events in the series, or this and all future events in the series?",
        "only_this_instance":"Only this instance",
        "all_other_events_in_the_series_will_remain":"All other events in the series will remain.",
        "all_following":"All following",
        "this_and_all_the_following_events_will_be_deleted":"This and all the following events will be deleted.",
        "all_events_in_the_series":"All events in the series",
        "all_events_in_the_series_will_be_deleted":"All events in the series will be deleted.",
        "repeats":"Repeats",
        "daily":"Daily",
        "every_weekday_monday_to_friday":"Every weekday (Monday to Friday)",
        "every_monday_wednesday_and_friday":"Every Monday, Wednesday, and Friday",
        "every_tuesday_and_thursday":"Every Tuesday, and Thursday",
        "weekly":"Weekly",
        "monthly":"Monthly",
        "yearly":"Yearly",
        "repeat_every":"Repeat every:",
        "weeks":"weeks",
        "repeat_on":"Repeat on:",
        "repeat_by":"Repeat by:",
        "day_of_the_month":"day of the month",
        "day_of_the_week":"day of the week",
        "starts_on":"Starts on:",
        "ends":"Ends:",
        "never":" Never",
        "after":"After",
        "occurrences":"occurrences",
        "summary":"Summary:",
        "every":"Every",
        "weekly_on_weekdays":"Weekly on weekdays",
        "weekly_on_monday_wednesday_friday":"Weekly on Monday, Wednesday, Friday",
        "weekly_on_tuesday_thursday":"Weekly on Tuesday, Thursday",
        "on":"on",
        "on_day":"on day",
        "on_the":"on the",
        "months":"months",
        "annually":"Annually",
        "years":"years",
        "once":"Once",
        "times":"times",
        "until":"until"
    }
});
