YUI.add("moodle-block_asp-comments",function(e,t){var n="blocks_asp_comments",r="/blocks/asp/ajax.php",i="stateid",s="editorid",o="editorname",u={BLOCKWORKFLOW:"block_asp",BLOCKCOMMENTS:"block_asp_comments",BLOCKCOMMBTN:"block_asp_editcommentbutton",BLOCKFINISHBTN:"block_asp_finishstepbutton",PANEL:"block-asp-panel",CONTENT:"content",COMMENTS:"wkf-comments",LIGHTBOX:"loading-lightbox",LOADINGICON:"loading-icon",TEXTAREA:"wfk-textarea",SUBMIT:"wfk-submit",HIDDEN:"hidden"},a=new M.core.dialogue({headerContent:"",bodyContent:e.one("."+u.PANEL),visible:!1,modal:!0,width:"auto",zIndex:100}),f=function(){f.superclass.constructor.apply(this,arguments)};e.extend(f,e.Base,{_formSubmitEvent:null,_escCloseEvent:null,_closeButtonEvent:null,_loadingNode:null,initializer:function(){a.hide();var t=e.one("."+u.PANEL);if(!t)return;this._loadingNode=t.one("."+u.LIGHTBOX),this.attachEvents()},show:function(t,n){t.halt(),n?(a.set("headerContent",M.str.block_asp.finishstep),e.one("."+u.PANEL).one("."+u.SUBMIT+" input").set("value",M.str.block_asp.finishstep),this._formSubmitEvent=e.one("."+u.SUBMIT+" input").on("click",this.finishstep,this)):(a.set("headerContent",M.str.block_asp.editcomments),e.one("."+u.PANEL).one("."+u.SUBMIT+" input").set("value",M.str.moodle.savechanges),this._formSubmitEvent=e.one("."+u.SUBMIT+" input").on("click",this.save,this)),a.show(),this._escCloseEvent=e.on("key",this.hide,document.body,"down:27",this),e.Event.purgeElement(e.one(".moodle-dialogue-hd .closebutton"),!0),this._closeButtonEvent=e.on("click",this.hide,e.one(".moodle-dialogue-hd .closebutton"),this);var o={sesskey:M.cfg.sesskey,action:"getcomment",stateid:this.get(i)};if(typeof tinyMCE!="undefined"){var f=tinyMCE.get(this.get(s)),l=tinymce.DOM.get(this.get(s)+"_ifr"),c=tinymce.DOM.getSize(l);c.h===30&&f.theme.resizeTo(c.w,90)}e.io(M.cfg.wwwroot+r,{method:"POST",data:build_querystring(o),on:{start:this.displayLoading,complete:function(t,n){var r;try{r=e.JSON.parse(n.responseText);if(r.error)return new M.core.ajaxException(r)}catch(i){new M.core.exception(i)}if(typeof tinyMCE!="undefined")f.setContent(r.response.comment);else{var o=this.get(s),u=e.one(document.getElementById(o+"editable"));u&&u.setHTML(r.response.comment),e.one(document.getElementById(o)).set("value",r.response.comment)}},end:this.removeLoading},context:this})},hide:function(){a.hide(),this._escCloseEvent&&(this._escCloseEvent.detach(),this._escCloseEvent=null),this._closeButtonEvent&&(this._closeButtonEvent.detach(),this._closeButtonEvent=null),this._formSubmitEvent&&(this._formSubmitEvent.detach(),this._formSubmitEvent=null)},save:function(){var t;typeof tinyMCE!="undefined"?t=tinyMCE.get(this.get(s)).getContent():t=e.one(document.getElementById(this.get(s))).get("value");var n=e.one("."+u.BLOCKWORKFLOW+" ."+u.BLOCKCOMMENTS),a={sesskey:M.cfg.sesskey,action:"savecomment",stateid:this.get(i),text:t,format:document.getElementsByName(this.get(o)+"[format]")[0].value};e.io(M.cfg.wwwroot+r,{method:"POST",data:build_querystring(a),on:{start:this.displayLoading,complete:function(t,r){var i;try{i=e.JSON.parse(r.responseText);if(i.error)return new M.core.ajaxException(i)}catch(s){new M.core.exception(s)}i.response.blockcomments?n.setContent(i.response.blockcomments):n.setContent(M.str.block_asp.nocomments)},end:this.removeLoading},context:this}),this.hide()},finishstep:function(){var t;typeof tinyMCE!="undefined"?t=tinyMCE.get(this.get(s)).getContent():t=e.one(document.getElementById(this.get(s))).get("value");var n=e.one("."+u.BLOCKWORKFLOW+" ."+u.CONTENT),a={sesskey:M.cfg.sesskey,action:"finishstep",stateid:this.get(i),text:t,format:document.getElementsByName(this.get(o)+"[format]")[0].value};e.io(M.cfg.wwwroot+r,{method:"POST",data:build_querystring(a),on:{start:this.displayLoading,complete:function(t,r){var s;try{s=e.JSON.parse(r.responseText);if(s.error)return new M.core.ajaxException(s)}catch(o){new M.core.exception(o)}if(s.response.blockcontent){n.setContent(s.response.blockcontent),s.response.stateid&&(this.set(i,s.response.stateid),this.attachEvents(),M.blocks_asp.init_todolist({stateid:s.response.stateid}));if(s.response.listasps){var u=n.one(".singleselect form select").getAttribute("id");M.core.init_formautosubmit({selectid:u,nothing:""})}}},end:this.removeLoading},context:this}),this.hide()},displayLoading:function(){this._loadingNode.removeClass(u.HIDDEN)},removeLoading:function(){this._loadingNode.addClass(u.HIDDEN)},attachEvents:function(){var t=e.one("."+u.BLOCKCOMMBTN+" input");t&&t.on("click",this.show,this,!1);var n=e.one("."+u.BLOCKFINISHBTN+" input");n&&n.on("click",this.show,this,!0)}},{NAME:n,ATTRS:{stateid:{value:null},editorid:{value:null},editorname:{validator:e.Lang.isString,value:null}}}),M.blocks_asp=M.blocks_asp||{},M.blocks_asp.init_comments=function(e){return new f(e)}},"@VERSION@",{requires:["base","overlay","moodle-core-formautosubmit","moodle-core-notification"]});
