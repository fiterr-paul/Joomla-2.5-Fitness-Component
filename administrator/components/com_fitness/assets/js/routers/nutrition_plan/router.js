define([
	'jquery',
	'underscore',
	'backbone',
        'app',
        'collections/nutrition_plan/nutrition_plans',
        'collections/nutrition_plan/targets',
	'models/nutrition_plan/nutrition_plan',
        'models/nutrition_plan/target',
        'views/nutrition_plan/overview',
        'views/nutrition_plan/target_block',
        'views/nutrition_plan/macronutrients',
        'views/nutrition_plan/information',
        'views/nutrition_plan/archive_list'
], function (
        $,
        _,
        Backbone,
        app, 
        Nutrition_plans_collection,
        Targets_collection,
        Nutrition_plan_model,
        Target_model,
        Overview_view,
        Target_block_view,
        Macronutrients_view,
        Information_view,
        Archive_list_view
    ) {

    var Controller = Backbone.Router.extend({
        
            initialize: function(){

                app.models.nutrition_plan = new Nutrition_plan_model({'id' : app.options.item_id});
                
                app.collections.nutrition_plans = new Nutrition_plans_collection();
                
                app.collections.targets = new Targets_collection({'id' : app.options.item_id});

            },
        
            routes: {
                "": "overview", 
                "!/": "overview", 
                "!/overview": "overview", 
                "!/targets": "targets", 
                "!/macronutrients": "macronutrients", 
                "!/information": "information", 
                "!/archive": "archive", 
                "!/close": "close", 
            },

            overview: function () {
                 this.no_active_plan_action();
                 this.common_actions();
                 $("#overview_wrapper").show();
                 $("#overview_link").addClass("active_link");
                 // connect Graph from Goals frontend logic
                 $.goals_frontend(app.options);
                 var id = app.models.nutrition_plan.get('id');
                 app.models.nutrition_plan.fetch({
                    data: {id : id},
                    wait : true,
                    success : function(model, response) {
                        var overview_view = new Overview_view({model : model});
                        $("#nutrition_focus_wrapper").html(overview_view.render().el);
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    }
                 });
                 
            },

            targets: function () {
                
                 this.no_active_plan_action();
                 this.common_actions();
                 $("#targets_wrapper").show();
                 $("#targets_link").addClass("active_link");
                 var id = app.models.nutrition_plan.get('id');
                 
                 app.collections.targets.fetch({
                    data: {id : id, client_id : app.options.client_id},
                    wait : true,
                    success : function(collection, response) {
                        $("#targets_container").empty();
                        _.each(collection.models, function(model) {
                            var target_block_view = new Target_block_view({model : model});
                            $("#targets_container").append(target_block_view.render().el);
                        });
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    }
                 });

                 // connect comments
                 var comment_options = {
                    'item_id' :  id,
                    'fitness_administration_url' : app.options.fitness_frontend_url,
                    'comment_obj' : {'user_name' : app.options.user_name, 'created' : "", 'comment' : ""},
                    'db_table' :  '#__fitness_nutrition_plan_targets_comments',
                    'read_only' : true,
                    'anable_comment_email' : false
                }
                var comments =  $.comments(comment_options, comment_options.item_id, 0);

                var comments_html = comments.run();
                $("#targets_comments_wrapper").html(comments_html);

            },
            
            macronutrients: function () {
                 this.no_active_plan_action();
                 this.common_actions();
                 $("#macronutrients_wrapper").show();
                 $("#macronutrients_link").addClass("active_link");
               
                 var id = app.models.nutrition_plan.get('id');
                 app.models.nutrition_plan.fetch({
                    data: {id : id},
                    wait : true,
                    success : function(model, response) {
                        var macronutrients_view = new Macronutrients_view({model : model});
                        
                        $("#macronutrients_container").html(macronutrients_view.render().el);
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    }
                 });
                 // connect comments
                 var comment_options = {
                    'item_id' :  id,
                    'fitness_administration_url' : app.options.fitness_frontend_url,
                    'comment_obj' : {'user_name' : app.options.user_name, 'created' : "", 'comment' : ""},
                    'db_table' : '#__fitness_nutrition_plan_macronutrients_comments',
                    'read_only' : true,
                    'anable_comment_email' : false
                }
                var comments = $.comments(comment_options, comment_options.item_id, 1);

                var comments_html = comments.run();
                $("#macronutrients_comments_wrapper").html(comments_html);
            },
     
            information: function () {
                 this.no_active_plan_action();
                 this.common_actions();
                 $("#information_wrapper").show();
                 $("#information_link").addClass("active_link");
                 var id = app.models.nutrition_plan.get('id');
                 app.models.nutrition_plan.fetch({
                    data: {id : id},
                    wait : true,
                    success : function(model, response) {
                        var information_view = new Information_view({model : model});
                        
                        $("#information_wrapper").html(information_view.render().el);
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    }
                 });
            },
                    
            archive: function () {
                 this.common_actions();
                 $("#archive_wrapper").show();
                 $("#archive_focus_link").addClass("active_link");

                 app.collections.nutrition_plans.fetch({
                    data: {id : app.options.item_id, client_id : app.options.client_id},
                    wait : true,
                    success : function(collection, response) {
                        var archive_list_view = new Archive_list_view({model : app.models.nutrition_plan, collection : collection});
                        
                        $("#archive_wrapper").html(archive_list_view.render().el);
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    }
                 });
            },
                    
            close: function() {
                 this.no_active_plan_action();
                 $("#close_tab").hide();
                 app.models.nutrition_plan.set({id : app.options.item_id});
                 this.overview();
            },
            
            common_actions : function() {
                $(".block").hide();
                $(".plan_menu_link").removeClass("active_link")
            },
            
            no_active_plan_action : function() {
                if(!app.options.item_id) {
                    alert('Please contact your trainer immediately regarding your current Nutrition Plan!');
                    return false;
                }
           }

        });

    return Controller;
});