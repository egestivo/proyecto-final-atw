Ext.onReady(() => {
    // Aplicar tema oscuro
    Ext.getBody().addCls('dark-theme');
    
    // Crear los paneles principales
    const hackathonsPanel = createHackathonsPanel();
    const participantesPanel = createParticipantesPanel();
    const retosPanel = createRetosPanel();
    const equiposPanel = createEquiposPanel();
    
    const mainCard = Ext.create('Ext.panel.Panel', {
        region: 'center',
        layout: 'card',
        items: [hackathonsPanel, participantesPanel, retosPanel, equiposPanel],
        cls: 'main-card-panel'
    });

    Ext.create('Ext.container.Viewport', {
        id: 'mainViewPort',
        layout: 'border',
        cls: 'main-viewport',
        items: [{
            region: 'north',
            xtype: 'toolbar',
            cls: 'main-toolbar',
            height: 60,
            items: [
                {
                    xtype: 'component',
                    html: '<h2 style="color: #00d4aa; margin: 0; padding: 10px;">🎓 EduHack Manager</h2>',
                    flex: 1
                },
                '->',
                {
                    text: '🏛️ Hackathons',
                    scale: 'medium',
                    cls: 'nav-button',
                    handler: () => mainCard.getLayout().setActiveItem(hackathonsPanel),
                },
                {
                    text: '👥 Participantes',
                    scale: 'medium',
                    cls: 'nav-button',
                    handler: () => mainCard.getLayout().setActiveItem(participantesPanel),
                },
                {
                    text: '🎯 Retos',
                    scale: 'medium',
                    cls: 'nav-button',
                    handler: () => mainCard.getLayout().setActiveItem(retosPanel),
                },
                {
                    text: '👨‍💻 Equipos',
                    scale: 'medium',
                    cls: 'nav-button',
                    handler: () => mainCard.getLayout().setActiveItem(equiposPanel),
                }
            ]
        }, mainCard],
    });
    
    // Mostrar hackathons por defecto
    mainCard.getLayout().setActiveItem(hackathonsPanel);
});
