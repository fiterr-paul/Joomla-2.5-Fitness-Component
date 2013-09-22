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
function getTrainingPeriods() {
    // Training Period List
    $db = JFactory::getDbo();
    $sql = "SELECT * FROM #__fitness_training_period WHERE state='1'";
    $db->setQuery($sql);
    $training_periods = $db->loadObjectList();

    foreach ($training_periods as $item) {
        $color = '<div style="float:left;margin-right:5px;width:15px; height:15px;background-color:' . $item->color . '" ></div>';
        $name = '<div class="grey_title"> ' . $item->name . '</div>';
        $html .= $color . $name ;
    }
    return $html;
}
?>
<div style="opacity: 1;" class="fitness_wrapper">
    <h2>GOALS & TRAINING PERIODS</h2>
    <div class="fitness_content_wrapper">
        <div  style="width:100%; text-align: right;">
            <a  id="by_year" href="javascript:void(0)">[Current Year]</a>
            <a  id="by_month" href="javascript:void(0)">[Current Month]</a>
        </div>
        <fieldset style="width:140px !important; margin-left: 0px; float: left;margin-top: 36px;">
            <legend class="grey_title">Training Period Keys</legend>
            <?php echo getTrainingPeriods();?>
        </fieldset>
        <div class="graph-container" style="width:800px;">

            <div id="placeholder" class="graph-placeholder"></div>

        </div>
    </div>
</div>

<div id="goal_container" class="fitness_wrapper">

</div>



