define([
	'jquery',
	'underscore',
	'backbone',
        'app',
	'text!templates/nutrition_plan/nutrition_guide/recipe_variations_filter.html',
], function ( $, _, Backbone, app, template ) {

    var view = Backbone.View.extend({
        
        template:_.template(template),
        
        render : function(){
            $(this.el).html(this.template());
            this.populateSelect();
            return this;
        },

        populateSelect : function() {
            var self = this;
            this.collection.on("add", function(model) {
                self.$el.find("#recipe_variations_filter").append('<option value="' + model.get('id') + '">' + model.get('name') + '</option>');
            });

            _.each(this.collection.models, function (model) { 
                self.$el.find("#recipe_variations_filter").append('<option value="' + model.get('id') + '">' + model.get('name') + '</option>');
            }, this);

        },

        events: {
            "change #recipe_variations_filter" : "onFilterSelect",
        },


        onFilterSelect : function(event){
            var ids = $(event.target).find(':selected').map(function(){ return this.value }).get().join(",");
            app.models.pagination.reset();
            app.collections.add_meal_recipes.reset();
            app.models.get_recipe_params.set({'recipe_variations_filter_options' : ids});
        }
    });
            
    return view;
});