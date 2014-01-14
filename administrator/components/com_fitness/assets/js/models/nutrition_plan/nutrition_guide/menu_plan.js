define([
    'underscore',
    'backbone',
    'app'
], function ( _, Backbone, app) {
    var model = Backbone.Model.extend({
            urlRoot : app.options.ajax_call_url + '&format=text&view=nutrition_plan&task=nutrition_plan_menu&',
            
            defaults : {
                id : null,
                nutrition_plan_id : app.options.item_id,
                name : null,
                start_date : null,
                created_by : null,
                status : 1,
                assessed_by : null,
            },
            
            validate: function(attrs, options) {
                if (!attrs.name) {
                  return 'Menu Plan Name is empty';
                }
            }
        });
    return model;
});