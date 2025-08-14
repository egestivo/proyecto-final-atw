const createRetosPanel = () => {
    
    Ext.define('App.model.Reto', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'id', type: 'int'},
            {name: 'titulo', type: 'string'},
            {name: 'descripcion', type: 'string'},
            {name: 'categoria', type: 'string'},
            {name: 'dificultad', type: 'string'},
            {name: 'tipo', type: 'string'},
            {name: 'fechaInicio', type: 'date', dateFormat: 'Y-m-d'},
            {name: 'fechaFin', type: 'date', dateFormat: 'Y-m-d'}
        ]
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create('Ext.window.Window', {
            title: isNew ? '🆕 Nuevo Reto' : '✏️ Editar Reto',
            modal: true,
            layout: 'fit',
            width: 700,
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
                        name: 'titulo', 
                        fieldLabel: 'Título del Reto', 
                        allowBlank: false,
                        emptyText: 'Nombre del reto...'
                    },
                    { 
                        xtype: 'textareafield', 
                        name: 'descripcion', 
                        fieldLabel: 'Descripción',
                        allowBlank: false,
                        height: 120,
                        emptyText: 'Describe el reto, objetivos, entregables esperados...'
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        defaults: { flex: 1, margin: '0 5 0 0' },
                        items: [
                            { 
                                xtype: 'combobox', 
                                name: 'categoria', 
                                fieldLabel: 'Categoría',
                                labelWidth: 150,
                                store: [
                                    ['frontend', '🎨 Frontend & UX'],
                                    ['backend', '⚙️ Backend & APIs'],
                                    ['mobile', '📱 Aplicaciones Móviles'],
                                    ['ia', '🤖 Inteligencia Artificial'],
                                    ['blockchain', '⛓️ Blockchain'],
                                    ['iot', '🌐 Internet de las Cosas'],
                                    ['juegos', '🎮 Desarrollo de Juegos'],
                                    ['datos', '📊 Ciencia de Datos'],
                                    ['seguridad', '🔒 Ciberseguridad'],
                                    ['fintech', '💰 Tecnología Financiera']
                                ],
                                queryMode: 'local',
                                editable: false,
                                allowBlank: false,
                                value: 'frontend'
                            },
                            { 
                                xtype: 'combobox', 
                                name: 'dificultad', 
                                fieldLabel: 'Dificultad',
                                labelWidth: 80,
                                store: [
                                    ['principiante', '🟢 Principiante'],
                                    ['intermedio', '🟡 Intermedio'],
                                    ['avanzado', '🔴 Avanzado'],
                                    ['experto', '🟣 Experto']
                                ],
                                queryMode: 'local',
                                editable: false,
                                allowBlank: false,
                                value: 'intermedio'
                            }
                        ]
                    },
                    { 
                        xtype: 'combobox', 
                        name: 'tipo', 
                        fieldLabel: 'Tipo de Reto',
                        store: [
                            ['real', '🏢 Empresarial (Cliente Real)'],
                            ['experimental', '🧪 Experimental (Innovación)']
                        ],
                        queryMode: 'local',
                        editable: false,
                        allowBlank: false,
                        value: 'experimental'
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        defaults: { flex: 1, margin: '0 5 0 0' },
                        items: [
                            { 
                                xtype: 'datefield', 
                                name: 'fechaInicio', 
                                fieldLabel: 'Fecha de Inicio',
                                labelWidth: 150,
                                format: 'Y-m-d',
                                allowBlank: false,
                                value: new Date()
                            },
                            { 
                                xtype: 'datefield', 
                                name: 'fechaFin', 
                                fieldLabel: 'Fecha Límite',
                                labelWidth: 100,
                                format: 'Y-m-d',
                                allowBlank: false,
                                value: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000) // +30 días
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
                            
                            // Validar fechas
                            const values = form.getValues();
                            if (new Date(values.fechaInicio) >= new Date(values.fechaFin)) {
                                Ext.Msg.alert('⚠️ Error', 'La fecha de fin debe ser posterior a la de inicio');
                                return;
                            }
                            
                            form.updateRecord(rec);
                            if (isNew) retoStore.add(rec);
                            retoStore.sync({
                                success: () => {
                                    Ext.Msg.alert('✅ Éxito', 'Reto guardado correctamente');
                                    this.up('window').close();
                                },
                                failure: () => {
                                    Ext.Msg.alert('❌ Error', 'No se pudo guardar el reto');
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

    const retoStore = Ext.create('Ext.data.Store', {
        storeId: 'retoStore',
        model: 'App.model.Reto',
        proxy: {
            type: 'rest',
            url: 'api/retos.php',
            reader: {
                type: 'json'
            }
        },
        autoLoad: true,
        autoSync: false
    });

    return Ext.create('Ext.grid.Panel', {
        id: 'retosPanel',
        title: '🏆 Gestión de Retos',
        store: retoStore,
        cls: 'main-grid',
        columns: [
            {
                text: 'ID',
                dataIndex: 'id',
                width: 60,
                align: 'center'
            },
            {
                text: 'Tipo',
                dataIndex: 'tipo',
                width: 80,
                renderer: function(value) {
                    const icons = {
                        'real': '🏢',
                        'experimental': '🧪'
                    };
                    return icons[value] || '❓';
                }
            },
            {
                text: 'Título',
                dataIndex: 'titulo',
                flex: 3,
                renderer: function(value) {
                    return `<strong>${value}</strong>`;
                }
            },
            {
                text: 'Categoría',
                dataIndex: 'categoria',
                width: 150,
                renderer: function(value) {
                    const icons = {
                        'frontend': '🎨',
                        'backend': '⚙️',
                        'mobile': '📱',
                        'ia': '🤖',
                        'blockchain': '⛓️',
                        'iot': '🌐',
                        'juegos': '🎮',
                        'datos': '📊',
                        'seguridad': '🔒',
                        'fintech': '💰'
                    };
                    return `${icons[value] || '💻'} ${value}`;
                }
            },
            {
                text: 'Dificultad',
                dataIndex: 'dificultad',
                width: 100,
                renderer: function(value) {
                    const config = {
                        'principiante': { icon: '🟢', color: '#4CAF50' },
                        'intermedio': { icon: '🟡', color: '#FF9800' },
                        'avanzado': { icon: '🔴', color: '#F44336' },
                        'experto': { icon: '🟣', color: '#9C27B0' }
                    };
                    const cfg = config[value] || { icon: '⚪', color: '#666' };
                    return `<span style="color: ${cfg.color}">${cfg.icon} ${value}</span>`;
                }
            },
            {
                text: 'Fechas',
                width: 180,
                renderer: function(value, metaData, record) {
                    const inicio = Ext.Date.format(record.get('fechaInicio'), 'd/m/Y');
                    const fin = Ext.Date.format(record.get('fechaFin'), 'd/m/Y');
                    const ahora = new Date();
                    const fechaFin = record.get('fechaFin');
                    
                    let estado = '';
                    if (fechaFin < ahora) {
                        estado = '<span style="color: #F44336">⏰ Vencido</span>';
                    } else {
                        const diasRestantes = Math.ceil((fechaFin - ahora) / (1000 * 60 * 60 * 24));
                        if (diasRestantes <= 7) {
                            estado = `<span style="color: #FF9800">⚠️ ${diasRestantes}d</span>`;
                        } else {
                            estado = `<span style="color: #4CAF50">✅ ${diasRestantes}d</span>`;
                        }
                    }
                    
                    return `<small>${inicio} - ${fin}<br/>${estado}</small>`;
                }
            }
        ],
        tbar: [
            {
                text: '➕ Nuevo Reto',
                cls: 'add-button',
                handler: () => openDialog(Ext.create('App.model.Reto'), true)
            },
            {
                text: '✏️ Editar',
                cls: 'edit-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un reto');
                    openDialog(rec, false);
                }
            },
            {
                text: '🗑️ Eliminar',
                cls: 'delete-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un reto');

                    Ext.Msg.confirm('🗑️ Confirmar', '¿Eliminar este reto?', btn => {
                        if (btn === 'yes') {
                            retoStore.remove(rec);
                            retoStore.sync({
                                success: () => Ext.Msg.alert('✅ Éxito', 'Reto eliminado'),
                                failure: () => Ext.Msg.alert('❌ Error', 'No se pudo eliminar')
                            });
                        }
                    });
                }
            },
            '->',
            {
                xtype: 'combobox',
                fieldLabel: 'Categoría:',
                labelWidth: 70,
                store: [
                    ['', 'Todas'],
                    ['frontend', '🎨 Frontend'],
                    ['backend', '⚙️ Backend'],
                    ['mobile', '📱 Mobile'],
                    ['ia', '🤖 IA'],
                    ['blockchain', '⛓️ Blockchain'],
                    ['iot', '🌐 IoT'],
                    ['juegos', '🎮 Juegos'],
                    ['datos', '📊 Datos'],
                    ['seguridad', '🔒 Seguridad'],
                    ['fintech', '💰 Fintech']
                ],
                queryMode: 'local',
                editable: false,
                value: '',
                width: 200,
                listeners: {
                    change: function(combo, newValue) {
                        const store = combo.up('grid').getStore();
                        if (newValue) {
                            store.filter('categoria', newValue);
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
                    retoStore.reload();
                }
            }
        ]
    });
};

window.createRetosPanel = createRetosPanel;
