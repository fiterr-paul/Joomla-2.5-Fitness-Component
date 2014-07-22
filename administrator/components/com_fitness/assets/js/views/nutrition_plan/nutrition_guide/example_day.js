define([
	'jquery',
	'underscore',
	'backbone',
        'app',
        'collections/recipe_database/recipes',
        'collections/nutrition_plan/nutrition_guide/nutrition_guide_recipes',
        'models/nutrition_plan/nutrition_guide/get_recipe_params',
        'views/nutrition_plan/nutrition_guide/example_day_recipe',
        'views/nutrition_plan/nutrition_guide/add_recipe',
	'text!templates/nutrition_plan/nutrition_guide/example_day.html',
        'jquery.timepicker'
], function ( 
        $,
        _,
        Backbone,
        app,
        Recipes_collection,
        Example_day_recipes_collection,
        Get_recipe_params_model,
        Example_day_recipe_view,
        Example_day_add_recipe_view,
        template
    ) {

    var view = Backbone.View.extend({
        
        initialize : function() {
            app.collections.example_day_recipes = new Example_day_recipes_collection();
            
            app.collections.example_day_recipes.bind("add", this.addItem, this);
            app.collections.example_day_recipes.bind("reset", this.getExampleDayRecipes, this);
            
            this.getExampleDayRecipes();
            
            this.recipe_params_model =  new Get_recipe_params_model();
                
            app.collections.recipes = app.collections.recipes || new Recipes_collection(); 

            this.recipe_params_model.bind("change", this.get_database_recipes, this);
        },
        
        template : _.template(template),

        render: function(){
            $(this.el).html(this.template({ }));
            return this;
        },
        
        events: {
            "click .add_recipe" : "onClickAddRecipe",
            "click .cancel_add_recipe": "onCancelViewRecipe",
        },

        getExampleDayRecipes : function() {
            $("#recipes_list_container").empty();
            var self = this;
            app.collections.example_day_recipes.fetch({
                data: {
                    nutrition_plan_id : this.options.nutrition_plan_id,
                    example_day_id : this.options.example_day_id,
                    sort_by : 'time'
                },
                success : function(collection, response) {
                    //console.log(collection.toJSON());
                },
                error: function (collection, response) {
                    alert(response.responseText);
                }
            });
        },
        
        loadItems : function(collection) {
            var self = this;
            _.each(collection.models, function(model) {
                self.addItem(model);
            });
        },
        
        addItem : function(model) {
            $(this.el).find("#recipes_list_container").append(new Example_day_recipe_view({
                nutrition_plan_id : this.options.nutrition_plan_id,
                example_day_id : this.options.example_day_id,
                menu_id : this.options.menu_id,
                model : model,
                collection : app.collections.example_day_recipes
            }).render().el);
        },
        
        onClickAddRecipe : function(event) {
            var container = $(event.target);
            if(!parseInt(app.collections.recipes.length)) {
                this.get_database_recipes();
            }
            container.parent().html(new Example_day_add_recipe_view({
                example_day_id : this.options.example_day_id,
                menu_id : this.options.menu_id,
                nutrition_plan_id : this.options.nutrition_plan_id,
                collection : app.collections.recipes,
                recipe_params_model : this.recipe_params_model
            }).render().el);
        },
        
        onCancelViewRecipe :function (event) {
            var container = $(event.target);
            container.closest( ".add_recipe_container" ).html('<div class="add_recipe" style="color:#4f4f4f;padding:5px;width:100%;height:100%;">click to add new recipe</div>');
        },
        
        get_database_recipes : function() {
            app.collections.recipes.reset();
            var self = this;
            app.collections.recipes.fetch({
                data : self.recipe_params_model.toJSON(),
                success : function(collection, response) {
                    //console.log(collection.toJSON());
                },
                error: function (model, response) {
                    alert(response.responseText);
                }
            });  
        },

            
    });
            
    return view;
});