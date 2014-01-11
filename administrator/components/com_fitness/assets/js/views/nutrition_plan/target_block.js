define([
	'jquery',
	'underscore',
	'backbone',
	'text!templates/nutrition_plan/target_block.html'
], function ( $, _, Backbone, template ) {

    var view = Backbone.View.extend({
            initialize: function(){
                _.bindAll(this, 'setTargetData', 'render');
            },
            
            template:_.template(template),
            
            render: function(){
                var template = _.template(this.template(this.model.toJSON()));
                this.$el.html(template);
   
                setTimeout(this.setTargetData,100);
                  
                return this;
            },
            
            setTargetData : function() {
                var activity_level;
                var type = this.model.get('type');
                
                var tite_container = this.$el.find(".title");
                
                if(type == 'heavy') {
                    activity_level = '1';
                    tite_container.text('Heavy Training Day');
                    tite_container.css('color', '#AD0C0C');
                }
                if(type == 'light') {
                    activity_level = '2';
                    tite_container.text('Light Training Day');
                    tite_container.css('color', '#0D7F22');
                }
                if(type == 'rest') {
                    activity_level = '3';
                    tite_container.text('Recovery / Rest Day');
                    tite_container.css('color', '#223FAA');
                }

                var data = [
                    {label: "Protein:", data: [[1, this.model.get('protein')]]},
                    {label: "Carbs:", data: [[1, this.model.get('carbs')]]},
                    {label: "Fat:", data: [[1, this.model.get('fats')]]}
                ];

                var container = this.$el.find(".placeholder_pie");
                
                var targets_pie = $.drawPie(data, container, {'no_percent_label' : false});

                targets_pie.draw(); 
                
            },
        });
            
    return view;
});