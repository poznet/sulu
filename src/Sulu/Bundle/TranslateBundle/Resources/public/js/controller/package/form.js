/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'jquery',
    'backbone',
    'router',
    'sulutranslate/model/package',
    'sulutranslate/model/catalogue'
], function ($, Backbone, Router, Package, Catalogue) {

    'use strict';

    var translatePackage;
    var cataloguesToDelete;
    var dataGrid;
    var $dialog;

    return Backbone.View.extend({

        events: {
            'submit #catalogue-form': 'submitForm',
            'click .icon-remove': 'deleteRow',
            'click .addRow': 'addRow'
        },

        initialize: function () {
            this.render();
        },

        getTabs: function (id) {
            //TODO Simplify this task for bundle developer?
            var cssId = id || 'new';

            // TODO translate
            var navigation = {
                'title': 'Catalogue',
                'header': {
                    'title': 'Catalogue'
                },
                'hasSub': 'true',
                //TODO id mandatory?
                'sub': {
                    'items': []
                }
            };

            if (!!id) {
                navigation.sub.items.push({
                    'title': 'Details',
                    'action': 'settings/translate/details:translate-package-' + cssId,
                    'hasSub': false,
                    'type': 'content',
                    'id': 'translate-package-details-' + cssId
                });
            }

            navigation.sub.items.push({
                'title': 'Settings',
                'action': 'settings/translate/settings:translate-package-' + cssId,
                'hasSub': false,
                'type': 'content',
                'id': 'translate-package-settings-' + cssId
            });

            return navigation;
        },

        render: function () {

            Backbone.Relational.store.reset(); //FIXME really necessary?
            require(['text!/translate/template/catalogue/form'], function (Template) {
                var template;

                cataloguesToDelete = new Array();

                if (!this.options.id) {
                    translatePackage = new Package();
                    template = _.template(Template, {name: '', locale: '', catalogues: []});
                    var catalogues = this.getArrayFromCatalogues(translatePackage.get('catalogues').models);
                    this.initializeCatalogueList(catalogues);
                    this.$el.html(template);
                } else {
                    translatePackage = new Package({id: this.options.id});
                    translatePackage.fetch({
                        success: function (translatePackage) {
                            template = _.template(Template, translatePackage.toJSON());
                            var catalogues = this.getArrayFromCatalogues(translatePackage.get('catalogues').models);
                            this.initializeCatalogueList(catalogues);
                            this.$el.html(template);

                        }.bind(this)
                    });
                }

                App.Navigation.trigger('navigation:item:column:show', {
                    data: this.getTabs(translatePackage.get('id'))
                });
            }.bind(this));
        },

        getArrayFromCatalogues: function (models) {

            var data = new Array();

            $.each(models, function (model) {
                data.push(models[model].attributes);
            });

            return data;
        },


        submitForm: function (event) {

            var that = this;

            event.preventDefault();

            translatePackage.set({name: this.$('#name').val()});

            var rows = $('#catalogues tbody tr');


            // create catalogues if necessary and add them

            for (var i = 1; i <= rows.length; i++) {

                var id = $(rows[i-1]).data('id');
                var locale = $('#catalogues tbody tr:nth-child(' + i + ') td:nth-child(2) input').val();
                var catalogue;

                if(id) {
                    catalogue = translatePackage.get('catalogues').get(id);
                } else {
                    catalogue = new Catalogue();
                    translatePackage.get('catalogues').add(catalogue);
                }
                catalogue.set({'locale': locale});
            }



            // send delete request for models which should be deleted

            cataloguesToDelete.forEach(function (id) {
                var model = translatePackage.get('catalogues').get(id);
                model.destroy({
                    success: function () {
                        console.log("deleted model");
                    }
                });
            });


            translatePackage.save(null, {
                success: function () {
                    that.undelegateEvents();
                    dataGrid.data('Husky.Ui.DataGrid').off();
                    Router.navigate('settings/translate');
                }
            });
        },

        initializeCatalogueList: function (data) {

            this.initializeDialog();

            require(['text!sulutranslate/templates/package/table-row.html'], function (RowTemplate) {
                dataGrid = $('#catalogues').huskyDataGrid({
                    pagination: false,
                    showPages: 6,
                    pageSize: 4,
                    selectItemType: 'radio',
                    removeRow: true,
                    template: {
                        row: RowTemplate
                    },
                    data: {
                        items: data
                    }
                });


                $('#addCatalogueRow').on('click', function () {
                    dataGrid.data('Husky.Ui.DataGrid').trigger('data-grid:row:add', { id: '', locale: '', translations: [] });
                });

                $('#catalogues').on('click', '.remove-row > span', function (event) {

                    $dialog.data('Husky.Ui.Dialog').trigger('dialog:show', {
                        template: {
                            content: '<h3><%= title %></h3><p><%= content %></p>',
                            footer: '<button class="btn btn-black closeButton"><%= buttonCancelText %></button><button class="btn btn-black agreeButton"><%= buttonSaveText %></button>',
                            header: '<button type="button" class="close">×</button>'
                        },
                        data: {
                            content: {
                                title: "Warning",
                                content: "Do you really want to delete this entry?"
                            },
                            footer: {
                                buttonCancelText: "No",
                                buttonSaveText: "Yes"
                            }
                        }
                    });

                    // TODO 
                    $dialog.off();

                    $dialog.on('click', '.closeButton', function() {
                        console.log("disagreed");
                        $dialog.data('Husky.Ui.Dialog').trigger('dialog:hide');
                    });


                    $dialog.on('click', '.agreeButton', function() {
                        console.log("agreed");

                        $dialog.data('Husky.Ui.Dialog').trigger('dialog:hide');
                        dataGrid.data('Husky.Ui.DataGrid').trigger('data-grid:row:remove', event);
                        var id = $(event.currentTarget).parent().parent().data('id');

                        if(id) {
                            console.log(id);
                            cataloguesToDelete.push(id);
                        }
                    });

                });

            }.bind(this));
        },

       initializeDialog: function(){

           $dialog = $('#dialog').huskyDialog({
               backdrop: true,
               width: '800px'
           });

       }

    });
});
