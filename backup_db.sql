-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: proyecto_final
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `equipo_participantes`
--

DROP TABLE IF EXISTS `equipo_participantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipo_participantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `equipo_id` int NOT NULL,
  `participante_id` int NOT NULL,
  `rol_en_equipo` enum('lider','desarrollador','disenador','analista','mentor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_union` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_equipo_participante` (`equipo_id`,`participante_id`),
  KEY `participante_id` (`participante_id`),
  KEY `idx_equipo_participante_rol` (`rol_en_equipo`),
  CONSTRAINT `equipo_participantes_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipo_participantes_ibfk_2` FOREIGN KEY (`participante_id`) REFERENCES `participantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipo_participantes`
--

LOCK TABLES `equipo_participantes` WRITE;
/*!40000 ALTER TABLE `equipo_participantes` DISABLE KEYS */;
INSERT INTO `equipo_participantes` VALUES (1,1,1,'lider','2025-08-14 22:27:44',1),(2,1,2,'analista','2025-08-14 22:27:44',1),(3,1,3,'disenador','2025-08-14 22:27:44',1),(4,1,7,'mentor','2025-08-14 22:27:44',1),(5,1,8,'mentor','2025-08-14 22:27:44',1),(6,2,4,'lider','2025-08-14 22:27:44',1),(7,2,5,'desarrollador','2025-08-14 22:27:44',1),(8,2,9,'mentor','2025-08-14 22:27:44',1),(9,2,10,'mentor','2025-08-14 22:27:44',1),(10,3,6,'desarrollador','2025-08-14 22:27:44',1),(11,3,1,'desarrollador','2025-08-14 22:27:44',1);
/*!40000 ALTER TABLE `equipo_participantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipo_retos`
--

DROP TABLE IF EXISTS `equipo_retos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipo_retos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `equipo_id` int NOT NULL,
  `reto_id` int NOT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `progreso` int DEFAULT '0' COMMENT 'Porcentaje de avance 0-100',
  `estado` enum('asignado','en_desarrollo','pausado','completado','abandonado') COLLATE utf8mb4_unicode_ci DEFAULT 'asignado',
  `solucion_propuesta` text COLLATE utf8mb4_unicode_ci,
  `calificacion` decimal(3,2) DEFAULT NULL COMMENT 'Calificación de 0 a 10',
  `comentarios_evaluacion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_equipo_reto` (`equipo_id`,`reto_id`),
  KEY `reto_id` (`reto_id`),
  KEY `idx_equipo_reto_estado` (`estado`),
  KEY `idx_equipo_reto_progreso` (`progreso`),
  CONSTRAINT `equipo_retos_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipo_retos_ibfk_2` FOREIGN KEY (`reto_id`) REFERENCES `retos_solucionables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipo_retos`
--

