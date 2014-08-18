define([
	'jquery',
	'underscore',
	'backbone',
        'app',
	'text!templates/diary/frontend/totals.html'
], function (
        $,
        _,
        Backbone,
        app,
        template 
    ) {

    var view = Backbone.View.extend({
        
        initialize: function(){
            app.collections.meal_ingredients.bind("sync", this.render, this);
            app.collections.meal_ingredients.bind("add", this.render, this);
            app.collections.meal_ingredients.bind("remove", this.render, this);

            this.model.set({
                daily_protein_grams : '0',
                daily_fats_grams : '0',
                daily_carbs_grams : '0',
                daily_saturated_fat_grams : '0',
                daily_total_sugars_grams : '0',
                daily_sodium_grams : '0',

            });

        },
        
        template : _.template(template),

        render : function () {
            this.setBaseValues();
            var data = {item : this.model.toJSON()};
            $(this.el).html(this.template(data));
            
            this.setVarianceGrams();
            this.setVariancePercents();
            this.setDailyTargetPercents();
            this.setDailyTotalsPercents();
            this.setDailyTargetCalories();       
            this.setDailyTargetEnergy();
            this.setDailyTotalsCalories();
            this.setDailyTotalsEnergy();
            this.setVarianceCalories();
            this.setVarianceEnergy();
            this.setCaloriesVariancePercents();
            this.setEnergyVariancePercents();
            this.setDailyTargetWater();
            this.setDailyTotalWater();
            this.setDailyVarianceWater();
            return this;
        },
        
        setBaseValues : function() {
            this.setDailyTotalsGrams();
            this.setDailyTargetGrams();
            
        },
        
        setDailyTotalsGrams : function() {
            this.daily_protein_grams = this.getCollectionNameAmount('protein');
            this.daily_fats_grams = this.getCollectionNameAmount('fats');
            this.daily_carbs_grams = this.getCollectionNameAmount('carbs');
            this.daily_saturated_fat_grams = this.getCollectionNameAmount('saturated_fat');
            this.daily_total_sugars_grams = this.getCollectionNameAmount('total_sugars');
            this.daily_sodium_grams = this.getCollectionNameAmount('sodium');

            this.model.set({
                daily_protein_grams : this.daily_protein_grams,
                daily_fats_grams : this.daily_fats_grams,
                daily_carbs_grams : this.daily_carbs_grams,
                daily_saturated_fat_grams : this.daily_saturated_fat_grams,
                daily_total_sugars_grams : this.daily_total_sugars_grams,
                daily_sodium_grams : this.daily_sodium_grams,
            });
        },

        getCollectionNameAmount : function( name) {
            var value =  app.collections.meal_ingredients.reduce(function(memo, value) { return parseFloat(memo) + parseFloat(value.get(name)) }, 0);
            return value.toFixed(2);
        },
        
        setDailyTargetGrams : function() {
            var nutrition_plan_id = this.model.get('nutrition_plan_id');
            //console.log(this.model.toJSON());
            
            //console.log(app.models.target.toJSON());
            
            this.daily_target_protein_grams = app.models.target.get('protein');
            this.daily_target_fats_grams = app.models.target.get('fats');
            this.daily_target_carbs_grams = app.models.target.get('carbs');
            
            this.model.set({
                daily_target_protein_grams : this.daily_target_protein_grams,
                daily_target_fats_grams : this.daily_target_fats_grams,
                daily_target_carbs_grams : this.daily_target_carbs_grams
            });
        },
        
        setVarianceGrams : function() {
            //protein
            var variance_protein_grams_element = $(this.el).find("#variance_protein_grams");
            
            this.variance_protein_grams_value = this.calculateVarianceGrams(this.model.get('daily_protein_grams'), this.model.get('daily_target_protein_grams'));
            
            variance_protein_grams_element.html(this.variance_protein_grams_value);
            
            this.variance_protein_percents_value = this.calculateVariancePercents(this.variance_protein_grams_value, this.model.get('daily_target_protein_grams'));
            
            this.setVarianceRangeStylePRO_FATS_CARBS(variance_protein_grams_element, this.variance_protein_percents_value);
            
            //fats
            var variance_fats_grams_element = $(this.el).find("#variance_fats_grams");
            
            this.variance_fats_grams_value = this.calculateVarianceGrams(this.model.get('daily_fats_grams'), this.model.get('daily_target_fats_grams'));
            
            variance_fats_grams_element.html(this.variance_fats_grams_value);
            
            this.variance_fats_percents_value = this.calculateVariancePercents(this.variance_fats_grams_value, this.model.get('daily_target_fats_grams'));
            
            this.setVarianceRangeStylePRO_FATS_CARBS(variance_fats_grams_element, this.variance_fats_percents_value);
            
            //carbs
            var variance_carbs_grams_element = $(this.el).find("#variance_carbs_grams");
            
            this.variance_carbs_grams_value = this.calculateVarianceGrams(this.model.get('daily_carbs_grams'), this.model.get('daily_target_carbs_grams'));
            
            variance_carbs_grams_element.html(this.variance_carbs_grams_value);
            
            this.variance_carbs_percents_value = this.calculateVariancePercents(this.variance_carbs_grams_value, this.model.get('daily_target_carbs_grams'));
            
            this.setVarianceRangeStylePRO_FATS_CARBS(variance_carbs_grams_element, this.variance_carbs_percents_value);
        },
        
        setVarianceRangeStylePRO_FATS_CARBS : function(element, value) {
            var abs_value = Math.abs(value); 
            var input_class = '';
            element.removeClass('green_style orange_style red_style');
            if((abs_value >= 0) && (abs_value <= 15)) {
                input_class = 'green_style'; 
            }

            if((abs_value > 15) && (abs_value <= 40)) {
                input_class = 'orange_style'; 
            }

            if(abs_value > 40) {
                input_class = 'red_style'; 
            }
            element.addClass(input_class);
        },
        
        round_2_sign : function(value) {
            return Math.round(value * 100)/100;
        },
        
        calculateVarianceGrams : function(total, target) {
            return this.round_2_sign(total - target);
        },
    
        calculateVariancePercents : function(variance, target) {
            return this.round_2_sign((variance / target) * 100);
        },
        
        setVariancePercents : function() {
            $(this.el).find("#variance_protein_percents").html(this.variance_protein_percents_value);
            this.setVarianceRangeStylePRO_FATS_CARBS($(this.el).find("#variance_protein_percents"), this.variance_protein_percents_value);

            $(this.el).find("#variance_fats_percents").html(this.variance_fats_percents_value);
            this.setVarianceRangeStylePRO_FATS_CARBS($(this.el).find("#variance_fats_percents"), this.variance_fats_percents_value);

            $(this.el).find("#variance_carbs_percents").html(this.variance_carbs_percents_value);
            this.setVarianceRangeStylePRO_FATS_CARBS($(this.el).find("#variance_carbs_percents"), this.variance_carbs_percents_value);
        },
        
        setDailyTargetPercents : function() {
            this.daily_target_protein_percents = app.models.target.get('step4_protein_percent');
            this.daily_target_carbs_percents = app.models.target.get('step4_carbs_percent');
            this.daily_target_fats_percents = app.models.target.get('step4_fat_percent');
            
            $(this.el).find("#daily_target_protein_percents").html(this.daily_target_protein_percents);
            $(this.el).find("#daily_target_carbs_percents").html(this.daily_target_carbs_percents);
            $(this.el).find("#daily_target_fats_percents").html(this.daily_target_fats_percents);
        },
        
        setDailyTotalsPercents : function() {
            this.daily_total_protein_percents = this.round_2_sign(this.daily_target_protein_percents / this.daily_target_protein_grams * this.daily_protein_grams);
            this.daily_total_carbs_percents = this.round_2_sign(this.daily_target_carbs_percents / this.daily_target_carbs_grams * this.daily_carbs_grams);
            this.daily_total_fats_percents = this.round_2_sign(this.daily_target_fats_percents / this.daily_target_fats_grams * this.daily_fats_grams);
            
            $(this.el).find("#daily_protein_percents").html(this.daily_total_protein_percents);
            $(this.el).find("#daily_fats_percents").html(this.daily_total_carbs_percents);
            $(this.el).find("#daily_carbs_percents").html(this.daily_total_fats_percents);
        },
        
        setDailyTargetCalories : function() {
            this.daily_target_calories = app.models.target.get('step4_calories');
            $(this.el).find("#daily_target_calories").html(this.daily_target_calories);
        },
        
        setDailyTargetEnergy : function() {
            this.daily_target_energy = this.round_2_sign(this.daily_target_calories * 4.184);
            $(this.el).find("#daily_target_energy").html(this.daily_target_energy);
        },
        
        setDailyTotalsCalories : function() {
            this.daily_totals_calories = this.getCollectionNameAmount('calories');
            $(this.el).find("#daily_calories").html(this.daily_totals_calories);
            
        },
        
        setDailyTotalsEnergy : function() {
            this.daily_totals_energy = this.round_2_sign(this.daily_totals_calories * 4.184);
            $(this.el).find("#daily_energy").html(this.daily_totals_energy);
        },
        
        setVarianceCalories : function() {
            var variance_calories_element = $(this.el).find("#variance_calories");
            
            this.variance_calories = this.daily_totals_calories - this.daily_target_calories;
            
            variance_calories_element.html(this.variance_calories);
            
            this.setVarianceRangeStyleCalories(variance_calories_element, this.getVarianceCaloriesPercents());
            
        },
        
        getVarianceCaloriesPercents : function() {
            return this.round_2_sign((this.variance_calories / this.daily_target_calories)*100);
        },
        
        getVarianceEnergyPercents : function() {
            return this.getVarianceCaloriesPercents();
        },
        
        setVarianceRangeStyleCalories : function(element, value) {
            var abs_value = Math.abs(value); 
            var input_class = '';
            element.removeClass('green_style orange_style red_style');
            if((abs_value >= 0) && (abs_value <= 30)) {
                input_class = 'green_style'; 
            }

            if((abs_value > 30) && (abs_value <= 50)) {
                input_class = 'orange_style'; 
            }

            if(abs_value > 50) {
                input_class = 'red_style'; 
            }
            element.addClass(input_class);
        },
        
        setVarianceEnergy : function() {
            var variance_energy_element = $(this.el).find("#variance_energy");
            
            this.variance_energy = this.round_2_sign(this.daily_totals_energy - this.daily_target_energy);
            
            variance_energy_element.html(this.variance_energy);
            
            this.setVarianceRangeStyleCalories(variance_energy_element, this.getVarianceCaloriesPercents());
        },
        
        setCaloriesVariancePercents : function() {
            $(this.el).find("#variance_calories_percents").html(this.getVarianceCaloriesPercents());
        },
        
        setEnergyVariancePercents : function() {
            $(this.el).find("#variance_energy_percents").html(this.getVarianceEnergyPercents());
        },
        
        setDailyTargetWater : function() {
            this.daily_target_water = app.models.target.get('water');
            $(this.el).find("#daily_target_water").html(this.daily_target_water);
        },
        
        getWaterAmount : function( name) {
            var value =  app.collections.meal_entries.reduce(function(memo, value) { return parseFloat(memo) + parseFloat(value.get(name)) }, 0);
            return value.toFixed(2);
        },
        
        setDailyTotalWater : function() {
            this.daily_total_water = parseFloat(this.getWaterAmount('water')) + parseFloat(this.getWaterAmount('previous_water'));
            $(this.el).find("#daily_total_water").html(this.daily_total_water);
        },
        
        setDailyVarianceWater : function() {
            var variance_daily_total_water_element = $(this.el).find("#variance_daily_total_water");
            
            this.variance_water = this.round_2_sign(this.daily_total_water - this.daily_target_water);
            
            variance_daily_total_water_element.html(this.variance_water);
            
            this.setVarianceRangeWater(variance_daily_total_water_element, this.variance_water );
        },
        
        setVarianceRangeWater : function(element, value) {
            var abs_value = Math.abs(value); 
            var input_class = '';
            element.removeClass('green_style orange_style red_style');
            if((abs_value >= 0) && (abs_value <= 250)) {
                input_class = 'green_style'; 
            }

            if((abs_value > 250) && (abs_value <= 350)) {
                input_class = 'orange_style'; 
            }

            if(abs_value > 350) {
                input_class = 'red_style'; 
            }
            element.addClass(input_class);
        }
        


    });
            
    return view;

});