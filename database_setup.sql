DROP DATABASE IF EXISTS proyecto_final;
CREATE DATABASE proyecto_final CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE proyecto_final;

-- ================================================================
-- CREAR USUARIO ADMINISTRADOR
-- ================================================================

-- Eliminar usuario si existe
DROP USER IF EXISTS 'admin'@'localhost';

-- Crear usuario administrador
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin';

-- Otorgar todos los privilegios sobre la base de datos
GRANT ALL PRIVILEGES ON proyecto_final.* TO 'admin'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- ================================================================
-- CREAR TABLAS DEL SISTEMA EDUHACK
-- ================================================================

-- Tabla de Hackathons/Eventos
CREATE TABLE hackathons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    lugar VARCHAR(200),
    estado ENUM('planificacion', 'activo', 'finalizado') DEFAULT 'planificacion',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hackathon_fecha (fecha_inicio, fecha_fin),
    INDEX idx_hackathon_estado (estado)
);

-- Tabla base Participantes (clase abstracta)
CREATE TABLE participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    tipo ENUM('estudiante', 'mentor_tecnico') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_participante_email (email),
    INDEX idx_participante_tipo (tipo)
);

-- Tabla específica para Estudiantes
CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participante_id INT NOT NULL,
    grado VARCHAR(150) NOT NULL,
    institucion VARCHAR(200) NOT NULL,
    tiempo_disponible_semanal INT NOT NULL COMMENT 'Horas disponibles por semana',
    habilidades TEXT COMMENT 'Habilidades técnicas y soft skills',
    portfolio_url VARCHAR(255),
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE,
    INDEX idx_estudiante_grado (grado),
    INDEX idx_estudiante_institucion (institucion)
);

-- Tabla específica para Mentores Técnicos
CREATE TABLE mentores_tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participante_id INT NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    experiencia_anos INT NOT NULL,
    disponibilidad_horaria VARCHAR(100) NOT NULL,
    certificaciones TEXT,
    linkedin_url VARCHAR(255),
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE,
    INDEX idx_mentor_especialidad (especialidad),
    INDEX idx_mentor_experiencia (experiencia_anos)
);

-- Tabla base Retos Solucionables (clase abstracta)
CREATE TABLE retos_solucionables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    dificultad ENUM('basico', 'intermedio', 'avanzado') NOT NULL,
    tecnologias_requeridas TEXT,
    tipo ENUM('reto_real', 'reto_experimental') NOT NULL,
    estado ENUM('borrador', 'publicado', 'en_desarrollo', 'completado') DEFAULT 'borrador',
    hackathon_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hackathon_id) REFERENCES hackathons(id) ON DELETE CASCADE,
    INDEX idx_reto_tipo (tipo),
    INDEX idx_reto_dificultad (dificultad),
    INDEX idx_reto_hackathon (hackathon_id)
);

-- Tabla específica para Retos Reales
CREATE TABLE retos_reales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reto_id INT NOT NULL,
    entidad_colaboradora VARCHAR(200) NOT NULL,
    contacto_entidad VARCHAR(150),
    impacto_esperado TEXT,
    recursos_disponibles TEXT,
    FOREIGN KEY (reto_id) REFERENCES retos_solucionables(id) ON DELETE CASCADE,
    INDEX idx_reto_real_entidad (entidad_colaboradora)
);

-- Tabla específica para Retos Experimentales
CREATE TABLE retos_experimentales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reto_id INT NOT NULL,
    enfoque_pedagogico ENUM('STEM', 'STEAM', 'ABP', 'Design_Thinking', 'Otro') NOT NULL,
    objetivos_aprendizaje TEXT NOT NULL,
    docente_responsable VARCHAR(150),
    recursos_educativos TEXT,
    FOREIGN KEY (reto_id) REFERENCES retos_solucionables(id) ON DELETE CASCADE,
    INDEX idx_reto_experimental_enfoque (enfoque_pedagogico)
);

-- Tabla de Equipos
CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    hackathon_id INT NOT NULL,
    fecha_formacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('formandose', 'completo', 'activo', 'finalizado') DEFAULT 'formandose',
    max_integrantes INT DEFAULT 6,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hackathon_id) REFERENCES hackathons(id) ON DELETE CASCADE,
    INDEX idx_equipo_hackathon (hackathon_id),
    INDEX idx_equipo_estado (estado)
);

