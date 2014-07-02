require([
    'jquery',
    'underscore',
    'backbone',
    'moment',
    'app',
    'routers/nutrition_plan/router_backend',
    'jquery.AjaxCall',
    'jquery.comments',
    'jquery.fitness_helper',
    'jquery.flot',
    'jquery.flot.time',
    'jquery.flot.pie',
    'jquery.drawPie',
    'jqueryui',
    'backbone.syphon',
    'jquery.backbone_pagination',
    'jquery.nutritionPlan',
    'jquery.macronutrientTargets',
    'jquery.status',
    'jquery.ajax_indicator'
    

], function($, _, Backbone, moment, app, Controller) {
    $.ajax_indicator({});
    $.fitness_helper = $.fitness_helper(app.options);
    Backbone.emulateHTTP = true ;
    Backbone.emulateJSON = true;

    app.routers.controller = new Controller();
  
   Backbone.history.start();
   

});
