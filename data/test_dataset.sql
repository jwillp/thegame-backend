-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Jeu 21 Juillet 2016 à 07:07
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `thelist`
--

-- --------------------------------------------------------

--
-- Structure de la table `challenge`
--

CREATE TABLE IF NOT EXISTS `challenge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `nbPoints` int(11) NOT NULL,
  `isDeleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=27 ;

--
-- Contenu de la table `challenge`
--

INSERT INTO `challenge` (`id`, `title`, `description`, `nbPoints`, `isDeleted`) VALUES
(1, 'TEST', 'TEST TEST TEST', 25, 0),
(2, '7fgyh', 'fghfgh', 56, 0),
(3, '7fgyh', 'fghfgh', 56, 0),
(4, '7fgyh', 'fghfgh', 56, 0),
(5, '7fgyh', 'fghfgh', 0, 0),
(6, '7fgyh', 'fghfgh', 0, 0),
(7, '12df', 'fghfgh', 0, 0),
(8, 'ytrtryrty', 'fghfgh', 56, 0),
(9, 'sdfsdf', 'sdfsdfsdfsdfdfsdfs', 10, 0),
(10, 'sdf', 'fdgfdg', 45, 0),
(11, 'sdf', 'sdf', 15, 0),
(12, 'aafdyyu', 'hgjghjghj', 5436, 0),
(13, 'fdgfdg', 'fdgdfgdfgdfg', 111, 0),
(14, 'esasdasd', 'asdasdas', 444, 0),
(15, 'rrrr', 'rrrr', 54, 0),
(16, 'fhggf', 'hgfhgfh', 45, 0),
(17, 'Notif Challenge', 'Notif Challenge', 60, 0),
(18, 'Notif Challenge', 'Notif Challenge', 60, 0),
(21, 'rrrr', 'rrrr', 45, 0),
(25, 'Tetttt', 'ttt', 45, 0),
(26, 'Tetttt', 'ttt', 45, 0);

-- --------------------------------------------------------

--
-- Structure de la table `fos_user`
--

CREATE TABLE IF NOT EXISTS `fos_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_957A647992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_957A6479A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Contenu de la table `fos_user`
--

INSERT INTO `fos_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`) VALUES
(1, 'admin', 'admin', 'admin@admin.com', 'admin@admin.com', 1, 'p493kswzhyoosg0gsgkgwsoo8wkskw8', '$2y$13$p493kswzhyoosg0gsgkgweLwC.7cEngzhlhJ0qabLKr86Bi4ypn16', '2016-07-21 05:41:36', 0, 0, NULL, NULL, NULL, 'a:0:{}', 0, NULL),
(2, 'SYSTEM', 'system', 'system@system.com', 'system@system.com', 1, '977yo8e5pfk008csss84gss0wsg0ss0', '$2y$13$977yo8e5pfk008csss84gepqUxc9MvNbxpwo8pGL0d/V11ZNVyINa', '2016-06-05 20:28:43', 0, 0, NULL, NULL, NULL, 'a:0:{}', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `challenge_id` int(11) DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `former_leader_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `nb_times_before` int(11) DEFAULT NULL,
  `nb_times_after` int(11) DEFAULT NULL,
  `nb_points` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BF5476CAA76ED395` (`user_id`),
  KEY `IDX_BF5476CA98A21AC6` (`challenge_id`),
  KEY `IDX_BF5476CA19BF9315` (`former_leader_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

--
-- Contenu de la table `notification`
--

INSERT INTO `notification` (`id`, `user_id`, `challenge_id`, `type`, `former_leader_id`, `date`, `nb_times_before`, `nb_times_after`, `nb_points`) VALUES
(1, 1, 17, 'NP_USER_CREATED_CHALLENGE', NULL, '0000-00-00 00:00:00', NULL, NULL, NULL),
(2, 1, 18, 'NP_USER_CREATED_CHALLENGE', NULL, '0000-00-00 00:00:00', NULL, NULL, NULL),
(3, 1, 21, 'NP_USER_CREATED_CHALLENGE', NULL, '2016-06-03 02:08:04', NULL, NULL, NULL),
(4, 1, 2, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-03 02:16:05', 0, 1, NULL),
(5, 1, 25, 'USER_CREATED_CHALLENGE', NULL, '2016-06-05 00:44:05', NULL, NULL, NULL),
(6, 1, 26, 'USER_CREATED_CHALLENGE', NULL, '2016-06-05 00:44:22', NULL, NULL, NULL),
(7, 1, 1, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-05 01:13:11', 9, 10, NULL),
(8, 2, 1, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-05 20:30:25', NULL, 1, NULL),
(9, 2, 1, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-05 20:30:51', 1, 2, NULL),
(10, 2, 12, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-05 21:50:47', NULL, 1, NULL),
(11, 1, 12, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-06 01:01:51', NULL, 1, NULL),
(12, 1, 12, 'NP_USER_CANCELED_CHALLENGE_SCORE', NULL, '2016-06-06 01:03:06', 1, 0, NULL),
(13, 1, 12, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-06 01:04:11', 0, 1, NULL),
(14, 1, 12, 'NP_USER_CANCELED_CHALLENGE_SCORE', NULL, '2016-06-06 01:50:18', 1, 0, NULL),
(15, 1, 12, 'NP_USER_COMPLETED_CHALLENGE', NULL, '2016-06-06 01:50:46', 0, 1, NULL),
(16, 1, 12, 'NP_USER_CANCELED_CHALLENGE_SCORE', NULL, '2016-06-06 01:51:42', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `score`
--

CREATE TABLE IF NOT EXISTS `score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `challenge_id` int(11) NOT NULL,
  `nbTimes` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_32993751A76ED395` (`user_id`),
  KEY `IDX_3299375198A21AC6` (`challenge_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Contenu de la table `score`
--

INSERT INTO `score` (`id`, `user_id`, `challenge_id`, `nbTimes`) VALUES
(1, 1, 1, 10),
(2, 1, 2, 1),
(3, 1, 26, 1),
(4, 2, 1, 2),
(5, 2, 12, 1),
(6, 1, 12, 0);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `FK_BF5476CA19BF9315` FOREIGN KEY (`former_leader_id`) REFERENCES `fos_user` (`id`),
  ADD CONSTRAINT `FK_BF5476CA98A21AC6` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`id`),
  ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`);

--
-- Contraintes pour la table `score`
--
ALTER TABLE `score`
  ADD CONSTRAINT `FK_3299375198A21AC6` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`id`),
  ADD CONSTRAINT `FK_32993751A76ED395` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
