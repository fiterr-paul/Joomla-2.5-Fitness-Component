define([
	'jquery',
	'underscore',
	'backbone',
        'app',
        'models/goals/primary_goal',
        'models/goals/mini_goal',
	'text!templates/goals/backend/form_mini.html'

], function (
        $,
        _,
        Backbone,
        app,
        Primary_goal_model,
        Model,
        template
    ) {

    var view = Backbone.View.extend({
        
        initialize : function() {
            
            this.model = new Model();
            
            if(parseInt(this.id)) {
                this.model = this.collection.get(this.id);
                if(this.model) {
                    this.render();
                    return;
                }
                
                this.model = new Model({id : this.id});
                var self = this;
                this.model.fetch({
                    wait : true,
                    success: function (model, response) {
                        self.collection.add(model);
                        self.render();
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    }
                })
                return;
            }
            
            this.render();
        },

        
        template:_.template(template),
        
        render: function(){
            var data = {item : this.model.toJSON()};
            //console.log(data);
            data.$ = $;
            var template = _.template(this.template(data));
            this.$el.html(template);
            
            this.getPrimaryGoal();
            
            return this;
        },
        
        events : {
            "click #save" : "onClickSave",
            "click #save_close" : "onClickSaveClose",
            "click #cancel" : "onClickCancel",
        },
        
        getPrimaryGoal : function() {
            var primary_goal_id = this.options.primary_goal_id;
            
            var primary_goal_model  = app.collections.primary_goals.get(primary_goal_id);
            
            if(primary_goal_model) {
                this.loadCalendar(primary_goal_model);
                return;
            }
            
            if(!primary_goal_model) {
                primary_goal_model = new Primary_goal_model({id : primary_goal_id});
                var self = this;
                primary_goal_model.fetch({
                    wait : true,
                    success: function (model, response) {
                        self.loadCalendar(model);
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    } 
                })
            }

        },
        
        loadCalendar : function(model) {
            var start_date  = model.get('start_date');
            var deadline = model.get('deadline');

            var min_date = new Date(Date.parse(start_date));
            var max_date = new Date(Date.parse(deadline));
            $(this.el).find("#start_date, #deadline").datepicker({ dateFormat: "yy-mm-dd", minDate: min_date, maxDate: max_date });
        },
       
        onClickSave : function() {
            this.saveItem();
        },

        onClickSaveClose : function() {
            this.save_method = 'save_close';
            this.saveItem();
        },


        onClickCancel : function() {
            app.controller.navigate("!/list_view", true);
        },
        
        
        saveItem : function() {
            //validation
            var start_date_field = $(this.el).find('#start_date');
            var deadline_field = $(this.el).find('#deadline');
            var details_field = $(this.el).find('#details');
            var error_start_date_field = $(this.el).find('#error_start_date');
            var error_deadline_field = $(this.el).find('#error_deadline');
            
            start_date_field.removeClass("red_style_border");
            deadline_field.removeClass("red_style_border");
            error_start_date_field.html('');
            error_deadline_field.html('');
            
            var start_date = start_date_field.val();
            var deadline= deadline_field.val();
            
            var message = '';
            
            this.model.set({
                    start_date : start_date, 
                    deadline : deadline, 
                    details : details_field.val(),
                    primary_goal_id : this.options.primary_goal_id
                    
            });
            
            var overlap_start_date = this.onCheckOverlapDate('start_date');
            var overlap_deadline = this.onCheckOverlapDate('deadline');
     
            if(start_date && overlap_start_date.status) {
                start_date_field.addClass("red_style_border");
                message = 'The date overlaps with Mini Goal beginning ' + overlap_start_date.model.get('start_date') + ' and ending ' + overlap_start_date.model.get('deadline');
                error_start_date_field.html(message);
                return false;
            }
            
            if(deadline && overlap_deadline.status) {
                deadline_field.addClass("red_style_border");
                message = 'The date overlaps with Mini Goal beginning ' + overlap_start_date.model.get('start_date') + ' and ending ' + overlap_start_date.model.get('deadline');
                error_deadline_field.html(message);
                return false;
            }
            
            if(start_date && deadline && (start_date >= deadline)) {
                start_date_field.addClass("red_style_border");
                deadline_field.addClass("red_style_border");
                message = '"Start Date" should be less than "Achieve By"! ';
                error_start_date_field.html(message);
                return false;
            }

            if (!this.model.isValid()) {
                var validate_error = this.model.validationError;

                if(validate_error == 'start_date') {
                    start_date_field.addClass("red_style_border");
                    return false;
                } else if(validate_error == 'deadline') {
                    deadline_field.addClass("red_style_border");
                    return false;
                } else {
                    alert(this.model.validationError);
                    return false;
                }

            }
            var self = this;

            if (this.model.isNew()) {
                this.collection.create(this.model, {
                    wait: true,
                    success: function (model, response) {
                             if(self.save_method == 'save_close') {
                            app.controller.navigate("!/list_view", true);
                        }
                    },
                    error: function (model, response) {
                        alert(response.responseText);
                    }
                })
            } else {
                this.model.save(null, {
                    success: function (model, response) {
                        if(self.save_method == 'save_close') {
                            app.controller.navigate("!/list_view", true);
                        }
                    },
                    error: function (model, response) {
                        alert(response.responseText);
                    }
                });
            }
        },
        
        onCheckOverlapDate : function(type) {
            var result = {},
                current = this.model.get(type),
                id = this.model.get('id'),
                i;
        
            for(i = 0; i < this.collection.models.length; i++) {
                var model = this.collection.models[i];
                result.model = model;
                if(id != model.get('id')) {
                    var start_date = model.get('start_date');
                    var deadline = model.get('deadline');

                    if(current >= start_date && current <= deadline) {
                        result.status = true;
                        return result;
                    }
                }
            }

            result.status = false;
            return result;
        }

    });
            
    return view;
});