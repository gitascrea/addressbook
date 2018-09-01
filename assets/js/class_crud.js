/**
 * helper class for doing CRUD and Database Conection
 *
 * @package     CRUD
 * @author      Antonio Scarfone
 * @copyright   2018 Coeln Concept
 */

/**
 * Class CRUD -> Create, Read, Update, Delete
 */
class CRUD {

    // Contructor init read all records
    constructor(action = "readRecords", clientRendering = '', templateName = '', templateEngine = '', templateRef = '', templatePath = '', controller = '') {
        this.records = null;
        this.ref = templateRef;
        this.clientRendering = parseInt(clientRendering);
        this.templateName = templateName;
        this.templateEngine = templateEngine;
        this.tableTemplate = $("#script_"+this.ref).html();
        this.controller = controller;
        this.template = null;
        this.templatePath = templatePath;
        this.templateInstance = new Array();
        this.templateNames = new Array();
        this.refs = new Array();
        this.getTemplate(this.templateName,this.ref);
        //this.performAction(action); // is no more longer neccessary because of resolution by method call of named methods like ex. readRecords()
        this.call(action)
    }

    //########## H E L P E R ##################

    //get template form server
    getTemplate(templateName,ref) {
        if (this.clientRendering > 0 && templateName.length > 0) {
            if (typeof this.templateInstance[ref] === 'undefined') {
                try { 
                    if (this.templateEngine == "twig") {
                        this.template = Twig.twig({
                            href: this.templatePath + templateName,
                            async: false
                        });
                    }
                    if (this.templateEngine == "nunjucks") {
                        this.template = nunjucks.configure(this.templatePath, { autoescape: true});
                    }
                    if (this.templateEngine == "underscore") {
                        this.template = _;
                    }
                    this.templateInstance[ref] = this.template;
                    this.templateNames[ref] = templateName;
                    this.refs[ref] = ref;

                    this.ref = ref;
                    this.templateName = templateName;
                    this.tableTemplate = $("#script_"+ref).html();
                } catch(e) { 
                    //do nothing
                }
            } else {
                this.template = this.templateInstance[ref];
                this.templateName = this.templateNames[ref];
                this.ref = this.refs[ref];
                this.tableTemplate = $("#script_"+this.ref).html();
            }
        }
    }

    //capitalize first letter of string
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    //call template
    callTemplate(ref) {
        this.getTemplate(ref+"_"+this.templateEngine+".twig", ref);
    }

    // Get Controller Url
    getController(action) {
        return this.controller + action;
    }

    // Perform Action -> call class method
    performAction(action = "") {
        var that = this;
		try {
            //call class method
            that[action](this);
        } catch (error) {
            console.log("Es konnten keine Datensätze gelesen werden!");
        }
    }

    //render json-data with template engine
    renderAll(ref) {
        var self = this;
        var my_data = "";
        var data_obj = {records: self.records};
        if (self.templateEngine == "underscore") {
            records = self.records; // underscore needs a global var records
            my_data = self.template.template(self.tableTemplate, data_obj);
            console.log("Underscore client-rendering (MVVM) " + ref);
        }
        if (self.templateEngine == "twig") {
            my_data = self.template.render(data_obj);
            console.log("Twig client-rendering (MVVM) " + ref);
        }
        if (self.templateEngine == "nunjucks") {
            my_data = self.template.render(self.templateName, data_obj);
            console.log("Nunjucks client-rendering (MVVM) " + ref);
        }
        return my_data;
    }
   
    //display data to dom
    displayData(ref, mode, data) {
        if (mode=="html") {
            $("#"+ref).html(data);
        }
        if (mode=="append") {
            $("body").append(data);
            $("#"+ref).modal("show");
        }
        if (mode=="prepend") {
            $("body").prepend(data);
        }
    }

    //handle Server data
    handleServerData(ref, data, mode, reload = "", hide="") {
        var self = this;
        var my_data = "";
        try { // if JSON-Data from Server so render client-side with twig,underscore or nunjucks
           self.records = JSON.parse(data);
           if (self.clientRendering > 0) {
              my_data = self.renderAll(ref);
           } else {
              var err = "JSON-Data parsed, but no clientRendering on!";
              my_data = err;
              console.log(err);
           }
        } catch(e) { // if not JSON-Data, so it must be else Html rendered by server or json error
            my_data = data;
            console.log("Twig server-rendering (MVC)");
        }
        
        if (reload=="readRecords") {
            //self.readRecords();
            self.call(reload);
            $("#"+hide).modal("hide");
        }
        
        self.displayData(ref, mode, my_data);
    }

    //Get model object
    getModel(ref, model) {
        if (model=="users") {
            return {
                first_name: $("#"+ref+"_first_name").val(),
                last_name: $("#"+ref+"_last_name").val(),
                email: $("#"+ref+"_email").val(),
                id: $("#"+ref+"_id").val()
            };
        }
        return {};
    }

    //########## C R U D ##################

    //calls every crud method possible through ref magic string
    call(ref = "readRecords", args = {id: "", ref2: "", method: "get", remove: false, render: "html", model: "", reload: "", conf: ""}) {
        var self = this;
        var ref = ref;
        var ref2= args.ref2;
        var method = args.method;
        var id = args.id;
        var remove = args.remove;
        var render = args.render;
        var model = args.model;
        var reload = args.reload;
        var _confirm = false;
        
        if (args.conf=="") {
            _confirm = true;
        } else {
            _confirm = confirm(args.conf);
        }
        var model_obj = null;
        var _controller = self.getController(ref);
        if (remove) {
            $("#"+ref).remove();
        }
        this.callTemplate(ref);
        if (id=="") {
            model_obj = self.getModel(ref2, model);
        } else {
            model_obj = {id: id};
        }
        
        if (_confirm == true) {
            if (method=="get") {
                $.get(_controller, model_obj, function (data, status) {
                    self.handleServerData(ref, data, render, reload, ref2);
                });
            } else {
                $.post(_controller, model_obj, function (data, status) {
                    self.handleServerData(ref, data, render, reload, ref2);
                });
            }
        }
    }
  
}

