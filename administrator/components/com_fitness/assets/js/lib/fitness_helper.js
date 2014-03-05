(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], factory);
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {
        function FitnessHelper(options) {
             //// Helper Model
            Helper_model = Backbone.Model.extend({
                defaults: {
                    'default_recipe_image' : '/administrator/components/com_fitness/assets/images/no_image.png'
                },

                initialize: function(){

                },

                ajaxCall : function(data, url, view, task, table, handleData) {
                    return $.AjaxCall(data, url, view, task, table, handleData);
                },

                getClientsByBusiness : function(view, business_profile_id, task, user_id) {
                    var data = {};
                    var url = this.get('ajax_call_url');
                    var view = view;
                    var task = task;
                    var table = '';
                    data.business_profile_id = business_profile_id;
                    data.user_id = user_id;//current logged user

                    var self = this;
                    this.set("clients", {});
                    this.ajaxCall(data, url, view, task, table, function(output) {
                        self.set("clients", output);
                    });
                },

                getTrainersByBusiness : function(view, business_profile_id, user_id) {
                    var data = {};
                    var url = this.get('ajax_call_url');
                    var view = view;
                    var task = 'onBusinessNameChange';
                    var table = '#__fitness_business_profiles';
                    data.business_profile_id = business_profile_id;
                    data.user_id = user_id;//current logged user

                    var self = this;
                    this.set("trainers", {});
                    this.ajaxCall(data, url, view, task, table, function(output) {
                        self.set("trainers", output);
                    });
                },

                populateSelect : function(data, target, selected_value) {
                    var html = '<option  value="">-Select-</option>';
                    $.each(data, function(index, value) {
                        if(index) {
                            var selected = '';
                            if(selected_value == index) {
                                selected = 'selected';
                            }
                            html += '<option ' + selected + ' value="' + index + '">' +  value + '</option>';
                        }
                    });
                    $(target).html(html);
                    return html;
                },

                populateUsersSelectOnBusiness : function(task, model, business_profile_id, target, selected, user_id) {

                    this.getClientsByBusiness(model, business_profile_id, task, user_id);
                    this.on('change:clients', function(model, items) {
                        model.populateSelect(items, target, selected);
                    });
                },

                populateClientsSelectOnBusiness : function(task, model, business_profile_id, target, selected, user_id) {
                    this.populateUsersSelectOnBusiness(task, model, business_profile_id, target, selected, user_id);
                },

                populateTrainersSelectOnBusiness : function(model, business_profile_id, target, selected, user_id) {
                    this.getTrainersByBusiness(model, business_profile_id, user_id);
                    this.on('change:trainers', function(model, items) {
                        model.populateSelect(items, target, selected);
                    });
                },

                hideSelectOption : function(value, element, all_options) {
                    $(element).html(all_options);
                    $(element + " option[value=" + value + "]").remove();
                },

                excludeSelectOption : function(select1, select2) {
                    var self = this;
                    $(select1).live('change', function() {
                        var all_options_select2 = $(select2).html();
                        var value = $(this).val();
                        self.hideSelectOption(value, select2, all_options_select2);
                    });
                    //on load
                    var value = $(select1).val();
                    if(value) {
                        var all_options_select2 = $(select2).html();
                        this.hideSelectOption(value, select2, all_options_select2);
                    }
                },

                setRecipeStatus : function(status) {
                    var style_class;
                    var text;
                    switch(status) {
                        case '1' :
                            style_class = 'recipe_status_pending';
                            text = 'PENDING';
                            break;
                        case '2' :
                            style_class = 'recipe_status_approved';
                            text = 'APPROVED';
                            break;
                        case '3' :
                            style_class = 'recipe_status_notapproved';
                            text = 'NOT APPROVED';
                            break;

                        default :
                            style_class = 'recipe_status_pending';
                            text = 'PENDING';
                            break;
                    }
                    var html = '<a style="cursor:default;" href="javascript:void(0)"  class="recipe_status_button ' + style_class + '">' + text + '</a>';
                    return html;
                },
                
                setMenuPlanStatus : function(status, id) {
                    var style_class;
                    var text;
                    switch(status) {
                        case '1' :
                            style_class = 'menu_plan_status_pending';
                            text = 'PENDING';
                            break;
                        case '2' :
                            style_class = 'recipe_status_approved';
                            text = 'APPROVED';
                            break;
                        case '3' :
                            style_class = 'recipe_status_notapproved';
                            text = 'NOT APPROVED';
                            break;
                        case '4' :
                            style_class = 'status_inprogress';
                            text = 'IN PROGRESS';
                            break;
                        case '5' :
                            style_class = 'status_submitted';
                            text = 'SUBMITTED';
                            break;
                        case '6' :
                            style_class = 'status_fail';
                            text = 'RESUBMIT';
                            break;

                        default :
                            style_class = 'recipe_status_pending';
                            text = 'PENDING';
                            break;
                    }
                    var html = '<a  data-item_id="' + id + '" style="cursor:default;" href="javascript:void(0)"  class="status_button ' + style_class + '">' + text + '</a>';
                    return html;
                },
                
                setDiaryStatus : function(status) {
                    var style_class;
                    var text;
                    switch(status) {
                        case '1' :
                            style_class = 'status_inprogress';
                            text = 'IN PROGRESS';
                            break;
                        case '2' :
                            style_class = 'status_pass';
                            text = 'PASS';
                            break;
                        case '3' :
                            style_class = 'status_fail';
                            text = 'FAIL';
                            break;
                        case '4' :
                            style_class = 'status_distinction';
                            text = 'DISTINCTION';
                            break;
                        case '5' :
                            style_class = 'status_submitted';
                            text = 'SUBMITTED';
                            break;
                        default :
                            style_class = 'status_inprogress';
                            text = 'IN PROGRESS';
                            break;
                    }
                    var html = '<a style="cursor:default;" href="javascript:void(0)"  class="status_button ' + style_class + '">' + text + '</a>';
                    return html;
                },
                
                setGoalStatus : function(status) {
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
                        case '6' :
                            style_class = 'goal_status_assessing';
                            text = 'ASSESSING';
                            break;
                        default :
                            style_class = 'goal_status_evaluating';
                            text = 'EVALUATING';
                            break;
                    }
                    var html = '<a style="cursor:default;" href="javascript:void(0)"  class="status_button ' + style_class + '">' + text + '</a>';
                    return html;
                },

                status_html_stamp : function(status) {
                    var class_name, text;
                    switch(status) {
                        case '2' :
                            class_name = 'status_pass_stamp';
                            break;
                        case '3' :
                            class_name = 'status_fail_stamp';

                            break;
                        case '4' :
                            class_name = 'status_distinction_stamp';
                            break;
                        case '5' :
                            class_name = 'status_submitted_stamp';
                            break;
                        default :
                            break;
                    }

                    var html = '<div class=" status_button_stamp ' + class_name + '"></div>';

                    return html;
                },
                
                setExerciseLibraryStatus : function(status, id) {
                    var style_class;
                    var text;
                    switch(status) {
                        case '1' :
                            style_class = 'goal_status_pending';
                            text = 'PENDING';
                            break;
                        case '2' :
                            style_class = 'recipe_status_approved';
                            text = 'APPROVED';
                            break;
                        case '3' :
                            style_class = 'recipe_status_notapproved';
                            text = 'NOT APPROVED';
                            break;
                        default :
                            style_class = 'recipe_status_pending';
                            text = 'PENDING';
                            break;
                    }
                    var html = '<a  data-item_id="' + id + '" style="cursor:default;" href="javascript:void(0)"  class="status_button ' + style_class + '">' + text + '</a>';
                    return html;
                },

                printPage : function(htmlPage) {
                    var w = window.open(htmlPage);
                    setTimeout(function(){w.print()},3000);
                    return false
                },

                sendEmail : function(o) {
                    var data = {};
                    data.id = o.id;
                    data.view = o.view;
                    data.method = o.method;
                    data.checked = o.checked;
                    
                    this.ajaxCall(data, o.url, o.view, o.task, o.table, function(output) {
                        var emails = output.split(',');
                        var message = 'Emails were sent to: ' +  "</br>";
                        $.each(emails, function(index, email) { 
                            message += email +  "</br>";
                        });
                        $("#emais_sended").append(message);
                    });
                },
                
                copy_recipe : function(recipe_id){
                    var data = {};
                    var url = this.get('ajax_call_url');
                    var view = 'recipe_database';
                    var task = 'copyRecipe';
                    var table = this.get('recipes_db_table');

                    data.id = recipe_id;

                    var self = this;
                    this.ajaxCall(data, url, view, task, table, function(output) {
                        self.set("recipe_copied", output);
                        //console.log(output);
                    });
                },
                
                //diary
                sendSubmitEmail : function(id){
                    var data = {};
                    var url = this.get('fitness_frontend_url');
                    var view = '';
                    var task = 'ajax_email';
                    var table = '';

                    data.id = id;
                    data.view = 'NutritionDiary';
                    data.method = 'DiarySubmitted';

                    var self = this;
                    this.ajaxCall(data, url, view, task, table, function(output) {
                        console.log(output);
                    });
                },
                
                add_diary : function(data, app) {
                    var url = this.get('fitness_frontend_url');
                    var view = 'nutrition_plan';
                    var task = 'importRecipe';

                    data.nutrition_plan_id = app.options.add_diary_options.nutrition_plan_id;
                    data.meal_id = app.options.add_diary_options.meal_id;
                    data.type = app.options.add_diary_options.type;
                    data.parent_view = app.options.add_diary_options.parent_view;

                    if(data.parent_view == 'nutrition_diary_frontend'){
                        data.db_table =  '#__fitness_nutrition_diary_ingredients';
                    }

                    if(data.parent_view == 'nutrition_plan_backend'){
                        data.db_table = '#__fitness_nutrition_plan_ingredients';
                    }

                    var table = data.db_table;

                    this.ajaxCall(data, url, view, task, table, function(output){
                        window.parent.resetBody();
                        window.parent.nutrition_meal.run();

                        var elem = window.parent.document.getElementById("recipes_list_wrapper");
                        elem.parentNode.removeChild(elem);
                        
                        
                        
                   });
                },
                
                loadVideoPlayer : function(video_path, app, height, width, container) {
                    var no_video_image_big = app.options.no_video_image_big;

                    var base_url = app.options.base_url;

                    var imageType = /no_video_image.*/; 
                    
                    var image = base_url + video_path.split('.')[0] + '.jpg';
                    if (video_path && !video_path.match(imageType) && video_path) {  

                        jwplayer(container).setup({
                            file: base_url + video_path,
                            image:  image,
                            height: height,
                            width: width
                       });
                    } else {
                        $("#" + container).css('background-image', 'url(' +  no_video_image_big + ')');
                    }
                },
            });

            return new Helper_model(options);
        }


        $.fitness_helper = function(options) {

            var constr = new FitnessHelper(options);

            return constr;
        };

}));