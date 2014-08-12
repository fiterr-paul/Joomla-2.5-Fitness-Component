define([
	'jquery',
	'underscore',
	'backbone',
        'app',
        'collections/nutrition_plan/nutrition_guide/menu_descriptions',
        'models/diary/meal_ingredient',
        'views/programs/select_element',
        'views/diary/frontend/meal_ingredient_item',
	'text!templates/diary/frontend/diary_meal_item.html'
], function (
        $,
        _,
        Backbone,
        app,
        Menu_descriptions_collection,
        Meal_ingredient_model,
        Select_element_view,
        Meal_ingredient_item_view,
        template 
    ) {

    var view = Backbone.View.extend({
            initialize: function(){
                this.collection.bind("sync", this.render, this);
                this.collection.bind("remove", this.render, this);

                this.model.set({
                    protein_totals : '0',
                    fats_totals : '0',
                    carbs_totals : '0',
                    calories_totals : '0',
                    energy_totals : '0',
                    saturated_fat_totals : '0',
                    total_sugars_totals : '0',
                    sodium_totals : '0',
                });
                
                this.edit_mode();
            },
            
            template:_.template(template),
            
            render: function(){
                this.calculateTotals();
                var data = {item : this.model.toJSON()};
                //console.log(this.model.toJSON());
                var template = _.template(this.template(data));
                this.$el.html(template);
                
                this.loadDescription();
                
                this.populateMealIngredients();
                
                return this;
            },
            
            events : {
                "click .save_diary_meal" : "onClickSave",
                "click .edit_diary_meal" : "onClickEdit",
                "click .cancel_diary_meal" : "onClickCancel",
                "click .delete_diary_meal" : "onClickDelete",
                
                "click .add_meal_ingredient" : "onClickAddMealIngredient",
                "click .save_as_recipe" : "onClickSaveAsRecipe",

            },
            
            onClickSave :function() {
                var description_field = this.$el.find('.diary_item_description');

                description_field.removeClass("red_style_border");

                var description = description_field.find(":selected").val();

                if(!description) {
                    description_field.addClass("red_style_border");
                    return false;
                }
                
                this.model.set({
                    description : description,
                });
                
                console.log(this.model.toJSON());
                
                if (!this.model.isValid()) {
                    var validate_error = this.model.validationError;

                    if(validate_error == 'description') {
                        description_field.addClass("red_style_border");
                        return false;
                    } else {
                        alert(this.model.validationError);
                        return false;
                    }
                }

                var self = this;
                this.model.save(null, {
                    success : function (model, response) {
                        self.model.set({edit_mode : false});
                        self.render();
                    },
                    error: function (model, response) {
                        alert(response.responseText);
                    }
                });
            },
            
            onClickEdit : function() {
                this.model.set({edit_mode : true});
                this.render();
            },

            onClickCancel : function(event) {
                if(this.model.isNew()) {
                    this.close();
                    return;
                }
                
                this.model.set({edit_mode : false});
                this.render();
            },
            
            onClickDelete : function() {
                var self = this;
                this.model.destroy({
                    success: function (model) {
                        self.close();
                    },
                    error: function (model, response) {
                        alert(response.responseText);
                    }
                });
            },
            
            edit_mode : function() {
                var edit_mode = false;
                
                if(this.model.get('edit_mode')) {
                    return true;
                }

                this.model.set({edit_mode : edit_mode});
            },
            
            loadDescription : function() {
                var collection = Menu_descriptions_collection;
                var description_id = this.model.get('description');

                if(description_id) {
                    var model = collection.get(description_id);

                    if(model) {
                        var name = model.get('name');
                        var image = model.get('image');

                        if(image) {
                            this.$el.find(".description_image").css('background-image', 'url(' + image + ')');
                        }
                        this.$el.find(".description_select").html(name);
                    }
                }

                if(this.model.get('edit_mode')) {
                    new Select_element_view({
                        model : this.model,
                        el : this.$el.find(".description_select"),
                        collection : collection,
                        first_option_title : '-Select-',
                        class_name : 'diary_item_description dark_input_style',
                        id_name : 'description',
                        model_field : 'description',
                        element_disabled :  ""
                    }).render();
                }
            },

            onClickEdit : function() {
                this.model.set({edit_mode : true});
                this.render();
            },
            
            populateMealIngredients : function() {
                var self = this;
                _.each(this.collection.models, function(model) {
                    self.addMealIngredientItem(model);
                });
            },     
            
            addMealIngredientItem : function(model) {
                model.set({edit_mode : this.edit_mode()});
                $(this.el).find(".meal_ingredients_wrapper").append(new Meal_ingredient_item_view({model : model, collection : this.collection}).render().el);
            },
            
            onClickAddMealIngredient : function() {
                var model = new Meal_ingredient_model({
                    nutrition_plan_id : this.model.get('nutrition_plan_id'),
                    diary_id  : this.model.get('diary_id'),
                    meal_entry_id  : this.model.get('meal_entry_id'),
                    meal_id : this.model.get('id')
                });
                this.addMealIngredientItem(model);                
            },
            
            calculateTotals : function() {
                //console.log(this.collection.toJSON());
                var protein_totals = this.getCollectionNameAmount('protein');
                var fats_totals = this.getCollectionNameAmount('fats');
                var carbs_totals = this.getCollectionNameAmount('carbs');
                var calories_totals = this.getCollectionNameAmount('calories');
                var energy_totals = this.getCollectionNameAmount('energy');
                var saturated_fat_totals = this.getCollectionNameAmount('saturated_fat');
                var total_sugars_totals = this.getCollectionNameAmount('total_sugars');
                var sodium_totals = this.getCollectionNameAmount('sodium');

                this.model.set({
                    protein_totals : protein_totals,
                    fats_totals : fats_totals,
                    carbs_totals : carbs_totals,
                    calories_totals : calories_totals,
                    energy_totals : energy_totals,
                    saturated_fat_totals : saturated_fat_totals,
                    total_sugars_totals : total_sugars_totals,
                    sodium_totals : sodium_totals,
                });
            },
            
            getCollectionNameAmount : function( name) {
                var value =  this.collection.reduce(function(memo, value) { return parseFloat(memo) + parseFloat(value.get(name)) }, 0);
                return value.toFixed(2);
            },
            
            onClickSaveAsRecipe : function() {
                console.log('save as recipe');
            },

            close :function() {
                $(this.el).unbind();
                $(this.el).remove();
            },
  
        });
            
    return view;
});