<script type="text/javascript">
    
    (function($) {
        
        var options = {
            'fitness_frontend_url' : '<?php echo JURI::root();?>index.php?option=com_fitness&tmpl=component&<?php echo JSession::getFormToken(); ?>=1',
            'calendar_frontend_url' : '<?php echo JURI::root()?>index.php?option=com_multicalendar&task=load&calid=0',
            'pending_review_text' : 'Pending Review',
            'user_name' : '<?php echo JFactory::getUser()->name;?>',
            'goals_db_table' : '#__fitness_goals',
            'minigoals_db_table' : '#__fitness_mini_goals',
            'goals_comments_db_table' : '#__fitness_goal_comments',
            'minigoals_comments_db_table' : '#__fitness_mini_goal_comments'
        }
        

        //// Goal Model
        Goal_model = Backbone.Model.extend({
            defaults: {
                'pages_number' : 10,
                'list_type' : '0'
            },
            
            initialize: function(){

            },
            
            addGoal : function(data) {
                
                var goal_type = this.get('goal_type');
                var url = this.get('fitness_frontend_url');
                var view = 'goals_periods';
                
                var task = 'addGoal';
                var table = this.get('goals_db_table');
                
                if(goal_type == 'mini_goal') {
                    var table = this.get('minigoals_db_table');
                    data.primary_goal_id = this.get('primary_goal_id')
                }
                
                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("saved_item", output);
                });
            },

            populateGoals : function() {
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'goals_periods';
                var task = 'populateGoals';
                var table = '';
                var list_type= this.getLocalStorageItem('list_type');
                data.list_type = list_type;
                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    //console.log(output);
                    self.set("goals", output);
                });
            },

            ajaxCall : function(data, url, view, task, table, handleData) {
                return $.AjaxCall(data, url, view, task, table, handleData);
            },
            setStatus : function(status) {
                var style_class;
                var text;
                switch(status) {
                    case '1' :
                        style_class = 'goal_status_pending';
                        text = 'PENDING';
                        break;
                    case '2' :
                        style_class = 'goal_status_complete';
                        text = 'COMPLETE';
                        break;
                    case '3' :
                        style_class = 'goal_status_incomplete';
                        text = 'INCOMPLETE';
                        break;
                    case '4' :
                        style_class = 'goal_status_evaluating';
                        text = 'EVALUATING';
                        break;
                    case '5' :
                        style_class = 'goal_status_inprogress';
                        text = 'IN PROGRESS';
                        break;
                    default :
                        style_class = 'goal_status_evaluating';
                        text = 'EVALUATING';
                        break;
                }
                var html = '<a href="javascript:void(0)"  class="status_button ' + style_class + '">' + text + '</a>';
                return html;
            },
            
            setDefaultText : function(status, string) {
                if(!this.statusReviewed(status)) return this.attributes.pending_review_text;
                return string;
            },
            
            statusReviewed : function(status) {
                if((status == '4') || (status == '0') || (status == '')) return false;
                return true;
            },
            
            checkLocalStorage : function() {
                if(typeof(Storage)==="undefined") {
                   return false;
                }
                return true;
            },
            
            
            setLocalStorageItem : function(name, value) {
                if(!this.checkLocalStorage) return;
                localStorage.setItem(name, value);
            },
            getLocalStorageItem : function(name) {
                var value = this.get(name);
                if(!this.checkLocalStorage) {
                    return value;
                }
                
                var store_value =  localStorage.getItem(name);
                
                if(!store_value) return value;
                
                return localStorage.getItem(name);
            }
        });
        
        
        //// 
        Goals_graph_model = Backbone.Model.extend({
            defaults: {
            },
            initialize: function(goals){
                this.setGraphData(goals);
            },
            setGraphData : function(goals) {
                var data = {};
                var primary_goals_data = this.setPrimaryGoalsGraphData(goals.primary_goals);
                $.extend(true,data, primary_goals_data);
                var mini_goals_data = this.setMiniGoalsGraphData(goals.mini_goals);
                $.extend(true,data, mini_goals_data);
                this.drawGraph(data);
            },
            setPrimaryGoalsGraphData : function(primary_goals) {
                var data = {};
                data.primary_goals = this.x_axisDateArray(primary_goals, 2, 'deadline');
                data.client_primary = this.graphItemDataArray(primary_goals, 'client_name');
                data.goal_primary = this.graphItemDataArray(primary_goals, 'primary_goal_name');
                data.start_primary = this.graphItemDataArray(primary_goals, 'start_date');
                data.finish_primary = this.graphItemDataArray(primary_goals, 'deadline');
                data.status_primary = this.graphItemDataArray(primary_goals, 'status');
                return data;
            },
            setMiniGoalsGraphData : function(mini_goals) {
                var data = {};
                data.mini_goals = this.x_axisDateArray(mini_goals, 1, 'deadline');
                data.client_mini = this.graphItemDataArray(mini_goals, 'client_name');
                data.goal_mini = this.graphItemDataArray(mini_goals, 'mini_goal_name');
                data.start_mini = this.graphItemDataArray(mini_goals, 'start_date');
                data.finish_mini = this.graphItemDataArray(mini_goals, 'deadline');
                data.status_mini = this.graphItemDataArray(mini_goals, 'status');
                data.training_period_colors = this.graphItemDataArray(mini_goals, 'training_period_color');
                return data;
            },
            x_axisDateArray : function(data, y_value, field) {
                var x_axis_array = []; 
                for(var i = 0; i < data.length; i++) {
                    var unix_time = new Date(Date.parse(data[i][field])).getTime();
                    x_axis_array[i] = [unix_time, y_value];
                }
                return x_axis_array;
            },
            graphItemDataArray : function(data, type) {
                var items = []; 
                for(var i = 0; i < data.length; i++) {
                    items[i] = data[i][type];
                }
                return items;
            },
            drawGraph : function(client_data) {
                var self = this;
                //TIME SETTINGS
                var current_time = new Date().getTime();
                var start_year = new Date(new Date().getFullYear(), 0, 1).getTime();
                var end_year = new Date(new Date().getFullYear(), 12, 0).getTime();
                
                var date = new Date();
                var firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getTime() - 60*59*24 * 1000;
                var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getTime() + 60*59*24 * 1000;
                // END TIME SETTINGS
 
                // DATA
                // Primary Goals
                var d1 = client_data.primary_goals;
                
            
                
                // Mini Goals
                var d2 = client_data.mini_goals;
                // Current Time
                var d8 = [[current_time, 3]];
                // Training Periods colors
                var training_period_colors = client_data.training_period_colors;
                console.log(training_period_colors);
                // Training periods 
                var markings = []; 
                for(var i = 0; i < d2.length - 1; i++) {
                    markings[i] =  { xaxis: { from: d2[i][0], to: d2[i + 1][0] }, yaxis: { from: 0.25, to: 0.75 }, color: training_period_colors[i+1]};
                }
                // first Primary Goal marking
                var first_mini_goal_start_date = new Date(client_data.start_mini[0]).getTime();
                if(first_mini_goal_start_date) {
                    markings[markings.length] =  { xaxis: { from: first_mini_goal_start_date, to: d2[0][0] }, yaxis: { from: 0.25, to: 0.75 }, color: training_period_colors[0]};
                }
        
                var data = [
                    {label: "Primary Goal", data: d1},
                    {label: "Mini Goal", data: d2},
                    {label: "Current Time", data: d8}
                ];
                // END DATA
                
                // START OPTIONS
                // base common options
                var options = {
                    xaxis: {mode: "time", timezone: "browser"},
                    yaxis: {show: false},
                    series: {
                        lines: {show: false },
                        points: {show: true, radius: 5, symbol: "circle", fill: true, fillColor: "#FFFFFF" },
                        bars: {show: true, lineWidth: 3},
                    },
                    grid: {
                        hoverable: true,
                        clickable: true,
                        backgroundColor: {
                             colors: ["#0E0704", "#0E0704"]
                        },
                        markings: markings,
                        color: "#C0C0C0"
                    },
                    legend: {show: true, margin: [0, 0], backgroundColor: "none", labelBoxBorderColor:"none"},

                    colors: [
                        "#A3270F",// Primary Goal
                        "#287725", // Mimi Goal
                        "#FFB01F"// Current Time
                    ]
                };
                // year options
                var options_year = { xaxis: {tickSize: [1, "month"], min: start_year, max: end_year}};
                $.extend(true,options_year, options);
                // month options
                var options_month = { xaxis: {tickSize: [1, "day"], min:  firstDay, max: lastDay, timeformat: "%d"}};
                $.extend(true,options_month, options);
                
                var current_options = {
                    get : function() {return this.options;},
                    set : function(options) {this.options = options}
                };
                current_options = options_year;
                
                // by year
                $("#by_year").click(function() {
                    current_options = options_year;
                    self.plotAccordingToChoices(data, current_options);
                });
                // by month
                $("#by_month").click(function() {
                    current_options = options_month;
                    self.plotAccordingToChoices(data, current_options);
                });
                
                this.plotAccordingToChoices(data, current_options);
                
                $("<div id='tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    border: "2px solid #cccccc",
                    "border-radius": "10px",
                    padding: "5px",
                    "background-color": "#fee",
                    opacity: 0.9,
                    color: "#fff",
                    "text-align" : "left",
                }).appendTo("body");
                
                $("#placeholder").bind("plothover", function (event, pos, item) {
                    if (item) {
                        var data_type = item.datapoint[1];
                        var html = "<p style=\"text-align:center;\"><b>" +  item.series.label + "</b></p>";

                        switch(data_type) {
                            case 1 : // Mini Goals
                                html +=  "Client: " +  client_data.client_mini[item.dataIndex] + "</br>";
                                html +=  "Goal: " +  (client_data.goal_mini[item.dataIndex] || '') + "</br>";
                                html +=  "Start: " +  client_data.start_mini[item.dataIndex] + "</br>";
                                html +=  "Finish: " +  client_data.finish_mini[item.dataIndex] + "</br>";
                                html +=  "Status: " +  (self.getStatusById(client_data.status_mini[item.dataIndex]) || '') + "</br>"; 
                                $("#tooltip").css("background-color", "#287725");
                                break;
                            case 2 : // Primary Goals
                                html +=  "Client: " +  client_data.client_primary[item.dataIndex] + "</br>";
                                html +=  "Goal: " +  (client_data.goal_primary[item.dataIndex] || '') + "</br>";
                                html +=  "Start: " +  client_data.start_primary[item.dataIndex] + "</br>";
                                html +=  "Finish: " +  client_data.finish_primary[item.dataIndex] + "</br>";
                                html +=  "Status: " +  (self.getStatusById(client_data.status_primary[item.dataIndex]) || '') + "</br>"; 
                                $("#tooltip").css("background-color", "#A3270F");
                                break;
                            case 3 : // Current Time
                                html =  "Current Time" ;
                                $("#tooltip").css("background-color", "#FFB01F");
                                break;
                            default :
                                break;
                        }

                        $("#tooltip").html(html)
                            .css({top: item.pageY+5, left: item.pageX+5})
                            .fadeIn(200);
                    } else {
                            $("#tooltip").hide();
                    }

                });
                
                
             },
             plotAccordingToChoices : function(data, options) {
                if (data.length > 0) {
                        $.plot("#placeholder", data, options);
                }
            },
            getStatusById : function(id) {
                var status_name;
                switch(id) {
                    case '1' : 
                       status_name = 'Pending';
                       break;
                    case '2' :
                       status_name = 'Complete';
                       break;
                    case '3' :
                       status_name = 'Incomplete';
                    case '4' :
                       status_name = 'Evaluating';
                       break;
                    case '5' :
                       status_name = 'In Progress';
                    default :
                       status_name = 'Evaluating';
                       break;
                }
                return status_name;
            }

        });


        ///// Add view   
        Add_goal_view = Backbone.View.extend({
            initialize: function(){
                this.model = this.options.model;
                this.model.set({'goal_type' : this.options.goal_type, 'primary_goal_id' : this.options.primary_goal_id});
                this.listenToOnce(this.model, "change:saved_item", this.onItemAdded);
                
                this.render();
                
            },
            render: function(){
                this.loadTemplate();
                this.loadPlugins();
            },
            loadTemplate : function() {
                var variables = {
                    'title' : this.options.title
                }
                var template = _.template( $("#add_goal_template").html(), variables );
                this.$el.html( template );
            },
            onItemAdded : function() {
                if (this.model.has("saved_item")){
                    //console.log(this.model);
                };
            },
            loadPlugins: function(){
                
                var model_attr = this.model.attributes;
                var primary_goal_obj = _.find(model_attr.goals.primary_goals, function(obj) { return obj.id == model_attr.primary_goal_id });
                var start_date = primary_goal_obj.start_date;
                var deadline = primary_goal_obj.deadline;
                var min_date = new Date(Date.parse(start_date));
                var max_date = new Date(Date.parse(deadline));
    
                $( "#start_date, #deadline" ).datepicker({ dateFormat: "yy-mm-dd", minDate: min_date, maxDate: max_date });
                $("#add_goal_form").validate();
            },
            events: {
                "click #cancel_add_goal" : "cancelAddGoal",
                "submit #add_goal_form" : "addGoal"
            },
            addGoal : function() {
                
                var data = {
                    'start_date' : $("#start_date").val(),
                    'deadline' : $("#deadline").val(),
                    'details' : $("#details").val()
                };

                this.model.addGoal(data);
                
                var default_list_view = new Default_list_view({ el: $("#goal_container") });
                
                this.undelegateEvents();
            },
            cancelAddGoal : function() {
                this.undelegateEvents();
                var default_list_view = new Default_list_view({ el: $("#goal_container") });
            },

        });
        
        
        /// Goal view
        Goal_view = Backbone.View.extend({
            initialize: function(){
                this.model = this.options.model;
                this.model.set({'goal_type' : this.options.goal_type, 'id' : this.options.id, 'comments' : this.options.comments});
                this.render();
            },
            render: function(){
                this.loadTemplate();
                var comments_html = this.model.attributes.comments.run();
                $("#comments_wrapper").html(comments_html);
            },
            loadTemplate : function() {
                var model = this.model;
                var variables = {
                    'title' : this.options.title,
                    'model' : model,
                }
                var template = _.template( $("#goal_template").html(), variables);
                this.$el.html( template );
            },
            events: {
                "click #cancel_goal" : "cancelGoal",
            },
            cancelGoal : function() {
                this.undelegateEvents();
                var default_list_view = new Default_list_view({ el: $("#goal_container") });
            },
        });



        
        //// LIst view
        Default_list_view = Backbone.View.extend({
            initialize: function(){
                this.model = new Goal_model(options);
                this.render();
            },
            render: function(){
                this.model.populateGoals();
                this.loadTemplate();
                this.listenToOnce(this.model, "change:goals", this.onPopulateGoals);
            },
            loadTemplate : function() {
                var variables = {
                    
                }
                var template = _.template( $("#default_goal_list_template").html(), variables );
                this.$el.html( template );
                var pages_number = this.model.getLocalStorageItem('pages_number');
                var list_type= this.model.getLocalStorageItem('list_type');
                $("#items_number").val(pages_number);
                $("#list_type").val(list_type);
                
            },
            events: {
                "click #new_goal" : "addGoal",
                "click .new_mini_goal" : "addMiniGoal",
                "click .open_goal" : "openGoal",
                "click .open_mini_goal" : "openMiniGoal",
                "change #items_number" : "setPagination",
                "change #list_type" : "runList"
            },
            onPopulateGoals : function() {
                if (this.model.has("goals")){
                    var model = this.model;
                    // init Graph
                    this.graph_data = new Goals_graph_model(this.model.attributes.goals);
                    var variables = {
                        'model' : model,
                    }
                    var template = _.template( $("#primary_goal_template").html(), variables);
                    $("#goals_wrapper").html(template);
                    
                };  
            },
            addGoal : function(event) {
                var add_goal_view = new Add_goal_view({ el: $("#goal_container"), 'model' : this.model, 'goal_type' : 'primary_goal', 'title' : 'CREATE PRIMARY GOAL' });
                this.undelegateEvents();
            },
            addMiniGoal : function(event) {
                var primary_goal_id = $(event.target).data('id');
                var add_goal_view = new Add_goal_view({ el: $("#goal_container"), 'model' : this.model, 'goal_type' : 'mini_goal', 'primary_goal_id' : primary_goal_id, 'title' : 'CREATE MINI GOAL'});
                this.undelegateEvents();
            },
            openGoal : function(event) {
            
                var id = $(event.target).data('id');
                
                var comment_options = {
                    'item_id' : id,
                    'fitness_administration_url' : this.model.attributes.fitness_frontend_url,
                    'comment_obj' : {'user_name' : this.model.attributes.user_name, 'created' : "", 'comment' : ""},
                    'db_table' : this.model.attributes.goals_comments_db_table,
                    'read_only' : true
                }
                var comments = $.comments(comment_options, comment_options.item_id, 0);
                
                var add_goal_view = new Goal_view({ el: $("#goal_container"), 'model' : this.model, 'comments' : comments, 'goal_type' : 'primary_goal', 'id' : id, 'title' : 'MY PRIMARY GOAL' });
                this.undelegateEvents();
            },
            openMiniGoal : function(event) {
                var id = $(event.target).data('id');
                
                var comment_options = {
                    'item_id' : id,
                    'fitness_administration_url' : this.model.attributes.fitness_frontend_url,
                    'comment_obj' : {'user_name' : this.model.attributes.user_name, 'created' : "", 'comment' : ""},
                    'db_table' : this.model.attributes.minigoals_comments_db_table,
                    'read_only' : true
                }
                var comments = $.comments(comment_options, comment_options.item_id, 0);
                
                var add_goal_view = new Goal_view({ el: $("#goal_container"), 'model' : this.model, 'comments' : comments, 'goal_type' : 'mini_goal', 'id' : id, 'title' : 'MY MINI GOAL'});
                this.undelegateEvents();
            },
            setPagination : function(event) {
                var pages_number = $(event.target).val();
                        
                this.initialize();

                $("#items_number").val(pages_number);
                
                       
                this.model.setLocalStorageItem('pages_number', pages_number);

 
            },
            runList : function(event) {
                var list_type = $(event.target).val();
                this.model.setLocalStorageItem('list_type', list_type);
                this.initialize();
                $("#list_type").val(list_type);
                
                //console.log(this.model.getLocalStorageItem('list_type'));
            }
        });

        var default_list_view = new Default_list_view({ el: $("#goal_container") });

        
        
    })($js);

    
</script>



