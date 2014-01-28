define([
	'jquery',
	'underscore',
	'backbone',
        'app',
	'text!templates/recipe_database/frontend/recipe_database_item.html'
        
], function ( $, _, Backbone, app, template ) {

    var view = Backbone.View.extend({
        
        initialize : function() {
            this.controller = app.routers.recipe_database;
        },

        template:_.template(template),
        
        render: function(){
            var data = this.model.toJSON();
            data.$ = $;
            data.app = app;
            var template = _.template(this.template(data));
            this.$el.html(template);
            
            this.connectComments();

            return this;
        },

        events: {
            "click #pdf_button_recipe" : "onClickPdf",
            "click #email_button_recipe" : "onClickEmail",
        },

        onClickPdf : function(event) {
            var id = $(event.target).attr('data-id');
            var htmlPage = app.options.base_url + 'index.php?option=com_multicalendar&view=pdf&tpml=component&layout=email_pdf_recipe&id=' + id + '&client_id=' + app.options.client_id;
            $.fitness_helper.printPage(htmlPage);
          
        },

        onClickEmail : function(event) {
            var data = {};
            data.url = app.options.ajax_call_url;
            data.view = '';
            data.task = 'ajax_email';
            data.table = '';

            data.id = $(event.target).attr('data-id');
            data.view = 'NutritionPlan';
            data.method = 'email_pdf_recipe';
            $.fitness_helper.sendEmail(data);
        },

        connectComments : function(){
            var comment_options = {
                'item_id' : this.model.get('id'),
                'fitness_administration_url' : app.options.fitness_frontend_url,
                'comment_obj' : {'user_name' : app.options.user_name, 'created' : "", 'comment' : ""},
                'db_table' : app.options.recipe_comments_db_table,
                'read_only' : true,
                'anable_comment_email' : true,
                'comment_method' : 'RecipeComment'
            }
            var comments_html = $.comments(comment_options, comment_options.item_id, 0).run();
            this.$el.find("#comments_wrapper").html(comments_html);
        },

        
    });
            
    return view;
});