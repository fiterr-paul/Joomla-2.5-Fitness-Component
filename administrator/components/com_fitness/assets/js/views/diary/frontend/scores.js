define([
	'jquery',
	'underscore',
	'backbone',
        'app',
        'views/graph/gredient_graph',
	'text!templates/diary/frontend/scores.html'
], function (
        $,
        _,
        Backbone,
        app,
        Gredient_graph_view,
        template 
    ) {

    var view = Backbone.View.extend({
        
        initialize: function(){
            
        },
        
        template : _.template(template),

        render : function () {
            var data = {item : this.model.toJSON()};
            data.$ = $;
            $(this.el).html(this.template(data));
            
            this.connectMacronutrientScores();
            
            this.setWaterScore();
            
            this.setCalorieScore();
            
            this.setFinalScore();
            
            this.onRender();
            
            return this;
        },
        
        onRender : function() {
            var self = this;
            $(this.el).show('0', function() {
                self.connectComments();
            });
        },
        
        calculateGraphScore : function(vpp) {
            var result = 0;

            if(vpp < 200) {
                if(vpp > 0) {
                    result = 100 + Math.abs(vpp) - ((1.5) * Math.abs(vpp));
                } else {
                    result = 100 + vpp;
                }
            }

            return this.round_2_sign(result);
        },
        
        round_2_sign : function(value) {
            return Math.round(value * 100)/100;
        },
        
        connectMacronutrientScores : function() {
            var vpp = parseFloat(this.options.totals_view.variance_protein_percents_value);
            var protein_graph_score = this.calculateGraphScore(vpp);
            
            var vcp = parseFloat(this.options.totals_view.variance_carbs_percents_value);
            var carbs_graph_score = this.calculateGraphScore(vcp);

            var vfp = parseFloat(this.options.totals_view.variance_fats_percents_value);
            var fats_graph_score = this.calculateGraphScore(vfp);
            
            var data = {};

            data.title = 'PROTEIN SCORE';
            data.width = '250px';
            data.level =  protein_graph_score + '%';
            $(this.el).find("#protein_score_graph").html(new Gredient_graph_view({data : data}).render().el);
            
            data.title = 'FATS SCORE';
            data.width = '250px';
            data.level =  fats_graph_score + '%';
            $(this.el).find("#fat_score_graph").html(new Gredient_graph_view({data : data}).render().el);
            
            data.title = 'CARBOHYDRATE SCORE';
            data.width = '250px';
            data.level =  carbs_graph_score + '%';
            $(this.el).find("#carbs_score_graph").html(new Gredient_graph_view({data : data}).render().el);
        },
        
        setWaterScore : function() {
            var water_score = this.options.totals_view.water_total;

            $(this.el).find("#water_score").html(water_score + '%');
        },
        
        setCalorieScore : function() {
            var calories_variance_percents = this.options.totals_view.variance_calories_percents;
            var cvp = calories_variance_percents;

            var calorie_score = 0;

            if(cvp < 200) {
                calorie_score = 100 + Math.abs(cvp) - ((1 + (100/200)) * Math.abs(cvp));
            } else {
                calorie_score = 100 + cvp;
            }

            calorie_score = this.round_2_sign(calorie_score);

            $(this.el).find("#calorie_score").html(calorie_score + '%');

        },
        
        setFinalScore : function() {
            var protein_variance = this.options.totals_view.variance_protein_percents_value;
            var carbs_variance = this.options.totals_view.variance_carbs_percents_value;
            var fats_variance = this.options.totals_view.variance_fats_percents_value;


            var protein= this.calculateScores(protein_variance);
            var carbs = this.calculateScores(carbs_variance);
            var fats = this.calculateScores(fats_variance);
            
            var total_score = this.calculateTotalScore(protein, carbs, fats);
            
            var final_score_field = $(this.el).find("#final_score");
            
            final_score_field.html(total_score + '%');
            
            this.setVarianceRangeFinalScore(final_score_field, total_score);
        },
        
        calculateScores : function(value) {
            var value = parseFloat(value);
            if(value < 0) {
                var score = 100 + value;
            } else {
                var score = 100 - value;
            }
            score = Math.abs(score);
            return this.round_2_sign(score);
        },
        
        calculateTotalScore : function(protein, carbs, fats) {
            var sum = protein + carbs + fats;
            return this.round_2_sign(sum / 3);
        },
        
        setVarianceRangeFinalScore : function(element, value) {
            var abs_value = Math.abs(value); 
            var input_class = '';
            element.removeClass('yellow_style_total green_style_total orange_style_total red_style_total');
            if((abs_value >= 0) && (abs_value <= 40)) {
                input_class = 'red_style_total'; 
            }

            if((abs_value > 40) && (abs_value <= 55)) {
                input_class = 'orange_style_total'; 
            }

            if((abs_value > 55) && (abs_value <= 79)) {
                input_class = 'yellow_style_total'; 
            }

            if((abs_value > 79) && (abs_value <= 93)) {
                input_class = 'green_style_total'; 
            }

            if(abs_value > 93) {
                input_class = 'blue_style_total'; 
            }

            element.addClass(input_class);
        },
        
        connectComments :function() {
            var comment_options = {
                'item_id' :  this.model.get('id'),
                'fitness_administration_url' : app.options.ajax_call_url,
                'comment_obj' : {'user_name' : app.options.user_name, 'created' : "", 'comment' : ""},
                'db_table' : '#__fitness_nutrition_diary_comments',
                'read_only' : true,
            }
            var comments = $.comments(comment_options, comment_options.item_id, '0');

            var comments_html = comments.run();
            $(this.el).find("#score_comments").html(comments_html);
        },
    });
            
    return view;

});