-- Tabla de relación Equipo-Participante
CREATE TABLE equipo_participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipo_id INT NOT NULL,
    participante_id INT NOT NULL,
    rol_en_equipo ENUM('lider', 'desarrollador', 'disenador', 'analista', 'mentor') NOT NULL,
    fecha_union TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE,
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_equipo_participante (equipo_id, participante_id),
    INDEX idx_equipo_participante_rol (rol_en_equipo)
);

-- Tabla de relación muchos a muchos: Equipo-Reto (UN EQUIPO PUEDE TRABAJAR EN MÚLTIPLES RETOS)
CREATE TABLE equipo_retos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipo_id INT NOT NULL,
    reto_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progreso INT DEFAULT 0 COMMENT 'Porcentaje de avance 0-100',
    estado ENUM('asignado', 'en_desarrollo', 'pausado', 'completado', 'abandonado') DEFAULT 'asignado',
    solucion_propuesta TEXT,
    calificacion DECIMAL(3,2) DEFAULT NULL COMMENT 'Calificación de 0 a 10',
    comentarios_evaluacion TEXT,
    FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE,
    FOREIGN KEY (reto_id) REFERENCES retos_solucionables(id) ON DELETE CASCADE,
    UNIQUE KEY unique_equipo_reto (equipo_id, reto_id),
    INDEX idx_equipo_reto_estado (estado),
    INDEX idx_equipo_reto_progreso (progreso)
);

-- ================================================================
-- DATOS DE PRUEBA - ESCENARIO "EDUHACK SOSTENIBILIDAD 2025"
-- ================================================================

-- Insertar el hackathon principal
INSERT INTO hackathons (nombre, descripcion, fecha_inicio, fecha_fin, lugar, estado) VALUES
('EduHack Sostenibilidad 2025', 
 'Hackathon de 48 horas para resolver problemas ambientales usando tecnología. Organizado por una red de escuelas y ONGs.',
 '2025-04-15', '2025-04-16', 
 'Plataforma online + sedes presenciales', 
 'activo');

-- Insertar participantes base
INSERT INTO participantes (nombre, email, telefono, tipo) VALUES
-- Estudiantes
('Ana García', 'ana.garcia@estudiante.edu', '+52-555-1001', 'estudiante'),
('Carlos López', 'carlos.lopez@estudiante.edu', '+52-555-1002', 'estudiante'),
('María Rodríguez', 'maria.rodriguez@estudiante.edu', '+52-555-1003', 'estudiante'),
('David Chen', 'david.chen@estudiante.edu', '+52-555-1004', 'estudiante'),
('Sophie Martin', 'sophie.martin@estudiante.edu', '+52-555-1005', 'estudiante'),
('Ahmed Hassan', 'ahmed.hassan@estudiante.edu', '+52-555-1006', 'estudiante'),
-- Mentores Técnicos
('Dr. Laura Vega', 'laura.vega@mentor.tech', '+52-555-2001', 'mentor_tecnico'),
('Ing. Roberto Silva', 'roberto.silva@mentor.tech', '+52-555-2002', 'mentor_tecnico'),
('Dra. Elena Kostova', 'elena.kostova@mentor.tech', '+52-555-2003', 'mentor_tecnico'),
('Prof. Juan Martínez', 'juan.martinez@mentor.tech', '+52-555-2004', 'mentor_tecnico');

-- Insertar datos específicos de estudiantes
INSERT INTO estudiantes (participante_id, grado, institucion, tiempo_disponible_semanal, habilidades, portfolio_url) VALUES
(1, 'Licenciatura en Ingeniería de Software - 6to semestre', 'TEC de Monterrey', 25, 'JavaScript, React, Node.js, UI/UX Design', 'https://github.com/anagarcia'),
(2, 'Ingeniería en Ciencias Ambientales - 4to semestre', 'UNAM', 20, 'Python, Data Science, GIS, Sostenibilidad', 'https://github.com/carloslopez'),
(3, 'Diseño Industrial - 8vo semestre', 'UAM', 30, 'Design Thinking, Prototipado, Arduino, 3D Modeling', 'https://behance.net/mariarodriguez'),
(4, 'Computer Science - 2do año', 'MIT (intercambio)', 35, 'Machine Learning, Python, TensorFlow, Blockchain', 'https://github.com/davidchen'),
(5, 'Ingeniería Biomédica - 5to semestre', 'Universidad Politécnica de Madrid', 28, 'IoT, Sensores, Salud Digital, C++', 'https://github.com/sophiemartin'),
(6, 'Ingeniería en Energías Renovables - 7mo semestre', 'Universidad del Cairo', 22, 'Sistemas Energéticos, Arduino, Simulación', 'https://github.com/ahmedhassan');

