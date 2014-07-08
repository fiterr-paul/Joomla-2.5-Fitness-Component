define([
	'jquery',
	'underscore',
	'backbone',
        'app',
	'text!templates/nutrition_plan/main_menu.html'
], function ( $, _, Backbone, app, template ) {

    var view = Backbone.View.extend({
        
        template:_.template(template),
            
        el: $("#plan_menu"), 

        initialize: function(){
            this.render();
        },

        render: function(){
            var template = _.template(this.template());
            this.$el.html(template);
            return this;
        },

        events: {
            "click #overview_link" : "onClickOverview",
            "click #targets_link" : "onClickTargets",
            "click #macronutrients_link" : "onClickMacronutrients",
            "click #supplements_link" : "onClickSupplements",
            "click #nutrition_guide_link" : "onClickNutrition_guide",
            "click #information_link" : "onClickInformation",
            "click #archive_focus_link" : "onClickArchive_focus",
            "click #close_tab" : "onClickClose",
        },

        onClickOverview : function() {
            app.controller.navigate("!/overview", true);
        },

        onClickTargets : function() {
            app.controller.navigate("!/targets", true);
        },

        onClickMacronutrients : function() {
            app.controller.navigate("!/macronutrients", true);
        },

        onClickSupplements : function() {
            app.controller.navigate("!/supplements", true);
        },

        onClickNutrition_guide : function() {
            var id = app.models.nutrition_plan.get('id');
            app.controller.navigate("!/nutrition_guide/" + id, true);
        },

        onClickInformation : function() {
            app.controller.navigate("!/information", true);
        },

        onClickArchive_focus : function() {
            app.controller.navigate("!/archive", true);
        },

        onClickClose : function() {
            app.controller.navigate("!/close", true);
        }

    });
            
    return view;
});