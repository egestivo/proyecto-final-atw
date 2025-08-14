const createEquiposPanel = () => {
    
    Ext.define('App.model.Equipo', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'id', type: 'int'},
            {name: 'nombre', type: 'string'},
            {name: 'descripcion', type: 'string'},
            {name: 'hackathonId', type: 'int'},
            {name: 'fechaFormacion', type: 'date', dateFormat: 'Y-m-d'},
            {name: 'estado', type: 'string'},
            // Campos de hackathon relacionado
            {name: 'hackathon_nombre', type: 'string'}
        ]
    });

    const openDialog = (rec, isNew) => {
        // Primero cargar hackathons disponibles
        const hackathonStore = Ext.create('Ext.data.Store', {
            fields: ['id', 'nombre'],
            proxy: {
                type: 'rest',
                url: 'api/hackathons.php'
            },
            autoLoad: true
        });

        const win = Ext.create('Ext.window.Window', {
            title: isNew ? '🆕 Nuevo Equipo' : '✏️ Editar Equipo',
            modal: true,
            layout: 'fit',
            width: 650,
            height: 500,
            cls: 'dialog-window',
            items: [{
                xtype: 'form',
                bodyPadding: 20,
                cls: 'dialog-form',
                defaults: {
                    anchor: '100%',
                    labelWidth: 150
                },
                items: [
                    { xtype: 'hidden', name: 'id'},
                    { 
                        xtype: 'textfield', 
                        name: 'nombre', 
                        fieldLabel: 'Nombre del Equipo', 
                        allowBlank: false,
                        emptyText: 'Ej: Los Innovadores'
                    },
                    { 
                        xtype: 'textareafield', 
                        name: 'descripcion', 
                        fieldLabel: 'Descripción',
                        height: 100,
                        emptyText: 'Describe la visión y objetivos del equipo...'
                    },
                    { 
                        xtype: 'combobox', 
                        name: 'hackathonId', 
                        fieldLabel: 'Hackathon',
                        store: hackathonStore,
                        displayField: 'nombre',
                        valueField: 'id',
                        queryMode: 'local',
                        editable: false,
                        allowBlank: false,
                        emptyText: 'Selecciona un hackathon...'
                    },
                    { 
                        xtype: 'datefield', 
                        name: 'fechaFormacion', 
                        fieldLabel: 'Fecha de Formación',
                        format: 'Y-m-d',
                        allowBlank: false,
                        value: new Date()
                    },
                    { 
                        xtype: 'combobox', 
                        name: 'estado', 
                        fieldLabel: 'Estado',
                        store: [
                            ['formacion', '🏗️ En Formación'],
                            ['activo', '✅ Activo'],
                            ['trabajando', '⚡ Trabajando'],
                            ['finalizado', '🏁 Finalizado'],
                            ['disuelto', '❌ Disuelto']
                        ],
                        queryMode: 'local',
                        editable: false,
                        allowBlank: false,
                        value: 'formacion'
                    },
                    {
                        xtype: 'fieldset',
                        title: '👥 Gestión de Participantes',
                        margin: '10 0 0 0',
                        items: [
                            {
                                xtype: 'displayfield',
                                value: 'Aquí podrás gestionar los miembros del equipo una vez guardado.',
                                fieldStyle: 'color: #666; font-style: italic;'
                            }
                        ]
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
                            if (isNew) equipoStore.add(rec);
                            equipoStore.sync({
                                success: () => {
                                    Ext.Msg.alert('✅ Éxito', 'Equipo guardado correctamente');
                                    this.up('window').close();
                                },
                                failure: () => {
                                    Ext.Msg.alert('❌ Error', 'No se pudo guardar el equipo');
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

    const openMembersDialog = (equipoRec) => {
        const participanteStore = Ext.create('Ext.data.Store', {
            fields: ['id', 'nombre', 'email', 'tipo'],
            proxy: {
                type: 'rest',
                url: 'api/participantes.php'
            },
            autoLoad: true
        });

        const win = Ext.create('Ext.window.Window', {
            title: `👥 Miembros del Equipo: ${equipoRec.get('nombre')}`,
            modal: true,
            layout: 'border',
            width: 800,
            height: 600,
            cls: 'dialog-window',
            items: [
                {
                    region: 'center',
                    xtype: 'grid',
                    title: 'Participantes Disponibles',
                    store: participanteStore,
                    columns: [
                        {text: 'Nombre', dataIndex: 'nombre', flex: 2},
                        {text: 'Email', dataIndex: 'email', flex: 2},
                        {
                            text: 'Tipo', 
                            dataIndex: 'tipo', 
                            width: 120,
                            renderer: function(value) {
                                const icons = {
                                    'estudiante': '🎓',
                                    'mentor': '👨‍🏫'
                                };
                                return `${icons[value] || '👤'} ${value}`;
                            }
                        }
                    ],
                    tbar: [
                        {
                            text: '➕ Agregar al Equipo',
                            handler: function() {
                                const grid = this.up('grid');
                                const selected = grid.getSelection()[0];
                                if (!selected) {
                                    Ext.Msg.alert('⚠️ Atención', 'Selecciona un participante');
                                    return;
                                }
                                
                                // Aquí se haría la llamada para agregar al equipo
                                Ext.Msg.alert('ℹ️ Info', 'Funcionalidad de gestión de miembros en desarrollo');
                            }
                        }
                    ]
                },
                {
                    region: 'east',
                    width: 300,
                    xtype: 'panel',
                    title: 'Miembros Actuales',
                    bodyPadding: 10,
                    html: `
                        <div style="text-align: center; color: #666; margin-top: 50px;">
                            <div style="font-size: 48px;">👥</div>
                            <p>Gestión de miembros<br/>en desarrollo</p>
                            <small>Próximamente podrás:<br/>
                            • Ver miembros actuales<br/>
                            • Asignar roles<br/>
                            • Remover participantes</small>
                        </div>
                    `
                }
            ],
            buttons: [
                {
                    text: '✅ Cerrar',
                    handler: function() { win.close(); }
                }
            ]
        });

        win.show();
    }

    const equipoStore = Ext.create('Ext.data.Store', {
        storeId: 'equipoStore',
        model: 'App.model.Equipo',
        proxy: {
            type: 'rest',
            url: 'api/equipos.php',
            reader: {
                type: 'json'
            }
        },
        autoLoad: true,
        autoSync: false
    });

    return Ext.create('Ext.grid.Panel', {
        id: 'equiposPanel',
        title: '👥 Gestión de Equipos',
        store: equipoStore,
        cls: 'main-grid',
        columns: [
            {
                text: 'ID',
                dataIndex: 'id',
                width: 60,
                align: 'center'
            },
            {
                text: 'Nombre del Equipo',
                dataIndex: 'nombre',
                flex: 2,
                renderer: function(value) {
                    return `<strong>👥 ${value}</strong>`;
                }
            },
            {
                text: 'Hackathon',
                dataIndex: 'hackathon_nombre',
                flex: 2,
                renderer: function(value) {
                    return value ? `🏆 ${value}` : 'Sin asignar';
                }
            },
            {
                text: 'Estado',
                dataIndex: 'estado',
                width: 130,
                renderer: function(value) {
                    const config = {
                        'formacion': { icon: '🏗️', color: '#FF9800', text: 'En Formación' },
                        'activo': { icon: '✅', color: '#4CAF50', text: 'Activo' },
                        'trabajando': { icon: '⚡', color: '#2196F3', text: 'Trabajando' },
                        'finalizado': { icon: '🏁', color: '#9C27B0', text: 'Finalizado' },
                        'disuelto': { icon: '❌', color: '#F44336', text: 'Disuelto' }
                    };
                    const cfg = config[value] || { icon: '❓', color: '#666', text: value };
                    return `<span style="color: ${cfg.color}">${cfg.icon} ${cfg.text}</span>`;
                }
            },
            {
                text: 'Fecha Formación',
                dataIndex: 'fechaFormacion',
                width: 120,
                renderer: function(value) {
                    return value ? Ext.Date.format(value, 'd/m/Y') : '';
                }
            },
            {
                text: 'Descripción',
                dataIndex: 'descripcion',
                flex: 1,
                renderer: function(value) {
                    if (!value) return '<em style="color: #999">Sin descripción</em>';
                    return value.length > 50 ? value.substring(0, 50) + '...' : value;
                }
            }
        ],
        tbar: [
            {
                text: '➕ Nuevo Equipo',
                cls: 'add-button',
                handler: () => openDialog(Ext.create('App.model.Equipo'), true)
            },
            {
                text: '✏️ Editar',
                cls: 'edit-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un equipo');
                    openDialog(rec, false);
                }
            },
            {
                text: '👥 Gestionar Miembros',
                cls: 'members-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un equipo');
                    openMembersDialog(rec);
                }
            },
            {
                text: '🗑️ Eliminar',
                cls: 'delete-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un equipo');

                    Ext.Msg.confirm('🗑️ Confirmar', '¿Eliminar este equipo?', btn => {
                        if (btn === 'yes') {
                            equipoStore.remove(rec);
                            equipoStore.sync({
                                success: () => Ext.Msg.alert('✅ Éxito', 'Equipo eliminado'),
                                failure: () => Ext.Msg.alert('❌ Error', 'No se pudo eliminar')
                            });
                        }
                    });
                }
            },
            '->',
            {
                xtype: 'combobox',
                fieldLabel: 'Estado:',
                labelWidth: 60,
                store: [
                    ['', 'Todos'],
                    ['formacion', '🏗️ En Formación'],
                    ['activo', '✅ Activos'],
                    ['trabajando', '⚡ Trabajando'],
                    ['finalizado', '🏁 Finalizados'],
                    ['disuelto', '❌ Disueltos']
                ],
                queryMode: 'local',
                editable: false,
                value: '',
                width: 200,
                listeners: {
                    change: function(combo, newValue) {
                        const store = combo.up('grid').getStore();
                        if (newValue) {
                            store.filter('estado', newValue);
                        } else {
                            store.clearFilter();
                        }
                    }
                }
            },
            {
                text: '🔄 Actualizar',
                cls: 'refresh-button',
                handler() {
                    equipoStore.reload();
                }
            }
        ]
    });
};

window.createEquiposPanel = createEquiposPanel;
