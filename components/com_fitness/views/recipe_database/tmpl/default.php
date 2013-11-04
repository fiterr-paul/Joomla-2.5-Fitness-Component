<?php
/**
 * @version     1.0.0
 * @package     com_fitness
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nikolay Korban <niklug@ukr.net> - http://
 */


// no direct access
defined('_JEXEC') or die;
?>

<div style="opacity: 1;" class="fitness_wrapper">
    <h2>RECIPE DATABASE</h2>
    
    <div id="recipe_mainmenu"></div>
    
    <div id="recipe_submenu"></div>
    
    <div id="recipe_main_container"></div>
    
</div>



<script type="text/javascript">
    
    (function($) {

        window.app = {};
        
        var options = {
            'fitness_frontend_url' : '<?php echo JURI::root();?>index.php?option=com_fitness&tmpl=component&<?php echo JSession::getFormToken(); ?>=1',
            'calendar_frontend_url' : '<?php echo JURI::root()?>index.php?option=com_multicalendar&task=load&calid=0',
            'user_name' : '<?php echo JFactory::getUser()->name;?>',
            'user_id' : '<?php echo JFactory::getUser()->id;?>',
            'recipes_db_table' : '#__fitness_nutrition_recipes',
            'recipe_types_db_table' : '#__fitness_recipe_types',
            'recipe_comments_db_table' : '#__fitness_nutrition_recipes_comments',
            'recipes_favourites_db_table' : '#__fitness_nutrition_recipes_favourites',
            'default_image' : 'administrator/components/com_fitness/assets/images/no_image.png'
        };
        
        // MODELS 
        window.app.Recipe_database_model = Backbone.Model.extend({


            ajaxCall : function(data, url, view, task, table, handleData) {
                return $.AjaxCall(data, url, view, task, table, handleData);
            },
            
            checkLocalStorage : function() {
                if(typeof(Storage)==="undefined") {
                   return false;
                }
                return true;
            },
          
            setLocalStorageItem : function(name, value) {
                if(!this.checkLocalStorage) return;
                localStorage.setItem(name, value);
            },
            
            getLocalStorageItem : function(name) {
                var value = this.get(name);
                if(!this.checkLocalStorage) {
                    return value;
                }
                var store_value =  localStorage.getItem(name);
                if(!store_value) return value;
                return store_value;
            },
          
            setStatus : function(status) {
                var style_class;
                var text;
                switch(status) {
                    case '1' :
                        style_class = 'recipe_status_pending';
                        text = 'PENDING';
                        break;
                    case '2' :
                        style_class = 'recipe_status_approved';
                        text = 'APPROVED';
                        break;
                    case '3' :
                        style_class = 'recipe_status_notapproved';
                        text = 'NOT APPROVED';
                        break;
                   
                    default :
                        style_class = 'recipe_status_pending';
                        text = 'PENDING';
                        break;
                }
                var html = '<a style="cursor:default;" href="javascript:void(0)"  class="status_button ' + style_class + '">' + text + '</a>';
                return html;
            },
             
        });
        
        
        window.app.Recipe_items_model = window.app.Recipe_database_model.extend({
            defaults: {
                current_page: 'my_recipes',
                filter_options : "",
                sort_by : 'recipe_name',
                order_dirrection : 'ASC',
                state : '1'
            },
            initialize: function(){
                this.connectPagination();
                this.setListeners();
            },
            
            setListeners : function() {
                this.bind("change:filter_options", this.resetCurrentPage, this);
                this.bind("change:recipe", this.onChangeRecipe, this);
                
                this.bind("change:favourite_removed", this.onRemoveFavourites, this);
                this.bind("change:favourite_added", this.onAddFavourites, this);
                
                this.bind("change:recipe_restored", this.onTrashRestored, this);
                this.bind("change:recipe_deleted", this.onRecipeDeleted, this);
                this.bind("change:recipe_trashed", this.onRecipeTrashed, this);
            },
            
            resetFilter : function() {
                this.unbind("change:filter_options", this.resetCurrentPage);
                this.set({'filter_options' : ''});
                this.bind("change:filter_options", this.resetCurrentPage, this);
                
                this.pagination_app_model.off("change:currentPage", this.loadRecipes);
                this.pagination_app_model.set({'currentPage' : ""});
                this.pagination_app_model.bind("change:currentPage", this.loadRecipes, this);
                this.pagination_app_model.setLocalStorageItem('currentPage', 1);
            },
 
            connectPagination : function() {
                this.pagination_app_model = $.backbone_pagination({});
                this.pagination_app_model.bind("change:currentPage", this.loadRecipes, this);
                this.pagination_app_model.bind("change:items_number", this.loadRecipes, this);
            },
            // on change filter options, reset pagination to 1 page
            resetCurrentPage : function() {
                this.pagination_app_model.setLocalStorageItem('currentPage', 1);
                this.pagination_app_model.off("change:currentPage", this.loadRecipes);
                this.pagination_app_model.set({'currentPage' : ""});
                this.pagination_app_model.bind("change:currentPage", this.loadRecipes, this);
                this.pagination_app_model.set({'currentPage' : 1});
            },
            

            getRecipes : function(page, limit) {
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'getRecipes';
                var table = this.get('recipes_db_table');
                
                data.sort_by = this.get('sort_by');
                data.order_dirrection = this.get('order_dirrection');

                data.page = page || 1;
                data.limit = limit;
                
                data.state = this.get('state');
                
                var filter_options = this.get('filter_options') || '';
                
                data.filter_options = filter_options;
                
                data.current_page = this.get('current_page');

                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipes", output);
                    
                });
            },
            
            
            loadRecipes : function() {

                //pagination
                var page = this.pagination_app_model.getLocalStorageItem('currentPage');
                var limit = this.pagination_app_model.getLocalStorageItem('items_number');
                //
                this.set({'recipes' : null});
                this.listenToOnce(this, "change:recipes", this.onGetRecipes);
                this.getRecipes(page, limit);
                
            },
            
            onGetRecipes : function() {
            
                if (this.has("recipes")){
                    var recipes = this.get("recipes");
                    
                    //pagination
                    var item = recipes[0];
                    var items_total = 0;
                    if (typeof item !== "undefined") {
                        items_total = item.items_total;
                    }
                    this.pagination_app_model.set({'items_total' : items_total});
                    //
                    this.populateRecipes(recipes);
                }
            },
            populateRecipes : function(recipes) {
            
                $("#recipe_database_items_wrapper").html('');
                
                if(recipes.length == 0) {
                    $("#recipe_database_items_wrapper").html('<div style="text-align:center;">No Recipes Found.</div>');
                }
                var recipe_item = new window.app.Recipe_item_view({ el: $("#recipe_database_items_wrapper"), model : this});
                _.each(recipes, function(item){
                    recipe_item.render(item);
                });
            },
            
            getRecipe : function(id) {
                if(!parseInt(id)) return;
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'getRecipe';
                var table = this.get('recipes_db_table');
                
                data.id = id;
                
                data.state = this.get('state');
                
                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipe", output);
                    //console.log(output);
                });
            },
            
            onChangeRecipe : function() {
                var recipe = this.get('recipe');
                             
                var current_page = this.get('current_page');


                if(current_page == 'my_recipes') {
                    new window.app.Submenu_myrecipe_view({ el: $("#submenu_container"), 'recipe_id' : recipe.id, 'is_favourite' : recipe.is_favourite});
                } else if (current_page == 'recipe_database') {
                    new window.app.Submenu_recipe_database_view({ el: $("#submenu_container"), 'recipe_id' : recipe.id, 'is_favourite' : recipe.is_favourite});
                } else if (current_page == 'my_favourites') {
                    new window.app.Submenu_my_favourites_view({ el: $("#submenu_container"), 'recipe_id' : recipe.id});
                }  else if (current_page == 'trash_list') {
                    new window.app.Submenu_trash_form_view({ el: $("#submenu_container"), 'recipe_id' : recipe.id});
                } else if (current_page == 'edit_recipe') {
                    return false;
                }
                
                this.populateRecipe(recipe);
            },
            
            populateRecipe : function(recipe) {
                var comment_options = {
                    'item_id' : recipe.id,
                    'fitness_administration_url' : this.attributes.fitness_frontend_url,
                    'comment_obj' : {'user_name' : this.attributes.user_name, 'created' : "", 'comment' : ""},
                    'db_table' : this.attributes.recipe_comments_db_table,
                    'read_only' : true,
                    'anable_comment_email' : false
                }
                var comments = $.comments(comment_options, comment_options.item_id, 0);
                var recipe_item = new window.app.Recipe_view({ el: $("#recipe_main_container"), model : this, 'comments' : comments});
                recipe_item.render(recipe);
            },
            
            copy_recipe : function(recipe_id){
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'copyRecipe';
                var table = this.get('recipes_db_table');
                
                data.id = recipe_id;

                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipe_copied", output);
                    //console.log(output);
                });
            },
            
            add_favourite : function(recipe_id){
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'addFavourite';
                var table = this.get('recipes_favourites_db_table');
                
                data.recipe_id = recipe_id;

                var self = this;
                this.set("favourite_added", null);
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("favourite_added", recipe_id);
                });
            },
            
            remove_favourite : function(recipe_id){
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'removeFavourite';
                var table = this.get('recipes_favourites_db_table');
                
                data.recipe_id = recipe_id;

                var self = this;
                this.set("favourite_removed", null);
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("favourite_removed", recipe_id);
                });
            },
            
            hide_recipe_item : function(recipe_id) {
                $(".recipe_database_item_wrapper[data-id='" + recipe_id + "']").fadeOut();
            },
            
            onRemoveFavourites : function(){
                var recipe_id = this.get('favourite_removed');
                var current_page = this.get('current_page');
                
                if(current_page == 'my_favourites') {
                    this.hide_recipe_item(recipe_id);
                }
                
                if((current_page == 'my_recipes') || (current_page == 'recipe_database')) {
                   $(".remove_favourites[data-id='" + recipe_id + "']").hide();
                   $(".add_favourite[data-id='" + recipe_id + "']").show();
                }
                
            },
            
            onAddFavourites : function() {
                var recipe_id = this.get('favourite_added');
                $(".remove_favourites[data-id='" + recipe_id + "']").show();
                $(".add_favourite[data-id='" + recipe_id + "']").hide();
            },
            
            delete_recipe : function(id) {
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'deleteRecipe';
                var table = this.get('recipes_db_table');
                
                data.id = id;

                var self = this;
                
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipe_deleted", id);
                    self.hide_recipe_item(id);
                });
            },
            
            restore_recipe : function(id) {
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'updateRecipe';
                var table = this.get('recipes_db_table');
                
                data.id = id;
                
                data.state = '1';

                var self = this;
                
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipe_restored", id);
                    self.hide_recipe_item(id);
                });
            },
            
            trash_recipe : function(id) {
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'updateRecipe';
                var table = this.get('recipes_db_table');
                
                data.id = id;
                
                data.state = '-2';

                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipe_trashed", id);
                    self.hide_recipe_item(id);
                });
            },
            
            onTrashRestored : function() {

                window.app.controller.navigate("!/trash_list", true);
            },
            
            onRecipeDeleted : function() {
                window.app.controller.navigate("!/trash_list", true);
            },
            
            onRecipeTrashed : function() {
                window.app.controller.navigate("!/my_recipes", true);
            }

        });
        
        
        
        window.app.Recipes_latest_model = window.app.Recipe_database_model.extend({
            initialize: function(){
                this.listenToOnce(this, "change:recipes_latest", this.populateRecipesLatest);
            },
            
            defaults: {
                limit : 15,
            },
            
            render : function(){
                this.populateRecipesLatest();
            },
            
            getRecipesLatest : function() {
                
                if(this.has("recipes_latest")) return;
                
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'getRecipes';
                var table = this.get('recipes_db_table');
                
                data.sort_by = 'created';
                data.order_dirrection = 'DESC';

                data.page = 1;
                data.limit = this.get('limit');
                
                data.state = '1';

                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipes_latest", output);
                    //console.log(output);
                });
            },
            
          
            populateRecipesLatest : function() {
                
                if(!this.has("recipes_latest")) {
                    this.getRecipesLatest();
                    return;
                }
                
                var recipes_latest = this.get('recipes_latest');
                
                var latest_recipes_wrapper_view = new window.app.Latest_recipes_wrapper_view({ el: $("#recipes_latest_wrapper")});
                latest_recipes_wrapper_view.render();
                
                $("#latest_recipes_container").html('');
                
                if(recipes_latest.length == 0) {
                    $("#latest_recipes_container").html('<div style="text-align:center;">No Recipes Found.</div>');
                }
                var recipe_item = new window.app.Latest_recipes_item_view({ el: $("#latest_recipes_container"), model : this});
                _.each(recipes_latest, function(item){
                    recipe_item.render(item);
                });

            },

        });
        
        
        window.app.Filter_categories_model = window.app.Recipe_database_model.extend({
            initialize: function() {
                this.listenToOnce(this, "change:recipe_types", this.populateRecipeTypes);
            },

            render : function(){
                this.populateRecipeTypes();
            },
            
            getRecipeTypes : function() {

                if(this.has("recipe_types")) return;
                
                var data = {};
                var url = this.get('fitness_frontend_url');
                var view = 'recipe_database';
                var task = 'getRecipeTypes';
                var table = this.get('recipe_types_db_table');

                var self = this;
                this.ajaxCall(data, url, view, task, table, function(output) {
                    self.set("recipe_types", output);
                    //console.log(output);
                });
            },

            populateRecipeTypes : function() {
                
                if(!this.has("recipe_types")) {
                    this.getRecipeTypes();
                    return;
                }
                
                var recipe_types = this.get('recipe_types');
                
                var categories_filter = new window.app.Filter_view({
                    el: $("#recipe_database_filter_wrapper"),
                    'recipe_types' : recipe_types
                });
                categories_filter.render();
            },
        });
        

                
        // VIEWS
        window.app.Views = { }; 
        
        window.app.Mainmenu_view = Backbone.View.extend({

            el: $("#recipe_mainmenu"), 

            render : function(){
                var template = _.template($("#recipe_database_mainmenu_template").html());
                this.$el.html(template);
            }
        });
        
        window.app.Submenu_view = Backbone.View.extend({
            el: $("#recipe_submenu"), 
            render : function(){
                var template = _.template($("#recipe_database_submenu_template").html());
                this.$el.html(template);
            }
        });
        
        window.app.Submenu_myrecipes_view = Backbone.View.extend({
            initialize: function(){
                this.render();
            },
            events: {
                "click #view_trash" : "onClickViewTrash",
                "click #new_recipe" : "onClickNewRecipe",
            },
            render : function(){
                var template = _.template($("#recipe_database_submenu_content_template").html());
                this.$el.html(template);
            },
             
            onClickViewTrash : function() {
                window.app.controller.navigate("!/trash_list", true);
            },
            
            onClickNewRecipe : function() {
                window.app.controller.navigate("!/edit_recipe/0", true);
            }
        });
        
        window.app.Submenu_myrecipe_view = Backbone.View.extend({
            initialize: function(){
                this.recipe_id = this.options.recipe_id;
                this.is_favourite = this.options.is_favourite;
                this.render();
            },
            events: {
                "click #close_recipe" : "onClickCloseRecipe",
                "click .add_favourite" : "onClickAddFavourite",
                "click .remove_favourites" : "onClickRemoveFavourites",
                "click .trash_recipe" : "onClickTrashRecipe",
                "click .edit_recipe" : "onClickEditRecipe",
            },
            render : function(){
                var variables = {'recipe_id' : this.recipe_id, 'is_favourite' : this.is_favourite};
                var template = _.template($("#submenu_my_recipes_template").html(), variables);
                this.$el.html(template);
            },
            onClickCloseRecipe : function() {
                window.history.back();
            },
            
            onClickAddFavourite : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.add_favourite(recipe_id);
            },
            
            onClickRemoveFavourites : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.remove_favourite(recipe_id);
            },
            
            onClickTrashRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.trash_recipe(recipe_id);
            },
            
            onClickEditRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.controller.navigate("!/edit_recipe/" + recipe_id, true);
            }
        });
        
        
        window.app.Submenu_recipe_database_view = Backbone.View.extend({
            initialize: function(){
                this.recipe_id = this.options.recipe_id;
                this.is_favourite = this.options.is_favourite;
                this.render();
            },
            events: {
                "click #close_recipe" : "onClickCloseRecipe",
                "click #copy_recipe" : "onClickCopyRecipe",
                "click .add_favourite" : "onClickAddFavourite",
                "click .remove_favourites" : "onClickRemoveFavourites",
                "click .trash_recipe" : "onClickTrashRecipe",
            },
            render : function(){
                var variables = {'recipe_id' : this.recipe_id, 'is_favourite' : this.is_favourite};
                var template = _.template($("#submenu_recipe_database_template").html(), variables);
                this.$el.html(template);
            },

            onClickCloseRecipe : function() {
                window.history.back();
            },
            
            onClickCopyRecipe : function() {
                window.app.recipe_items_model.copy_recipe(this.recipe_id);
            },
            
            onClickAddFavourite : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.add_favourite(recipe_id);
            },
            
            onClickRemoveFavourites : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.remove_favourite(recipe_id);
            },
            
            onClickTrashRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.trash_recipe(recipe_id);
            },
            
            onClickTrashRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.trash_recipe(recipe_id);
            },
        });
        
        
        window.app.Submenu_my_favourites_view = Backbone.View.extend({
            initialize: function(){
                this.listenToOnce(window.app.recipe_items_model, "change:favourite_removed", this.redirectToFavourites);
                this.recipe_id = this.options.recipe_id;
                this.render();
            },
            events: {
                "click #close_recipe" : "onClickCloseRecipe",
                "click .remove_favourites" : "onClickRemoveFavourites",
            },
            render : function(){
                var variables = {'recipe_id' : this.recipe_id};
                var template = _.template($("#submenu_my_favourites_template").html(), variables);
                this.$el.html(template);
            },
            onClickCloseRecipe : function() {
                window.history.back();
            },
            onClickRemoveFavourites : function(event) {
            
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.remove_favourite(recipe_id);
            },
            redirectToFavourites : function(){
                window.app.controller.navigate("!/my_favourites", true);
            }
        });
        
        
        window.app.Submenu_trash_list_view = Backbone.View.extend({
            initialize: function(){
                this.render();
            },
            events: {
                "click #close_trash_list" : "onClickCloseTrashList",
            },
            render : function(){
                var variables = {};
                var template = _.template($("#submenu_trash_list_template").html(), variables);
                this.$el.html(template);
            },

            onClickCloseTrashList : function(){
                window.app.controller.navigate("!/my_recipes", true);
            }
        });
        
        window.app.Submenu_trash_form_view = Backbone.View.extend({
            initialize: function(){
                this.recipe_id = this.options.recipe_id;
                this.render();
            },
            events: {
                "click .close_trash_form" : "onClickCloseTrashForm",
                "click .delete_recipe" : "onClickDeleteRecipe",
                "click .restore_recipe" : "onClickRestoreRecipe",
            },
            render : function(){
                var variables = {'recipe_id' : this.recipe_id};
                var template = _.template($("#submenu_trash_form_template").html(), variables);
                this.$el.html(template);
            },

            onClickCloseTrashForm : function(){
                window.history.back();
            },
            
            onClickDeleteRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.delete_recipe(recipe_id);
            },
            
            onClickRestoreRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.restore_recipe(recipe_id);
            }
        });
        
        window.app.Submenu_edit_recipe_view = Backbone.View.extend({
            initialize: function(){
                this.render();
            },
            events: {
                "click #save" : "onClickSave",
                "click #save_close" : "onClickSaveClose",
                "click #cancel" : "onClickCancel",
            },
            render : function(){
                var variables = {};
                var template = _.template($("#submenu_edit_recipe_template").html(), variables);
                this.$el.html(template);
            },
            
            onClickCancel : function() {
                window.app.recipe_items_model.set({current_page : 'my_recipes'});
                window.app.controller.navigate("!/my_recipes", true);
            }

        });
        
        // on open recipe
        window.app.Recipe_view = Backbone.View.extend({
             render : function(data){
                var data = data
                data.model = this.model;
                var template = _.template($("#recipe_database_view_recipe_template").html(), data);
                this.$el.html(template);
                this.loadComments();
            },
                        
            loadComments : function(){
                var comments_html = this.options.comments.run();
                $("#comments_wrapper").html(comments_html)
            },
            
        });
        
        // list item
        window.app.Recipe_item_view = Backbone.View.extend({
            initialize: function(){

            },
            render : function(data){
                var data = data
                data.model = this.model;
                var template = _.template($("#recipe_database_item_template").html(), data);
                this.$el.append(template);
            },
            
            events: {
                "click .view_recipe" : "onClickViewRecipe",
                "click #copy_recipe" : "onClickCopyRecipe",
                "click .add_favourite" : "onClickAddFavourite",
                "click .remove_favourites" : "onClickRemoveFavourites",
                "click .trash_recipe" : "onClickTrashRecipe",
                "click .delete_recipe" : "onClickDeleteRecipe",
                "click .restore_recipe" : "onClickRestoreRecipe",
            },
            
            onClickViewRecipe : function(event) {
                var id = $(event.target).attr("data-id");

                window.app.controller.navigate("!/nutrition_recipe/" + id, true);
            },
            
            onClickCopyRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                this.model.copy_recipe(recipe_id);
            },
            
            onClickAddFavourite : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                this.model.add_favourite(recipe_id);
            },
            
           
            onClickRemoveFavourites : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                this.model.remove_favourite(recipe_id);
            },
            
            onClickTrashRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.trash_recipe(recipe_id);
            },
            
            onClickDeleteRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.delete_recipe(recipe_id);
            },
            
            onClickRestoreRecipe : function(event) {
                var recipe_id = $(event.target).attr('data-id');
                window.app.recipe_items_model.restore_recipe(recipe_id);
            }
        });
        
        
        window.app.Filter_view = Backbone.View.extend({
            initialize: function(){
                
            },
            
            render : function(){
                var recipe_types = this.options.recipe_types;
                this.loadTemplate(recipe_types);
            },
            
            events: {
                "change #categories_filter" : "onFilterSelect",
            },
            
            loadTemplate : function(recipe_types) {
                var data = {'items' : recipe_types};
                var template = _.template($("#recipe_database_filter_template").html(), data);
                this.$el.html(template);
            },
            
            onFilterSelect : function(event){
                var ids = $(event.target).find(':selected').map(function(){ return this.value }).get().join(",");
                window.app.recipe_items_model.set({'filter_options' : ids});
                //console.log(ids);
            }
        });
        
        window.app.Latest_recipes_item_view = Backbone.View.extend({

            render : function(data){
                var data = data
                data.model = this.model;
                var template = _.template($("#recipes_latest_item_template").html(), data);
                this.$el.append(template);
            },
            
            events: {
                "click .view_recipe" : "onClickViewRecipe",
            },
            
            onClickViewRecipe : function(event) {
                var id = $(event.target).attr("data-id");

                window.app.controller.navigate("!/recipe_database", true);
                window.app.controller.navigate("!/nutrition_recipe/" + id, true);
            }
        });
        
        window.app.Latest_recipes_wrapper_view = Backbone.View.extend({
            
            initialize: function(){
                
            },
            
            render : function(){
                var template = _.template($("#recipes_latest_wrapper_template").html());
                this.$el.html(template);
            }
        });



        window.app.MainRecipesContainer_view = Backbone.View.extend({
            
            el: $("#recipe_main_container"), 

            render : function(){
                var template = _.template($("#recipe_database_recipes_container_template").html());
                this.$el.html(template);
            },

        });
        
        window.app.EditRecipeContainer_view = Backbone.View.extend({
            
            el: $("#recipe_main_container"), 

            render : function(id){
                if (window.app.recipe_items_model.get('current_page') != 'edit_recipe') { 
                    return false;
                }

                var id = parseInt(id);
                
                this.recipe  = {};
                
                if(id) {
                   
                    this.listenToOnce(window.app.recipe_items_model, "change:recipe", this.render);
                                        
                    this.recipe = window.app.recipe_items_model.get('recipe');
                    
                    if(!this.recipe) {
                        window.app.recipe_items_model.getRecipe(id);
                        return false;
                    }
                    
                    
                }
                
                if(this.recipe_types = this.options.filter_categories_model.get('recipe_types')) {
                   this.loadTemplate({'recipe_types' : this.recipe_types, 'recipe' : this.recipe});
                   return;
                }
                this.listenToOnce(this.options.filter_categories_model, "change:recipe_types", this.render);
                this.options.filter_categories_model.getRecipeTypes();

            },
            
            loadTemplate : function(data) {
                var template = _.template($("#recipe_database_edit_recipe_container_template").html(), data);
                this.$el.html(template);
            },


        });
        
        
        
        //Creation global object
        window.app.recipe_items_model = new window.app.Recipe_items_model(options);
        window.app.recipes_latest_model = new window.app.Recipes_latest_model(options);
        var filter_options = options;
        filter_options.recipe_items_model = window.app.recipe_items_model;
        window.app.filter_categories_model = new window.app.Filter_categories_model(filter_options);
        
        window.app.Views = { 
            mainmenu: new window.app.Mainmenu_view(),
            submenu: new window.app.Submenu_view(),
            recipes_container : new window.app.MainRecipesContainer_view(),
            edit_recipe_container : new window.app.EditRecipeContainer_view({'filter_categories_model' : window.app.filter_categories_model}),
        };
        //
        
         // CONTROLLER
         window.app.Controller = Backbone.Router.extend({

            routes : {
                "": "my_recipes", 
                "!/": "my_recipes", 
                "!/my_recipes": "my_recipes", 
                "!/recipe_database": "recipe_database", 
                "!/nutrition_database": "nutrition_database", 
                "!/nutrition_recipe/:id" : "nutrition_recipe",
                "!/my_favourites" : "my_favourites",
                "!/trash_list" : "trash_list",
                "!/edit_recipe/:id" : "edit_recipe",
            },

            my_recipes : function () {
                window.app.recipe_items_model.set({state : '1'});
                this.common_actions();
                $("#my_recipes_link").addClass("active_link");
                
                this.load_submenu();
                // populate submenu
                new window.app.Submenu_myrecipes_view({ el: $("#submenu_container")});
            
                window.app.recipe_items_model.set({current_page : 'my_recipes'});
                
                this.recipe_pages_actions();
             },

            recipe_database : function () {
                window.app.recipe_items_model.set({state : '1'});
                this.common_actions();
                $("#recipe_database_link").addClass("active_link");
                
                this.hide_submenu();
                
                window.app.recipe_items_model.set({current_page : 'recipe_database'});
                
                this.recipe_pages_actions();
            },
            
            recipe_pages_actions : function () {
                window.app.recipe_items_model.resetFilter();

                window.app.recipe_items_model.loadRecipes();
                
                window.app.recipe_items_model.connectPagination();
   
                window.app.filter_categories_model.render();
                
                window.app.recipes_latest_model.render();
            },
            
            my_favourites : function () {
                window.app.recipe_items_model.set({state : '1'});
                this.common_actions();
                $("#my_favourites_link").addClass("active_link");
                
                this.hide_submenu();
                
                window.app.recipe_items_model.set({current_page : 'my_favourites'});
                
                this.recipe_pages_actions();
             },

            nutrition_database : function () {
                window.app.recipe_items_model.set({state : '1'});
                this.hide_submenu();
                this.common_actions();
                $("#nutrition_database_link").addClass("active_link");
            },

            common_actions : function() {
                $(".block").hide();
                $(".plan_menu_link").removeClass("active_link");
                
                this.load_mainmenu();
                
                window.app.Views.recipes_container.render();

            },
            
            trash_list : function() {
                this.common_actions();
                
                this.load_submenu();
            
                new window.app.Submenu_trash_list_view({ el: $("#submenu_container")});
            
                window.app.recipe_items_model.set({state : '-2'});
                
                window.app.recipe_items_model.set({current_page : 'trash_list'});
                
                this.recipe_pages_actions();
            },
            
            load_mainmenu : function() {
                if (window.app.Views.mainmenu != null) {
                    window.app.Views.mainmenu.render();
                }
            },
            
            load_submenu : function() {
                if (window.app.Views.submenu != null) {
                    window.app.Views.submenu.render();
                }
            },
            
            hide_submenu : function() {
                $(window.app.Views.submenu.$el).html('');
            },
            
            clear_main_ontainer : function() {
                $("#recipe_main_container").html('');
            },

            nutrition_recipe : function(id) {
                
                this.clear_main_ontainer();
                this.load_submenu();
                
                window.app.recipe_items_model.getRecipe(id);

           },
           
           edit_recipe : function(id) {
               window.app.recipe_items_model.set({current_page : 'edit_recipe'});
               this.load_submenu();
               new window.app.Submenu_edit_recipe_view ({ el: $("#submenu_container")});
               window.app.Views.edit_recipe_container.render(id);
           }
 
            
        });

        window.app.controller = new window.app.Controller(); 

        Backbone.history.start();  
        
        
        
        
        
        
        

        

    })($js);
    

        
</script>