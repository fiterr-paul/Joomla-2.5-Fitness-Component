define([
	'jquery',
	'underscore',
	'backbone',
        'app',
        'collections/diary/nutrition_database_ingredients',
        'views/diary/frontend/ingredients_search_results',
	'text!templates/diary/frontend/meal_ingredient_item.html'
], function (
        $,
        _,
        Backbone,
        app,
        Nutrition_database_ingredients_collection,
        Ingredients_search_results_view,
        template 
    ) {

    var view = Backbone.View.extend({
            tagName : "tr",
        
            initialize: function(){
                this.ingredients_collection = new Nutrition_database_ingredients_collection();
                this.search_results_view = new Ingredients_search_results_view();

                this.edit_mode();
            },
            
            template:_.template(template),
            
            render: function(){
                var data = {item : this.model.toJSON()};
                //console.log(this.model.toJSON());
                var template = _.template(this.template(data));
                this.$el.html(template);

                return this;
            },
            
            events : {
                "click .delete_meal_ingredient" : "onClickDelete",
                "input .ingredient_name_input" : "onInputName",
                "change .ingredients_results " : "onChooseIngredient",
                "focusout .ingredient_quantity_input " : "onChangeQuantity"
                
            },
            
            edit_mode : function() {
                var edit_mode = false;
                
                if(this.model.get('edit_mode')) {
                    return true;
                }

                this.model.set({edit_mode : edit_mode});
            },
            
            onClickDelete : function() {
                var self = this;
                this.model.destroy({
                    success: function (model, response) {
                        self.collection.remove(model);
                        self.close();
                    },
                    error: function (model, response) {
                        alert(response.responseText);
                    }
                });
            },
            
            onInputName : function(event) {
                var typingTimer;
                var search_text = $(event.target).val();
                
                this.search_results_view.close();
                
                $(event.target).parent().append(
                    this.search_results_view.render().el
                );
                
                clearTimeout(typingTimer);
                var self = this;
                if (search_text) {
                    typingTimer = setTimeout(
                        function() {
                            self.ingredients_collection.fetch({
                                data : {search_text : search_text},
                                success: function (collection, response) {
                                    //console.log(collection);
                                    self.populateSearchContainer(collection);
                                },
                                error: function (collection, response) {
                                    alert(response.responseText);
                                }
                            }); 
                        },
                        self.options.doneTypingInterval
                    );
                }
            },
            
            populateSearchContainer : function(collection) {
                $(this.el).find(".results_count").html('Search returned ' + collection.length + ' ingredients.');
                
                var select_field =  $(this.el).find(".ingredients_results");
                
                var self = this;
                _.each(collection.models, function(model) {
                    select_field.append(
                        '<option value="' + model.get('id') + '" >' + model.get('ingredient_name') + '</option>'
                    );
                });
                
                select_field.find(":odd").css("background-color", "#F0F0EE")
                
            },
            
            onChooseIngredient : function(event) {
                var id = $(event.target).val();
                this.ingredient_model = this.ingredients_collection.get(id);
                $(this.el).find(".ingredient_name_input").val(this.ingredient_model.get('ingredient_name'));
                this.search_results_view.close();
                
                var measurement = this.getMeasurement(this.ingredient_model.get('specific_gravity'));
                $(this.el).find(".grams_mil").html(measurement);
                
                $(this.el).find(".ingredient_quantity_input").focus();
            },
            
            getMeasurement : function(specific_gravity) {
                if(parseFloat(specific_gravity) > 0) {
                    return 'millilitres';
                } 
                return 'grams';
            },
            
            onChangeQuantity : function(event) {
                var quantity = $(event.target).val();
                var inredient_id = $(event.target).attr('data-id');
                
                if(typeof this.ingredient_model !== "undefined") {
                    this.onSetQuantity(this.ingredient_model, quantity);
                    return;
                }
                
                if(this.ingredients_collection.get(inredient_id)) {
                    this.onSetQuantity(this.ingredients_collection.get(inredient_id), quantity);
                    return;
                }

                var self = this;
                this.ingredients_collection.fetch({
                    success: function (collection, response) {
                        var model = collection.get(inredient_id);
                        self.onSetQuantity(model, quantity);
                    },
                    error: function (collection, response) {
                        alert(response.responseText);
                    }
                }); 
            },
            
            onSetQuantity : function(model, quantity) {
                var ingredient = model.toJSON();
                var calculatedIngredient = this.calculatedIngredientItems(ingredient, quantity);
                this.model.set(calculatedIngredient);
                console.log(this.model.toJSON());
                
                if (!this.model.isValid()) {
                    var validate_error = this.model.validationError;
                    alert(this.model.validationError);
                    return false;
                }
                
                var self = this;
                this.model.save(null, {
                    success: function (model, response) {
                        self.collection.add(model);
                        self.render();
                    },
                    error: function (model, response) {
                        alert(response.responseText);
                    }
                });
            },
            
            calculatedIngredientItems : function(ingredient, quantity) {
                var calculated_ingredient = {};
                var specific_gravity = ingredient.specific_gravity;
                //quantity = 100;
                //specific_gravity = 1.03;
                //ingredient.protein = 3.2;
                calculated_ingredient.ingredient_id = ingredient.id;

                calculated_ingredient.meal_name = ingredient.ingredient_name;

                calculated_ingredient.quantity = quantity;

                calculated_ingredient.measurement = this.getMeasurement(ingredient.specific_gravity);

                calculated_ingredient.protein = this.calculateDependsOnGravity(ingredient.protein, quantity, specific_gravity);

                calculated_ingredient.fats = this.calculateDependsOnGravity(ingredient.fats, quantity, specific_gravity);

                calculated_ingredient.carbs = this.calculateDependsOnGravity(ingredient.carbs, quantity, specific_gravity);

                calculated_ingredient.calories = this.calculateDependsOnGravity(ingredient.calories, quantity, specific_gravity);

                calculated_ingredient.energy = this.calculateDependsOnGravity(ingredient.energy, quantity, specific_gravity);

                calculated_ingredient.saturated_fat = this.calculateDependsOnGravity(ingredient.saturated_fat, quantity, specific_gravity);

                calculated_ingredient.total_sugars = this.calculateDependsOnGravity(ingredient.total_sugars, quantity, specific_gravity);

                calculated_ingredient.sodium = this.calculateDependsOnGravity(ingredient.sodium, quantity, specific_gravity);

                //console.log(ingredient.specific_gravity);
                //console.log(ingredient);
                //console.log(calculated_ingredient);

                return calculated_ingredient;
            },
            
            calculateDependsOnGravity : function(value, quantity, specific_gravity) {
                var calculated_value;
                if(parseFloat(specific_gravity) > 0) {
                    calculated_value = this.millilitresFormula(value, quantity, specific_gravity);
                } else {
                    calculated_value = this.gramsFormula(value, quantity);
                }
                return calculated_value;
            },

            gramsFormula : function(value, quantity) {
                return this.round_2_sign (value / 100 * quantity );
            },

            millilitresFormula : function(value, quantity, specific_gravity) {
                return this.round_2_sign (value / 100 * quantity * specific_gravity );
            },

            round_2_sign : function(value) {
                return Math.round(value * 100)/100;
            },
            
            

            close :function() {
                $(this.el).unbind();
                $(this.el).remove();
            },
        });
            
    return view;
});