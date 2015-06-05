define(function(){"use strict";var a="sulu.media-edit.",b={infoKey:"public.info",versionsKey:"sulu.media.history",multipleEditTitle:"sulu.media.multiple-edit.title",loadingTitle:"sulu.media.edit.loading",instanceName:""},c={infoFormSelector:"#media-info",versionsFormSelector:"#media-versions",multipleEditFormSelector:"#media-multiple-edit",dropzoneSelector:"#file-version-change",multipleEditDescSelector:".media-description",multipleEditTagsSelector:".media-tags",descriptionCheckboxSelector:"#show-descriptions",tagsCheckboxSelector:"#show-tags",singleEditClass:"single-edit",multiEditClass:"multi-edit",loadingClass:"loading",loaderClass:"media-edit-loader"},d=function(){return h.call(this,"edit")},e=function(){return h.call(this,"loading")},f=function(){return h.call(this,"closed")},g=function(){return h.call(this,"initialized")},h=function(b){return a+(this.options.instanceName?this.options.instanceName+".":"")+b};return{templates:["/admin/media/template/media/info","/admin/media/template/media/versions","/admin/media/template/media/multiple-edit"],initialize:function(){this.options=this.sandbox.util.extend(!0,{},b,this.options),this.bindCustomEvents(),this.sandbox.dom.width(this.$el,0),this.sandbox.dom.height(this.$el,0),this.media=null,this.medias=null,this.$info=null,this.$versions=null,this.$multiple=null,this.startLoadingOverlay(),this.sandbox.emit(g.call(this))},bindCustomEvents:function(){this.sandbox.on(d.call(this),this.editMedia.bind(this)),this.sandbox.on("husky.overlay.media-edit.closed",function(){this.sandbox.emit(f.call(this))}.bind(this)),this.sandbox.on(e.call(this),function(){this.sandbox.emit("husky.overlay.media-edit.loading.open")}.bind(this))},editMedia:function(a){this.sandbox.dom.isArray(a)?this.editMultipleMedia(a):this.editSingleMedia(a)},editSingleMedia:function(a){this.media=a,this.$info=this.sandbox.dom.createElement(this.renderTemplate("/admin/media/template/media/info",{media:this.media})),this.$versions=this.sandbox.dom.createElement(this.renderTemplate("/admin/media/template/media/versions",{media:this.media})),this.startSingleOverlay()},editMultipleMedia:function(a){this.medias=a,this.$multiple=this.sandbox.dom.createElement(this.renderTemplate("/admin/media/template/media/multiple-edit")),this.bindMultipleEditDomEvents(),this.startMultipleEditOverlay()},startLoadingOverlay:function(){var a=this.sandbox.dom.createElement('<div class="'+c.loadingClass+'"/>'),b=this.sandbox.dom.createElement('<div class="'+c.loaderClass+'" />');this.sandbox.dom.append(this.$el,a),this.sandbox.once("husky.overlay.media-edit.loading.opened",function(){this.sandbox.start([{name:"loader@husky",options:{el:b,size:"100px",color:"#cccccc"}}])}.bind(this)),this.sandbox.start([{name:"overlay@husky",options:{el:a,title:this.sandbox.translate(this.options.loadingTitle),data:b,skin:"wide",openOnStart:!1,removeOnClose:!1,instanceName:"media-edit.loading",propagateEvents:!1,draggable:!1,closeIcon:"",okInactive:!0}}])},startSingleOverlay:function(){var a=this.sandbox.dom.createElement('<div class="'+c.singleEditClass+'"/>');this.sandbox.dom.append(this.$el,a),this.bindSingleOverlayEvents(),this.sandbox.start([{name:"overlay@husky",options:{el:a,title:this.media.title,tabs:[{title:this.sandbox.translate(this.options.infoKey),data:this.$info},{title:this.sandbox.translate(this.options.versionsKey),data:this.$versions}],skin:"wide",openOnStart:!0,instanceName:"media-edit",propagateEvents:!1,okCallback:function(){this.changeSingleModel()}.bind(this)}}])},bindSingleOverlayEvents:function(){this.sandbox.once("husky.overlay.media-edit.opened",function(){this.sandbox.form.create(c.infoFormSelector),this.sandbox.form.setData(c.infoFormSelector,this.media).then(function(){this.sandbox.start(c.infoFormSelector),this.startDropzone()}.bind(this))}.bind(this)),this.sandbox.once("husky.overlay.media-edit.initialized",function(){this.sandbox.emit("husky.overlay.media-edit.loading.close")}.bind(this)),this.sandbox.once("husky.dropzone.file-version-"+this.media.id+".initialized",function(){this.sandbox.emit("husky.overlay.media-edit.set-position")}.bind(this)),this.sandbox.once("husky.auto-complete-list.media-info-"+this.media.id+".initialized",function(){this.sandbox.emit("husky.overlay.media-edit.set-position")}.bind(this)),this.sandbox.on("husky.auto-complete-list.media-info-"+this.media.id+".item-added",function(){this.sandbox.emit("husky.overlay.media-edit.set-position")}.bind(this))},startMultipleEditOverlay:function(){var a=this.sandbox.dom.createElement('<div class="'+c.multiEditClass+'"/>');this.sandbox.dom.append(this.$el,a),this.bindMultipleOverlayEvents(),this.sandbox.start([{name:"overlay@husky",options:{el:a,title:this.sandbox.translate(this.options.multipleEditTitle),data:this.$multiple,openOnStart:!0,draggable:!1,propagateEvents:!1,closeIcon:!1,instanceName:"media-multiple-edit",okCallback:this.changeMultipleModel.bind(this),closeCallback:function(){this.sandbox.stop(c.multipleEditFormSelector+" *")}.bind(this)}}])},bindMultipleOverlayEvents:function(){this.sandbox.once("husky.overlay.media-multiple-edit.opened",function(){this.sandbox.form.create(c.multipleEditFormSelector).initialized.then(function(){this.sandbox.form.setData(c.multipleEditFormSelector,{records:this.medias}).then(function(){this.sandbox.start(c.multipleEditFormSelector),this.sandbox.emit("husky.overlay.media-multiple-edit.set-position")}.bind(this))}.bind(this))}.bind(this)),this.sandbox.once("husky.overlay.media-multiple-edit.initialized",function(){this.sandbox.emit("husky.overlay.media-edit.loading.close")}.bind(this)),this.sandbox.once("husky.overlay.media-multiple-edit.closed",function(){this.sandbox.stop("."+c.multiEditClass)}.bind(this))},bindMultipleEditDomEvents:function(){this.sandbox.dom.on(this.sandbox.dom.find(c.descriptionCheckboxSelector,this.$multiple),"change",this.toggleDescriptions.bind(this)),this.sandbox.dom.on(this.sandbox.dom.find(c.tagsCheckboxSelector,this.$multiple),"change",this.toggleTags.bind(this))},toggleDescriptions:function(){var a=this.sandbox.dom.is(this.sandbox.dom.find(c.descriptionCheckboxSelector,this.$multiple),":checked"),b=this.sandbox.dom.find(c.multipleEditDescSelector,this.$multiple);a===!0?(this.sandbox.dom.show(b),this.sandbox.dom.removeClass(b,"hidden")):(this.sandbox.dom.hide(b),this.sandbox.dom.addClass(b,"hidden")),this.sandbox.emit("husky.overlay.media-multiple-edit.set-position")},toggleTags:function(){var a=this.sandbox.dom.is(this.sandbox.dom.find(c.tagsCheckboxSelector,this.$multiple),":checked"),b=this.sandbox.dom.find(c.multipleEditTagsSelector,this.$multiple);a===!0?(this.sandbox.dom.show(b),this.sandbox.dom.removeClass(b,"hidden")):(this.sandbox.dom.hide(b),this.sandbox.dom.addClass(b,"hidden")),this.sandbox.emit("husky.overlay.media-multiple-edit.set-position")},startDropzone:function(){this.sandbox.off("husky.dropzone.file-version-"+this.media.id+".files-added",this.filesAddedHandler),this.sandbox.on("husky.dropzone.file-version-"+this.media.id+".files-added",this.filesAddedHandler,this),this.sandbox.start([{name:"dropzone@husky",options:{el:c.dropzoneSelector,url:"/admin/api/media/"+this.media.id+"?action=new-version",method:"POST",paramName:"fileVersion",showOverlay:!1,skin:"small",titleKey:"sulu.upload.small-dropzone-title",instanceName:"file-version-"+this.media.id,maxFiles:1}}])},filesAddedHandler:function(a){a[0]&&(this.media=this.sandbox.util.extend(!1,{},this.media,a[0]),this.sandbox.emit("husky.overlay.media-edit.close"),this.editSingleMedia(this.media),this.sandbox.emit("sulu.media.collections.save-media",this.media,this.savedCallback.bind(this),!0))},changeSingleModel:function(){if(this.sandbox.form.validate(c.infoFormSelector)){var a=this.sandbox.form.getData(c.infoFormSelector);return this.media=this.sandbox.util.extend(!1,{},this.media,a),this.sandbox.emit("sulu.media.collections.save-media",this.media,this.savedCallback.bind(this)),this.media=null,!0}return!1},changeMultipleModel:function(){if(this.sandbox.form.validate(c.multipleEditFormSelector)){var a=this.sandbox.form.getData(c.multipleEditFormSelector);return this.sandbox.util.foreach(this.medias,function(b,c){this.medias[c]=this.sandbox.util.extend(!1,{},b,a.records[c])}.bind(this)),this.sandbox.emit("sulu.media.collections.save-media",this.medias,this.savedCallback.bind(this)),this.medias=null,!0}return!1},savedCallback:function(){this.sandbox.emit("sulu.labels.success.show","labels.success.media-save-desc","labels.success")}}});