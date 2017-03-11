-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           5.6.26 - MySQL Community Server (GPL)
-- OS do Servidor:               Win32
-- HeidiSQL Versão:              9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Copiando estrutura do banco de dados para actualsales
DROP DATABASE IF EXISTS `actualsales`;
CREATE DATABASE IF NOT EXISTS `actualsales` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `actualsales`;


-- Copiando estrutura para tabela actualsales.acos
DROP TABLE IF EXISTS `acos`;
CREATE TABLE IF NOT EXISTS `acos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `foreign_key` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Copiando dados para a tabela actualsales.acos: ~0 rows (aproximadamente)
DELETE FROM `acos`;
/*!40000 ALTER TABLE `acos` DISABLE KEYS */;
/*!40000 ALTER TABLE `acos` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.aros
DROP TABLE IF EXISTS `aros`;
CREATE TABLE IF NOT EXISTS `aros` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `foreign_key` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Copiando dados para a tabela actualsales.aros: ~0 rows (aproximadamente)
DELETE FROM `aros`;
/*!40000 ALTER TABLE `aros` DISABLE KEYS */;
/*!40000 ALTER TABLE `aros` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.aros_acos
DROP TABLE IF EXISTS `aros_acos`;
CREATE TABLE IF NOT EXISTS `aros_acos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) unsigned NOT NULL,
  `aco_id` int(10) unsigned NOT NULL,
  `_create` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `_read` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `_update` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `_delete` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Copiando dados para a tabela actualsales.aros_acos: ~0 rows (aproximadamente)
DELETE FROM `aros_acos`;
/*!40000 ALTER TABLE `aros_acos` DISABLE KEYS */;
/*!40000 ALTER TABLE `aros_acos` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.cadastro
DROP TABLE IF EXISTS `cadastro`;
CREATE TABLE IF NOT EXISTS `cadastro` (
  `codigo` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(100) NOT NULL,
  `codigo_regiao` int(11) NOT NULL,
  `codigo_unidade` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `token` text NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela actualsales.cadastro: ~5 rows (aproximadamente)
DELETE FROM `cadastro`;
/*!40000 ALTER TABLE `cadastro` DISABLE KEYS */;
INSERT INTO `cadastro` (`codigo`, `nome`, `data_nascimento`, `email`, `telefone`, `codigo_regiao`, `codigo_unidade`, `score`, `token`) VALUES
	(1, 'caroline cadfsd sfdsfd', '1986-01-01', 'carol@uol.com.br', '', 2, 4, 3, 'f42384ec9f1d036e4228a3181860ef49'),
	(2, 'caroline cadfsd sfdsfd', '1986-01-01', 'carol@uol.com.br', '', 5, 10, 1, 'f42384ec9f1d036e4228a3181860ef49'),
	(3, 'caroline cadfsd sfdsfd', '1986-01-01', 'carol@uol.com.br', '', 3, 7, 4, 'f42384ec9f1d036e4228a3181860ef49'),
	(4, 'caroline cadfsd sfdsfd', '1986-01-01', 'carol@uol.com.br', '', 6, NULL, 0, 'f42384ec9f1d036e4228a3181860ef49'),
	(5, 'caroline cadfsd sfdsfd', '1986-01-01', 'carol@uol.com.br', '', 6, NULL, 0, 'f42384ec9f1d036e4228a3181860ef49');
/*!40000 ALTER TABLE `cadastro` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.filtros
DROP TABLE IF EXISTS `filtros`;
CREATE TABLE IF NOT EXISTS `filtros` (
  `codigo` int(11) NOT NULL,
  `nome_filtro` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `element_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `model_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `codigo_usuario` int(11) DEFAULT NULL,
  `data_inclusao` datetime(3) NOT NULL,
  `codigo_usuario_inclusao` int(11) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Copiando dados para a tabela actualsales.filtros: ~0 rows (aproximadamente)
DELETE FROM `filtros`;
/*!40000 ALTER TABLE `filtros` DISABLE KEYS */;
/*!40000 ALTER TABLE `filtros` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.regiao
DROP TABLE IF EXISTS `regiao`;
CREATE TABLE IF NOT EXISTS `regiao` (
  `codigo` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `data_inclusao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela actualsales.regiao: ~5 rows (aproximadamente)
DELETE FROM `regiao`;
/*!40000 ALTER TABLE `regiao` DISABLE KEYS */;
INSERT INTO `regiao` (`codigo`, `nome`, `data_inclusao`) VALUES
	(2, 'Sul', '2017-03-10 23:07:23'),
	(3, 'Sudeste', '2017-03-10 23:07:30'),
	(4, 'Centro-Oeste', '2017-03-10 23:07:38'),
	(5, 'Nordeste', '2017-03-10 23:07:45'),
	(6, 'Norte', '2017-03-10 23:07:52');
