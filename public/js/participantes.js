const createParticipantesPanel = () => {
    
    Ext.define('App.model.Participante', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'id', type: 'int'},
            {name: 'nombre', type: 'string'},
            {name: 'email', type: 'string'},
            {name: 'telefono', type: 'string'},
            {name: 'tipo', type: 'string'},
            // Campos específicos de estudiante
            {name: 'grado', type: 'string'},
            {name: 'institucion', type: 'string'},
            {name: 'tiempoDisponibleSemanal', type: 'string'},
            // Campos específicos de mentor
            {name: 'especialidad', type: 'string'},
            {name: 'experienciaAnos', type: 'string'},
            {name: 'disponibilidadHoraria', type: 'string'}
        ]
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create('Ext.window.Window', {
            title: isNew ? '🆕 Nuevo Participante' : '✏️ Editar Participante',
            modal: true,
            layout: 'fit',
            width: 650,
            cls: 'dialog-window',
            items: [{
                xtype: 'form',
                bodyPadding: 20,
                cls: 'dialog-form',
                defaults: {
                    anchor: '100%',
                    labelWidth: 160
                },
                items: [
                    { xtype: 'hidden', name: 'id'},
                    { 
                        xtype: 'textfield', 
                        name: 'nombre', 
                        fieldLabel: 'Nombre', 
                        allowBlank: false,
                        emptyText: 'Nombre completo'
                    },
                    { 
                        xtype: 'textfield', 
                        name: 'email', 
                        fieldLabel: 'Email', 
                        allowBlank: false,
                        vtype: 'email',
                        emptyText: 'correo@ejemplo.com'
                    },
                    { 
                        xtype: 'textfield', 
                        name: 'telefono', 
                        fieldLabel: 'Teléfono',
                        emptyText: '555-1234'
                    },
                    { 
                        xtype: 'combobox', 
                        name: 'tipo', 
                        fieldLabel: 'Tipo',
                        store: [
                            ['estudiante', '🎓 Estudiante'],
                            ['mentor', '👨‍🏫 Mentor Técnico']
                        ],
                        queryMode: 'local',
                        editable: false,
                        allowBlank: false,
                        value: 'estudiante',
                        listeners: {
                            change: function(combo, newValue) {
                                const form = combo.up('form');
                                const estudianteFields = form.query('[name=grado], [name=institucion], [name=tiempoDisponibleSemanal]');
                                const mentorFields = form.query('[name=especialidad], [name=experienciaAnos], [name=disponibilidadHoraria]');
                                
                                if (newValue === 'estudiante') {
                                    estudianteFields.forEach(field => field.show());
                                    mentorFields.forEach(field => field.hide());
                                } else {
                                    estudianteFields.forEach(field => field.hide());
                                    mentorFields.forEach(field => field.show());
                                }
                            }
                        }
                    },
                    // Campos específicos de estudiante
                    { 
                        xtype: 'textfield', 
                        name: 'grado', 
                        fieldLabel: 'Grado/Carrera',
                        emptyText: 'Ej: Ingeniería en Sistemas'
                    },
                    { 
                        xtype: 'textfield', 
                        name: 'institucion', 
                        fieldLabel: 'Institución',
                        emptyText: 'Ej: Universidad XYZ'
                    },
                    { 
                        xtype: 'numberfield', 
                        name: 'tiempoDisponibleSemanal', 
                        fieldLabel: 'Horas disponibles/semana',
                        minValue: 0,
                        maxValue: 168,
                        value: 20
                    },
                    // Campos específicos de mentor
                    { 
                        xtype: 'textfield', 
                        name: 'especialidad', 
                        fieldLabel: 'Especialidad',
                        emptyText: 'Ej: Desarrollo Full Stack',
                        hidden: true
                    },
                    { 
                        xtype: 'numberfield', 
                        name: 'experienciaAnos', 
                        fieldLabel: 'Años de experiencia',
                        minValue: 0,
                        value: 5,
                        hidden: true
                    },
                    { 
                        xtype: 'textfield', 
                        name: 'disponibilidadHoraria', 
                        fieldLabel: 'Disponibilidad horaria',
                        emptyText: 'Ej: Tardes y fines de semana',
                        hidden: true
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
                            if (isNew) participanteStore.add(rec);
                            participanteStore.sync({
                                success: () => {
                                    Ext.Msg.alert('✅ Éxito', 'Participante guardado correctamente');
                                    this.up('window').close();
                                },
                                failure: () => {
                                    Ext.Msg.alert('❌ Error', 'No se pudo guardar el participante');
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
        
        const form = win.down('form');
        form.loadRecord(rec);
        
        // Mostrar/ocultar campos según el tipo
        const tipo = rec.get('tipo') || 'estudiante';
        const estudianteFields = form.query('[name=grado], [name=institucion], [name=tiempoDisponibleSemanal]');
        const mentorFields = form.query('[name=especialidad], [name=experienciaAnos], [name=disponibilidadHoraria]');
        
        if (tipo === 'estudiante') {
            estudianteFields.forEach(field => field.show());
            mentorFields.forEach(field => field.hide());
        } else {
            estudianteFields.forEach(field => field.hide());
            mentorFields.forEach(field => field.show());
        }
        
        win.show();
    }

    const participanteStore = Ext.create('Ext.data.Store', {
        storeId: 'participanteStore',
        model: 'App.model.Participante',
        proxy: {
            type: 'rest',
            url: 'api/participantes.php',
            reader: {
                type: 'json'
            }
        },
        autoLoad: true,
        autoSync: false
    });

    return Ext.create('Ext.grid.Panel', {
        id: 'participantesPanel',
        title: '👥 Gestión de Participantes',
        store: participanteStore,
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
                width: 100,
                renderer: function(value) {
                    const icons = {
                        'estudiante': '🎓',
                        'mentor': '👨‍🏫'
                    };
                    return `${icons[value] || '👤'} ${value}`;
                }
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
                text: 'Email',
                dataIndex: 'email',
                flex: 2
            },
            {
                text: 'Teléfono',
                dataIndex: 'telefono',
                width: 120
            },
            {
                text: 'Info Adicional',
                width: 200,
                renderer: function(value, metaData, record) {
                    const tipo = record.get('tipo');
                    if (tipo === 'estudiante') {
                        const grado = record.get('grado');
                        const institucion = record.get('institucion');
                        return `<small>${grado}<br/>${institucion}</small>`;
                    } else {
                        const especialidad = record.get('especialidad');
                        const experiencia = record.get('experienciaAnos');
                        return `<small>${especialidad}<br/>${experiencia} años exp.</small>`;
                    }
                }
            }
        ],
        tbar: [
            {
                text: '➕ Nuevo Participante',
                cls: 'add-button',
                handler: () => openDialog(Ext.create('App.model.Participante'), true)
            },
            {
                text: '✏️ Editar',
                cls: 'edit-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un participante');
                    openDialog(rec, false);
                }
            },
            {
                text: '🗑️ Eliminar',
                cls: 'delete-button',
                handler() {
                    const rec = this.up('grid').getSelection()[0];
                    if (!rec) return Ext.Msg.alert('⚠️ Atención', 'Selecciona un participante');

                    Ext.Msg.confirm('🗑️ Confirmar', '¿Eliminar este participante?', btn => {
                        if (btn === 'yes') {
                            participanteStore.remove(rec);
                            participanteStore.sync({
                                success: () => Ext.Msg.alert('✅ Éxito', 'Participante eliminado'),
                                failure: () => Ext.Msg.alert('❌ Error', 'No se pudo eliminar')
                            });
                        }
                    });
                }
            },
            '->',
            {
                xtype: 'combobox',
                fieldLabel: 'Filtrar por tipo:',
                labelWidth: 100,
                store: [
                    ['', 'Todos'],
                    ['estudiante', '🎓 Estudiantes'],
                    ['mentor', '👨‍🏫 Mentores']
                ],
                queryMode: 'local',
                editable: false,
                value: '',
                listeners: {
                    change: function(combo, newValue) {
                        const store = combo.up('grid').getStore();
                        if (newValue) {
                            store.filter('tipo', newValue);
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
                    participanteStore.reload();
                }
            }
        ]
    });
};

window.createParticipantesPanel = createParticipantesPanel;