-- Insertar datos específicos de mentores técnicos
INSERT INTO mentores_tecnicos (participante_id, especialidad, experiencia_anos, disponibilidad_horaria, certificaciones, linkedin_url) VALUES
(7, 'Desarrollo de Apps Móviles Sostenibles', 12, 'Fines de semana 9:00-17:00', 'Google Developer Expert, Apple Developer', 'https://linkedin.com/in/lauravega'),
(8, 'IoT y Smart Cities', 8, 'Viernes 14:00-18:00, Sábados 10:00-14:00', 'AWS IoT Specialist, Cisco IoT Expert', 'https://linkedin.com/in/robertosilva'),
(9, 'Inteligencia Artificial para Sostenibilidad', 15, 'Sábados y Domingos 10:00-16:00', 'TensorFlow Certified, Microsoft AI Engineer', 'https://linkedin.com/in/elenakostova'),
(10, 'Diseño de Experiencia y Educación Digital', 10, 'Disponibilidad flexible durante el evento', 'Design Thinking Coach, Google UX Certificate', 'https://linkedin.com/in/juanmartinez');

-- Insertar retos solucionables base
INSERT INTO retos_solucionables (titulo, descripcion, dificultad, tecnologias_requeridas, tipo, estado, hackathon_id) VALUES
-- Retos Reales
('App para Reducir Desperdicio Alimentario', 
 'Desarrollar una aplicación móvil que conecte restaurantes con organizaciones benéficas para redistribuir alimentos no vendidos antes de que caduquen.',
 'intermedio', 
 'React Native, Node.js, APIs de geolocalización, Base de datos', 
 'reto_real', 'publicado', 1),

('Sistema de Monitoreo de Calidad del Aire Urbano',
 'Crear una red de sensores IoT de bajo costo para medir la calidad del aire en tiempo real y generar alertas ciudadanas.',
 'avanzado',
 'Arduino/Raspberry Pi, Sensores ambientales, APIs web, Dashboard de visualización',
 'reto_real', 'publicado', 1),

('Plataforma de Intercambio de Objetos Reutilizables',
 'Diseñar una plataforma web donde estudiantes universitarios puedan intercambiar objetos que ya no usan, promoviendo la economía circular.',
 'basico',
 'HTML/CSS, JavaScript, Base de datos, API REST',
 'reto_real', 'publicado', 1),

-- Retos Experimentales
('Simulador de Ecosistemas con IA Generativa',
 'Crear un simulador educativo que use IA para mostrar cómo las acciones humanas afectan diferentes ecosistemas a lo largo del tiempo.',
 'avanzado',
 'Python, Machine Learning, Simulación, Interfaz gráfica',
 'reto_experimental', 'publicado', 1),

('Chatbot Educativo sobre Cambio Climático',
 'Desarrollar un chatbot conversacional que enseñe a estudiantes de secundaria sobre el cambio climático de manera interactiva.',
 'intermedio',
 'NLP, Chatbot frameworks, Base de conocimiento, Interfaz web',
 'reto_experimental', 'publicado', 1);

-- Insertar detalles de retos reales
INSERT INTO retos_reales (reto_id, entidad_colaboradora, contacto_entidad, impacto_esperado, recursos_disponibles) VALUES
(1, 'Fundación Banco de Alimentos de México', 'colaboracion@bamx.org.mx', 
   'Reducir 30% el desperdicio alimentario en 5 ciudades piloto', 
   'Acceso a datos reales, mentores de la industria, infraestructura de testing'),
(2, 'Greenpeace México y Secretaría de Medio Ambiente CDMX', 'tech@greenpeace.org.mx',
   'Instalar 100 sensores piloto en la Ciudad de México',
   'Hardware de sensores, acceso a datos gubernamentales, asesoría técnica'),
(3, 'Red de Universidades Sustentables de México', 'innovacion@redsustentable.mx',
   'Implementar en 10 universidades como programa piloto',
   'Acceso a comunidades estudiantiles, infraestructura web, feedback de usuarios');

-- Insertar detalles de retos experimentales  
INSERT INTO retos_experimentales (reto_id, enfoque_pedagogico, objetivos_aprendizaje, docente_responsable, recursos_educativos) VALUES
(4, 'STEAM', 
   'Comprender sistemas complejos, modelado matemático, pensamiento sistémico, programación aplicada',
   'Dra. Patricia Morales - Instituto de Ecología UNAM',
   'Datasets ambientales, librerías de simulación, tutoriales de modelado'),