/*!40000 ALTER TABLE `regiao` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.selecoes_filtros
DROP TABLE IF EXISTS `selecoes_filtros`;
CREATE TABLE IF NOT EXISTS `selecoes_filtros` (
  `codigo` int(11) NOT NULL,
  `codigo_filtro` int(11) NOT NULL,
  `campo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` int(11) NOT NULL,
  `valor` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `data_inclusao` datetime(3) NOT NULL,
  `codigo_usuario_inclusao` int(11) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Copiando dados para a tabela actualsales.selecoes_filtros: ~0 rows (aproximadamente)
DELETE FROM `selecoes_filtros`;
/*!40000 ALTER TABLE `selecoes_filtros` DISABLE KEYS */;
/*!40000 ALTER TABLE `selecoes_filtros` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.unidade
DROP TABLE IF EXISTS `unidade`;
CREATE TABLE IF NOT EXISTS `unidade` (
  `codigo` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `codigo_regiao` int(11) DEFAULT NULL,
  `data_inclusao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`codigo`),
  KEY `FK_unidade_regiao` (`codigo_regiao`),
  CONSTRAINT `FK_unidade_regiao` FOREIGN KEY (`codigo_regiao`) REFERENCES `regiao` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela actualsales.unidade: ~8 rows (aproximadamente)
DELETE FROM `unidade`;
/*!40000 ALTER TABLE `unidade` DISABLE KEYS */;
INSERT INTO `unidade` (`codigo`, `nome`, `codigo_regiao`, `data_inclusao`) VALUES
	(1, 'Porto Alegre', 2, '0000-00-00 00:00:00'),
	(4, 'Curitiba', 2, '2017-03-11 03:08:22'),
	(5, 'São Paulo', 3, '2017-03-11 03:08:41'),
	(6, 'Rio de Janeiro', 3, '2017-03-11 03:08:55'),
	(7, 'Belo Horizonte', 3, '2017-03-11 03:09:06'),
	(8, 'Brasília', 4, '2017-03-11 03:09:16'),
	(9, 'Salvador', 5, '2017-03-11 03:09:27'),
	(10, 'Recife', 5, '2017-03-11 03:09:35');
/*!40000 ALTER TABLE `unidade` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.user
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `codigo` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela actualsales.user: ~0 rows (aproximadamente)
DELETE FROM `user`;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `codigo` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela actualsales.users: ~0 rows (aproximadamente)
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;


-- Copiando estrutura para tabela actualsales.usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `codigo` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `apelido` varchar(100) NOT NULL,
  `senha` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ativo` varchar(100) NOT NULL,
  `data_inclusao` varchar(100) NOT NULL,
  `codigo_usuario_inclusao` varchar(100) NOT NULL,
  `codigo_uperfil` varchar(100) NOT NULL,
  `alerta_portal` varchar(100) NOT NULL,
  `alerta_email` varchar(100) NOT NULL,
  `alerta_sms` varchar(100) NOT NULL,
  `celular` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `fuso_horario` varchar(100) NOT NULL,
  `horario_verao` varchar(100) NOT NULL,
  `cracha` varchar(100) NOT NULL,
  `data_senha_expiracao` varchar(100) NOT NULL,
  `admin` varchar(100) NOT NULL,
  `codigo_usuario_alteracao` varchar(100) NOT NULL,
  `data_alteracao` varchar(100) NOT NULL,
  `codigo_usuario_pai` varchar(100) NOT NULL,
  `restringe_base_cnpj` varchar(100) NOT NULL,
  `codigo_cliente` varchar(100) NOT NULL,
  `codigo_departamento` varchar(100) NOT NULL,
  `codigo_filial` varchar(100) NOT NULL,
  `codigo_proposta_credenciamento` varchar(100) NOT NULL,
  `codigo_fornecedor` varchar(100) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela actualsales.usuarios: ~0 rows (aproximadamente)
DELETE FROM `usuarios`;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
