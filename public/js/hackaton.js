const createHackathonsPanel = () => {
    
    Ext.define('App.model.Hackathon', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'id', type: 'int'},
            {name: 'nombre', type: 'string'},
            {name: 'descripcion', type: 'string'},
            {name: 'fechaInicio', type: 'date', dateFormat: 'Y-m-d'},
            {name: 'fechaFin', type: 'date', dateFormat: 'Y-m-d'},
            {name: 'lugar', type: 'string'},
            {name: 'estado', type: 'string'}
        ]
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create('Ext.window.Window', {
            title: isNew ? '🆕 Nuevo Hackathon' : '✏️ Editar Hackathon',
            modal: true,
            layout: 'fit',
            width: 600,
            cls: 'dialog-window',
            items: [{
                xtype: 'form',
                bodyPadding: 20,
                cls: 'dialog-form',
                defaults: {
                    anchor: '100%',
                    allowBlank: false,
                    labelWidth: 120
                },
                items: [
                    { xtype: 'hidden', name: 'id'},
                    { 
                        xtype: 'textfield', 
                        name: 'nombre', 
                        fieldLabel: 'Nombre', 
                        allowBlank: false,
                        emptyText: 'Ej: EduHack 2025'
                    },
                    { 
                        xtype: 'textareafield', 
                        name: 'descripcion', 
                        fieldLabel: 'Descripción', 
                        allowBlank: false,
                        height: 80,
                        emptyText: 'Describe el hackathon...'
                    },
                    { 
                        xtype: 'datefield', 
                        name: 'fechaInicio', 
                        fieldLabel: 'Fecha Inicio', 
                        format: 'Y-m-d',
                        submitFormat: 'Y-m-d',
                        allowBlank: false
                    },
                    { 
                        xtype: 'datefield', 
                        name: 'fechaFin', 
                        fieldLabel: 'Fecha Fin', 
                        format: 'Y-m-d',
                        submitFormat: 'Y-m-d',
                        allowBlank: false
                    },
                    { 
                        xtype: 'textfield', 
                        name: 'lugar', 
                        fieldLabel: 'Lugar',
                        allowBlank: false,
                        emptyText: 'Ej: Universidad XYZ'
                    },
                    { 
                        xtype: 'combobox', 
                        name: 'estado', 
                        fieldLabel: 'Estado',
                        store: ['planificacion', 'activo', 'finalizado'],
                        queryMode: 'local',
                        editable: false,
                        value: 'planificacion'
                    }
                ],
                buttons: [
                    {
                        text: '💾 Guardar',
                        cls: 'save-button',
                        handler() {
                            const form = this.up('form').getForm();
                            if (!form.isValid()) return;
                            form.updateRecord(rec);
                            if (isNew) hackathonStore.add(rec);
                            hackathonStore.sync({
                                success: () => {
                                    Ext.Msg.alert('✅ Éxito', 'Hackathon guardado correctamente');
                                    this.up('window').close();
                                },
                                failure: () => {
                                    Ext.Msg.alert('❌ Error', 'No se pudo guardar el hackathon');
                                }
                            });
                        }
                    },
                    {
                        text: '❌ Cancelar',
                        cls: 'cancel-button',
                        handler: function() { win.close(); }
                    }
                ]
            }]
        });
        win.down('form').loadRecord(rec);
        win.show();
    }

    const hackathonStore = Ext.create('Ext.data.Store', {
        storeId: 'hackathonStore',
        model: 'App.model.Hackathon',
        proxy: {
            type: 'rest',
            url: 'api/hackathons.php',
            reader: {
                type: 'json'
            }
        },
        autoLoad: true,
        autoSync: false
    });

    return Ext.create('Ext.grid.Panel', {
        id: 'hackathonsPanel',
        title: '🏛️ Gestión de Hackathons',
        store: hackathonStore,
        cls: 'main-grid',
        columns: [
            {
                text: 'ID',
                dataIndex: 'id',
                width: 60,
                align: 'center'
            },
            {
                text: 'Nombre',
                dataIndex: 'nombre',
                flex: 2,
                renderer: function(value) {
                    return `<strong>${value}</strong>`;
                }
            },
            {
                text: 'Descripción',
                dataIndex: 'descripcion',
                flex: 3
            },
            {
                text: 'Inicio',
                dataIndex: 'fechaInicio',
                width: 120,
                xtype: 'datecolumn',
                format: 'Y-m-d'
            },
            {
                text: 'Fin',
                dataIndex: 'fechaFin',
                width: 120,
                xtype: 'datecolumn',
                format: 'Y-m-d'
            },
            {
                text: 'Lugar',
                dataIndex: 'lugar',
                width: 150
            },
            {
                text: 'Estado',
                dataIndex: 'estado',
                width: 120,
                renderer: function(value) {
                    const colors = {
                        'planificacion': '#ffa500',
                        'activo': '#00d4aa',
                        'finalizado': '#ff6b6b'
                    };
                    return `<span style="color: ${colors[value] || '#fff'}; font-weight: bold;">● ${value}</span>`;
                }
            }
        ],
        tbar: [
            {
                text: '➕ Nuevo Hackathon',
                cls: 'add-button',
                handler: () => openDialog(Ext.create('App.model.Hackathon'), true)
            },
            {
                text: '✏️ Editar',
                cls: 'edit-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un hackathon');
                    openDialog(rec, false);
                }
            },
            {
                text: '🗑️ Eliminar',
                cls: 'delete-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un hackathon');

                    Ext.Msg.confirm('🗑️ Confirmar', '¿Eliminar este hackathon?', btn => {
                        if (btn === 'yes') {
                            hackathonStore.remove(rec);
                            hackathonStore.sync({
                                success: () => Ext.Msg.alert('✅ Éxito', 'Hackathon eliminado'),
                                failure: () => Ext.Msg.alert('❌ Error', 'No se pudo eliminar')
                            });
                        }
                    });
                }
            },
            '->',
            {
                text: '🔄 Actualizar',
                cls: 'refresh-button',
                handler() {
                    hackathonStore.reload();
                }
            }
        ]
    });
};

window.createHackathonsPanel = createHackathonsPanel;