LOCK TABLES `equipo_retos` WRITE;
/*!40000 ALTER TABLE `equipo_retos` DISABLE KEYS */;
INSERT INTO `equipo_retos` VALUES (1,1,1,'2025-08-14 22:27:44',45,'en_desarrollo','App móvil \"FoodBridge\" con geolocalización y sistema de notificaciones push',NULL,NULL),(2,1,2,'2025-08-14 22:27:44',30,'en_desarrollo','Red de sensores Arduino con conectividad LoRaWAN y dashboard web',NULL,NULL),(3,2,4,'2025-08-14 22:27:44',60,'en_desarrollo','Simulador \"EcoSystem AI\" usando redes neuronales y visualización 3D',NULL,NULL),(4,2,5,'2025-08-14 22:27:44',25,'en_desarrollo','Chatbot \"ClimateBot\" con procesamiento de lenguaje natural',NULL,NULL),(5,3,3,'2025-08-14 22:27:44',70,'en_desarrollo','Plataforma web \"CampusCircular\" con sistema de matching inteligente',NULL,NULL);
/*!40000 ALTER TABLE `equipo_retos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipos`
--

DROP TABLE IF EXISTS `equipos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `hackathon_id` int NOT NULL,
  `fecha_formacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('formandose','completo','activo','finalizado') COLLATE utf8mb4_unicode_ci DEFAULT 'formandose',
  `max_integrantes` int DEFAULT '6',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_equipo_hackathon` (`hackathon_id`),
  KEY `idx_equipo_estado` (`estado`),
  CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipos`
--

LOCK TABLES `equipos` WRITE;
/*!40000 ALTER TABLE `equipos` DISABLE KEYS */;
INSERT INTO `equipos` VALUES (1,'EcoTech Warriors','Equipo multidisciplinario enfocado en soluciones tecnológicas para sostenibilidad urbana',1,'2025-08-14 22:27:44','completo',5,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(2,'Green AI Innovators','Especialistas en inteligencia artificial aplicada a problemas ambientales',1,'2025-08-14 22:27:44','completo',4,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(3,'Circular Economy Builders','Desarrolladores y diseñadores comprometidos con la economía circular',1,'2025-08-14 22:27:44','activo',6,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(4,'EcoHackers','',1,'2025-08-14 23:45:46','formandose',6,'2025-08-14 23:45:46','2025-08-14 23:45:46');
/*!40000 ALTER TABLE `equipos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estudiantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `participante_id` int NOT NULL,
  `grado` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institucion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tiempo_disponible_semanal` int NOT NULL COMMENT 'Horas disponibles por semana',
  `habilidades` text COLLATE utf8mb4_unicode_ci COMMENT 'Habilidades técnicas y soft skills',
  `portfolio_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `participante_id` (`participante_id`),
  KEY `idx_estudiante_grado` (`grado`),
  KEY `idx_estudiante_institucion` (`institucion`),
  CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`participante_id`) REFERENCES `participantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiantes`
--

LOCK TABLES `estudiantes` WRITE;
/*!40000 ALTER TABLE `estudiantes` DISABLE KEYS */;
INSERT INTO `estudiantes` VALUES (1,1,'Licenciatura en Ingeniería de Software - 6to semestre','TEC de Monterrey',25,'JavaScript, React, Node.js, UI/UX Design','https://github.com/anagarcia'),(2,2,'Ingeniería en Ciencias Ambientales - 4to semestre','UNAM',20,'Python, Data Science, GIS, Sostenibilidad','https://github.com/carloslopez'),(3,3,'Diseño Industrial - 8vo semestre','UAM',30,'Design Thinking, Prototipado, Arduino, 3D Modeling','https://behance.net/mariarodriguez'),(4,4,'Computer Science - 2do año','MIT (intercambio)',35,'Machine Learning, Python, TensorFlow, Blockchain','https://github.com/davidchen'),(5,5,'Ingeniería Biomédica - 5to semestre','Universidad Politécnica de Madrid',28,'IoT, Sensores, Salud Digital, C++','https://github.com/sophiemartin'),(6,6,'Ingeniería en Energías Renovables - 7mo semestre','Universidad del Cairo',22,'Sistemas Energéticos, Arduino, Simulación','https://github.com/ahmedhassan');
/*!40000 ALTER TABLE `estudiantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hackathons`
--

DROP TABLE IF EXISTS `hackathons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hackathons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `lugar` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('planificacion','activo','finalizado') COLLATE utf8mb4_unicode_ci DEFAULT 'planificacion',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hackathon_fecha` (`fecha_inicio`,`fecha_fin`),
  KEY `idx_hackathon_estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hackathons`
--

LOCK TABLES `hackathons` WRITE;
/*!40000 ALTER TABLE `hackathons` DISABLE KEYS */;
INSERT INTO `hackathons` VALUES (1,'EduHack Sostenibilidad 2025','Hackathon de 48 horas para resolver problemas ambientales usando tecnología. Organizado por una red de escuelas y ONGs.','2025-04-15','2025-04-16','Plataforma online + sedes presenciales','activo','2025-08-14 22:27:44','2025-08-14 22:27:44'),(2,'EduHack 2025','Hackathon educativo enfocado en innovación pedagógica','2025-09-15','2025-09-17','Universidad Tecnológica Nacional','planificacion','2025-08-14 23:41:42','2025-08-14 23:41:42');
/*!40000 ALTER TABLE `hackathons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentores_tecnicos`
--

DROP TABLE IF EXISTS `mentores_tecnicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentores_tecnicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `participante_id` int NOT NULL,
  `especialidad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `experiencia_anos` int NOT NULL,
  `disponibilidad_horaria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificaciones` text COLLATE utf8mb4_unicode_ci,
  `linkedin_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `participante_id` (`participante_id`),
  KEY `idx_mentor_especialidad` (`especialidad`),
  KEY `idx_mentor_experiencia` (`experiencia_anos`),
  CONSTRAINT `mentores_tecnicos_ibfk_1` FOREIGN KEY (`participante_id`) REFERENCES `participantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentores_tecnicos`
--

LOCK TABLES `mentores_tecnicos` WRITE;
/*!40000 ALTER TABLE `mentores_tecnicos` DISABLE KEYS */;
INSERT INTO `mentores_tecnicos` VALUES (1,7,'Desarrollo de Apps Móviles Sostenibles',12,'Fines de semana 9:00-17:00','Google Developer Expert, Apple Developer','https://linkedin.com/in/lauravega'),(2,8,'IoT y Smart Cities',8,'Viernes 14:00-18:00, Sábados 10:00-14:00','AWS IoT Specialist, Cisco IoT Expert','https://linkedin.com/in/robertosilva'),(3,9,'Inteligencia Artificial para Sostenibilidad',15,'Sábados y Domingos 10:00-16:00','TensorFlow Certified, Microsoft AI Engineer','https://linkedin.com/in/elenakostova'),(4,10,'Diseño de Experiencia y Educación Digital',10,'Disponibilidad flexible durante el evento','Design Thinking Coach, Google UX Certificate','https://linkedin.com/in/juanmartinez'),(5,12,'Desarrollo Full Stack',8,'Tardes y fines de semana',NULL,NULL);
/*!40000 ALTER TABLE `mentores_tecnicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantes`
--

DROP TABLE IF EXISTS `participantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('estudiante','mentor_tecnico') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_participante_email` (`email`),
  KEY `idx_participante_tipo` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantes`
--

LOCK TABLES `participantes` WRITE;
/*!40000 ALTER TABLE `participantes` DISABLE KEYS */;
INSERT INTO `participantes` VALUES (1,'Ana García','ana.garcia@estudiante.edu','+52-555-1001','estudiante','2025-08-14 22:27:44','2025-08-14 22:27:44'),(2,'Carlos López','carlos.lopez@estudiante.edu','+52-555-1002','estudiante','2025-08-14 22:27:44','2025-08-14 22:27:44'),(3,'María Rodríguez','maria.rodriguez@estudiante.edu','+52-555-1003','estudiante','2025-08-14 22:27:44','2025-08-14 22:27:44'),(4,'David Chen','david.chen@estudiante.edu','+52-555-1004','estudiante','2025-08-14 22:27:44','2025-08-14 22:27:44'),(5,'Sophie Martin','sophie.martin@estudiante.edu','+52-555-1005','estudiante','2025-08-14 22:27:44','2025-08-14 22:27:44'),(6,'Ahmed Hassan','ahmed.hassan@estudiante.edu','+52-555-1006','estudiante','2025-08-14 22:27:44','2025-08-14 22:27:44'),(7,'Dr. Laura Vega','laura.vega@mentor.tech','+52-555-2001','mentor_tecnico','2025-08-14 22:27:44','2025-08-14 22:27:44'),(8,'Ing. Roberto Silva','roberto.silva@mentor.tech','+52-555-2002','mentor_tecnico','2025-08-14 22:27:44','2025-08-14 22:27:44'),(9,'Dra. Elena Kostova','elena.kostova@mentor.tech','+52-555-2003','mentor_tecnico','2025-08-14 22:27:44','2025-08-14 22:27:44'),(10,'Prof. Juan Martínez','juan.martinez@mentor.tech','+52-555-2004','mentor_tecnico','2025-08-14 22:27:44','2025-08-14 22:27:44'),(12,'Carlos Rodríguez Silva','carlos.rodriguez@tech.com','555-5678','mentor_tecnico','2025-08-14 23:28:38','2025-08-14 23:28:38');
/*!40000 ALTER TABLE `participantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retos_experimentales`
--

DROP TABLE IF EXISTS `retos_experimentales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retos_experimentales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reto_id` int NOT NULL,
  `enfoque_pedagogico` enum('STEM','STEAM','ABP','Design_Thinking','Otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivos_aprendizaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `docente_responsable` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recursos_educativos` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `reto_id` (`reto_id`),
  KEY `idx_reto_experimental_enfoque` (`enfoque_pedagogico`),
  CONSTRAINT `retos_experimentales_ibfk_1` FOREIGN KEY (`reto_id`) REFERENCES `retos_solucionables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retos_experimentales`
--

LOCK TABLES `retos_experimentales` WRITE;
/*!40000 ALTER TABLE `retos_experimentales` DISABLE KEYS */;
INSERT INTO `retos_experimentales` VALUES (1,4,'STEAM','Comprender sistemas complejos, modelado matemático, pensamiento sistémico, programación aplicada','Dra. Patricia Morales - Instituto de Ecología UNAM','Datasets ambientales, librerías de simulación, tutoriales de modelado'),(2,5,'ABP','Comunicación científica, procesamiento de lenguaje natural, diseño centrado en el usuario','Prof. Miguel Santos - Facultad de Ciencias UNAM','Corpus de textos científicos, frameworks de NLP, guías de conversación educativa');
/*!40000 ALTER TABLE `retos_experimentales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retos_reales`
--

DROP TABLE IF EXISTS `retos_reales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retos_reales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reto_id` int NOT NULL,
  `entidad_colaboradora` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contacto_entidad` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `impacto_esperado` text COLLATE utf8mb4_unicode_ci,
  `recursos_disponibles` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `reto_id` (`reto_id`),
  KEY `idx_reto_real_entidad` (`entidad_colaboradora`),
  CONSTRAINT `retos_reales_ibfk_1` FOREIGN KEY (`reto_id`) REFERENCES `retos_solucionables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retos_reales`
--

LOCK TABLES `retos_reales` WRITE;
/*!40000 ALTER TABLE `retos_reales` DISABLE KEYS */;
INSERT INTO `retos_reales` VALUES (1,1,'Fundación Banco de Alimentos de México','colaboracion@bamx.org.mx','Reducir 30% el desperdicio alimentario en 5 ciudades piloto','Acceso a datos reales, mentores de la industria, infraestructura de testing'),(2,2,'Greenpeace México y Secretaría de Medio Ambiente CDMX','tech@greenpeace.org.mx','Instalar 100 sensores piloto en la Ciudad de México','Hardware de sensores, acceso a datos gubernamentales, asesoría técnica'),(3,3,'Red de Universidades Sustentables de México','innovacion@redsustentable.mx','Implementar en 10 universidades como programa piloto','Acceso a comunidades estudiantiles, infraestructura web, feedback de usuarios'),(4,6,'Ministerio de Educación',NULL,NULL,NULL);
/*!40000 ALTER TABLE `retos_reales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retos_solucionables`
--

DROP TABLE IF EXISTS `retos_solucionables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retos_solucionables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `dificultad` enum('basico','intermedio','avanzado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tecnologias_requeridas` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('reto_real','reto_experimental') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('borrador','publicado','en_desarrollo','completado') COLLATE utf8mb4_unicode_ci DEFAULT 'borrador',
  `hackathon_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reto_tipo` (`tipo`),
  KEY `idx_reto_dificultad` (`dificultad`),
  KEY `idx_reto_hackathon` (`hackathon_id`),
  CONSTRAINT `retos_solucionables_ibfk_1` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retos_solucionables`
--

LOCK TABLES `retos_solucionables` WRITE;
/*!40000 ALTER TABLE `retos_solucionables` DISABLE KEYS */;
INSERT INTO `retos_solucionables` VALUES (1,'App para Reducir Desperdicio Alimentario','Desarrollar una aplicación móvil que conecte restaurantes con organizaciones benéficas para redistribuir alimentos no vendidos antes de que caduquen.','intermedio','React Native, Node.js, APIs de geolocalización, Base de datos','reto_real','publicado',1,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(2,'Sistema de Monitoreo de Calidad del Aire Urbano','Crear una red de sensores IoT de bajo costo para medir la calidad del aire en tiempo real y generar alertas ciudadanas.','avanzado','Arduino/Raspberry Pi, Sensores ambientales, APIs web, Dashboard de visualización','reto_real','publicado',1,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(3,'Plataforma de Intercambio de Objetos Reutilizables','Diseñar una plataforma web donde estudiantes universitarios puedan intercambiar objetos que ya no usan, promoviendo la economía circular.','basico','HTML/CSS, JavaScript, Base de datos, API REST','reto_real','publicado',1,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(4,'Simulador de Ecosistemas con IA Generativa','Crear un simulador educativo que use IA para mostrar cómo las acciones humanas afectan diferentes ecosistemas a lo largo del tiempo.','avanzado','Python, Machine Learning, Simulación, Interfaz gráfica','reto_experimental','publicado',1,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(5,'Chatbot Educativo sobre Cambio Climático','Desarrollar un chatbot conversacional que enseñe a estudiantes de secundaria sobre el cambio climático de manera interactiva.','intermedio','NLP, Chatbot frameworks, Base de conocimiento, Interfaz web','reto_experimental','publicado',1,'2025-08-14 22:27:44','2025-08-14 22:27:44'),(6,'App de Gestión Escolar','Desarrollar una aplicación móvil para gestionar tareas y horarios escolares','intermedio','React Native, Firebase, Node.js','reto_real','borrador',1,'2025-08-14 23:29:42','2025-08-14 23:29:42');
/*!40000 ALTER TABLE `retos_solucionables` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-14 18:51:27