(5, 'ABP',
   'Comunicación científica, procesamiento de lenguaje natural, diseño centrado en el usuario',
   'Prof. Miguel Santos - Facultad de Ciencias UNAM', 
   'Corpus de textos científicos, frameworks de NLP, guías de conversación educativa');

-- Crear equipos de ejemplo
INSERT INTO equipos (nombre, descripcion, hackathon_id, estado, max_integrantes) VALUES
('EcoTech Warriors', 'Equipo multidisciplinario enfocado en soluciones tecnológicas para sostenibilidad urbana', 1, 'completo', 5),
('Green AI Innovators', 'Especialistas en inteligencia artificial aplicada a problemas ambientales', 1, 'completo', 4),
('Circular Economy Builders', 'Desarrolladores y diseñadores comprometidos con la economía circular', 1, 'activo', 6);

-- Asignar participantes a equipos
INSERT INTO equipo_participantes (equipo_id, participante_id, rol_en_equipo) VALUES
-- Equipo 1: EcoTech Warriors
(1, 1, 'lider'),           -- Ana García (Desarrollo)
(1, 2, 'analista'),        -- Carlos López (Ciencias Ambientales) 
(1, 3, 'disenador'),       -- María Rodríguez (Diseño)
(1, 7, 'mentor'),          -- Dr. Laura Vega (Mentor Apps)
(1, 8, 'mentor'),          -- Ing. Roberto Silva (Mentor IoT)

-- Equipo 2: Green AI Innovators  
(2, 4, 'lider'),           -- David Chen (ML)
(2, 5, 'desarrollador'),   -- Sophie Martin (IoT/Biomédica)
(2, 9, 'mentor'),          -- Dra. Elena Kostova (Mentor IA)
(2, 10, 'mentor'),         -- Prof. Juan Martínez (Mentor UX)

-- Equipo 3: Circular Economy Builders
(3, 6, 'desarrollador'),   -- Ahmed Hassan (Energías)
(3, 1, 'desarrollador');   -- Ana García también participa aquí (multireto)

-- Asignar equipos a retos (relación muchos a muchos)
INSERT INTO equipo_retos (equipo_id, reto_id, estado, progreso, solucion_propuesta) VALUES
-- Equipo 1 trabajando en múltiples retos
(1, 1, 'en_desarrollo', 45, 'App móvil "FoodBridge" con geolocalización y sistema de notificaciones push'),
(1, 2, 'en_desarrollo', 30, 'Red de sensores Arduino con conectividad LoRaWAN y dashboard web'),

-- Equipo 2 enfocado en IA
(2, 4, 'en_desarrollo', 60, 'Simulador "EcoSystem AI" usando redes neuronales y visualización 3D'),
(2, 5, 'en_desarrollo', 25, 'Chatbot "ClimateBot" con procesamiento de lenguaje natural'),

-- Equipo 3 en economía circular
(3, 3, 'en_desarrollo', 70, 'Plataforma web "CampusCircular" con sistema de matching inteligente');

-- ================================================================
-- VERIFICACIÓN DE LA INSTALACIÓN
-- ================================================================

-- Mostrar información de las tablas creadas
SELECT 'VERIFICACIÓN DE INSTALACIÓN EDUHACK' as status;
SELECT COUNT(*) as total_hackathons FROM hackathons;
SELECT COUNT(*) as total_participantes FROM participantes;
SELECT COUNT(*) as total_estudiantes FROM estudiantes;
SELECT COUNT(*) as total_mentores FROM mentores_tecnicos;
SELECT COUNT(*) as total_retos FROM retos_solucionables;
SELECT COUNT(*) as total_equipos FROM equipos;
SELECT COUNT(*) as total_asignaciones FROM equipo_retos;

-- Mostrar estructura de tablas
SHOW TABLES;

-- Consulta de ejemplo: Equipos y sus retos asignados
SELECT 
    e.nombre as equipo,
    rs.titulo as reto,
    rs.tipo as tipo_reto,
    er.estado as estado_desarrollo,
    er.progreso as porcentaje_avance
FROM equipos e
JOIN equipo_retos er ON e.id = er.equipo_id
JOIN retos_solucionables rs ON er.reto_id = rs.id
ORDER BY e.nombre, rs.titulo;

SELECT 'BASE DE DATOS EDUHACK CONFIGURADA EXITOSAMENTE' as result;
