CREATE DATABASE adminet_global;
USE adminet_global;

DROP TABLE IF EXISTS `usuarios_empresas`;

CREATE TABLE `usuarios_empresas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `base_datos` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `logs_sesiones`;
CREATE TABLE `logs_sesiones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `ip_publica` varchar(45) DEFAULT NULL,
  `navegador` varchar(50) DEFAULT NULL,
  `user_agent` text,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  `exitoso` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
);

-- Ejemplo de cómo registrar una empresa y usuario administrador al sistema:
-- El orden de los valores es:
-- ID (auto), USUARIO, PASSWORD, NOMBRE_EMPRESA, BASE_DE_DATOS
-- INSERT INTO `usuarios_empresas` VALUES (1, 'adminet', 'adminet', 'adminet', 'adminet_test');
