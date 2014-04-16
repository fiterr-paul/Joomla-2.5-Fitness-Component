define([
	'jquery',
	'underscore',
	'backbone',
        'app',
	'text!templates/programs/backend/form_workout_instructions.html'
], function (
        $,
        _,
        Backbone,
        app,
        template
    ) {

    var view = Backbone.View.extend({
        
        initialize : function() {
           this.render();
        },

        
        template:_.template(template),
        
        render: function(){
            var template = _.template(this.template(this.model.toJSON()));
            this.$el.html(template);
            return this;
        },
        


    });
            
    return view;
});