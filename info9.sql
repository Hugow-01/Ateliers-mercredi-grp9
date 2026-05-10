-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 09 mai 2026 à 21:49
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `info9`
--

-- --------------------------------------------------------

--
-- Structure de la table `activite`
--

CREATE TABLE `activite` (
  `nom` varchar(100) NOT NULL,
  `capacite` int(11) NOT NULL,
  `syllabus` text NOT NULL,
  `tranche_age` varchar(10) DEFAULT NULL,
  `theme` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activite`
--

INSERT INTO `activite` (`nom`, `capacite`, `syllabus`, `tranche_age`, `theme`) VALUES
('Atelier Arts plastiques', 5, 'Cet atelier permet aux enfants d\'exprimer leur créativité à travers le dessin, la peinture et la sculpture. Chaque séance explore une technique différente : aquarelle, collage, argile. Tout le matériel est fourni.', '5-12', 'Créativité & arts'),
('Atelier Cuisine & nature', 15, 'Initiation à la cuisine saine et aux produits de saison. Les enfants préparent des recettes simples et apprennent à connaître les fruits et légumes. Tablier fourni.', '5-12', 'Nature & bien-etre'),
('Atelier Jeux & motricité', 15, 'Parcours sportifs et jeux collectifs pour se dépenser et développer la coordination. Au programme : jeux d\'équipe, parcours d\'obstacles, sports adaptés. Tenue de sport recommandée.', '4-12', 'Sport & motricite'),
('Atelier Lecture & créativité', 10, 'Lectures interactives, contes et ateliers d\'écriture créative. Les enfants inventent leurs propres histoires et les illustrent. Idéal pour les petits lecteurs curieux.', '5-12', 'Langage & imaginaire'),
('Atelier Musique & rythme', 12, 'Découverte des instruments et éveil musical. Les enfants explorent le rythme, la mélodie et le chant dans une ambiance joyeuse. Aucune connaissance musicale requise.', '4-12', 'Musique & expression'),
('damit', 3, 'testmail', '6-14', 'Créativité & arts'),
('football', 24, 'cool', '6-14', 'Sport & motricite');

-- --------------------------------------------------------

--
-- Structure de la table `creneau`
--

CREATE TABLE `creneau` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `debut` time DEFAULT NULL,
  `fin` time DEFAULT NULL,
  `id_salle` varchar(100) DEFAULT NULL,
  `nom_activite` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `creneau`
--

INSERT INTO `creneau` (`id`, `date`, `debut`, `fin`, `id_salle`, `nom_activite`) VALUES
(2, '2026-03-04', '14:00:00', '16:00:00', 'A101', 'Atelier Arts plastiques'),
(4, '2026-03-11', '14:00:00', '16:00:00', 'A101', 'Atelier Arts plastiques'),
(5, '2026-03-18', '09:00:00', '11:00:00', 'A101', 'Atelier Arts plastiques'),
(6, '2026-04-01', '09:00:00', '11:00:00', 'A101', 'Atelier Arts plastiques'),
(7, '2026-03-04', '09:00:00', '11:30:00', 'GYM', 'Atelier Jeux & motricité'),
(8, '2026-03-04', '14:00:00', '16:30:00', 'GYM', 'Atelier Jeux & motricité'),
(9, '2026-03-11', '09:00:00', '11:30:00', 'GYM', 'Atelier Jeux & motricité'),
(10, '2026-03-18', '14:00:00', '16:30:00', 'GYM', 'Atelier Jeux & motricité'),
(11, '2026-04-01', '09:00:00', '11:30:00', 'GYM', 'Atelier Jeux & motricité'),
(12, '2026-03-04', '10:00:00', '12:00:00', 'C301', 'Atelier Musique & rythme'),
(13, '2026-03-11', '10:00:00', '12:00:00', 'C301', 'Atelier Musique & rythme'),
(14, '2026-03-18', '10:00:00', '12:00:00', 'C301', 'Atelier Musique & rythme'),
(15, '2026-04-01', '14:00:00', '16:00:00', 'C301', 'Atelier Musique & rythme'),
(16, '2026-03-04', '14:00:00', '16:00:00', 'B201', 'Atelier Lecture & créativité'),
(17, '2026-03-11', '14:00:00', '16:00:00', 'B201', 'Atelier Lecture & créativité'),
(18, '2026-03-18', '09:00:00', '11:00:00', 'B201', 'Atelier Lecture & créativité'),
(19, '2026-04-01', '09:00:00', '11:00:00', 'B201', 'Atelier Lecture & créativité'),
(20, '2026-03-04', '14:00:00', '17:00:00', 'A101', 'Atelier Cuisine & nature'),
(21, '2026-03-18', '14:00:00', '17:00:00', 'A101', 'Atelier Cuisine & nature'),
(22, '2026-04-01', '14:00:00', '17:00:00', 'A101', 'Atelier Cuisine & nature'),
(23, '2026-02-25', '09:00:00', '11:00:00', NULL, 'Atelier Arts plastiques'),
(24, '2026-02-25', '12:00:00', '14:00:00', 'B201', 'football'),
(25, '2026-03-11', '16:02:00', '14:02:00', 'A101', 'Atelier Arts plastiques'),
(26, '2026-05-13', '10:20:00', '00:20:00', 'B201', 'damit'),
(27, '2026-05-09', '20:10:00', '22:10:00', 'B201', 'damit');

-- --------------------------------------------------------

--
-- Structure de la table `enfant`
--

CREATE TABLE `enfant` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `id_famille` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `enfant`
--

INSERT INTO `enfant` (`id`, `nom`, `prenom`, `age`, `id_famille`) VALUES
(1, 'Ducobu', 'dam', 8, 8),
(5, 'dubois', 'alex', 5, 10),
(6, 'dubois', 'alice', 5, 11),
(7, 'mollet', 'enzo', 12, 10),
(8, 'dupont', 'quentin', 6, 3),
(9, 'Test', 'Test', 3, 9),
(11, 'test2', 'test2', 17, 9),
(12, 'gg', 'Gelo', 8, 5),
(13, 'll', 'll', 9, 5),
(14, 'may', 'may', 8, 7),
(15, 'ss', 'scqs', 8, 1);

-- --------------------------------------------------------

--
-- Structure de la table `enfant_creneau`
--

CREATE TABLE `enfant_creneau` (
  `id_enfant` int(11) NOT NULL,
  `id_creneau` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `enfant_creneau`
--

INSERT INTO `enfant_creneau` (`id_enfant`, `id_creneau`) VALUES
(1, 5),
(5, 10),
(5, 14),
(5, 26),
(6, 17),
(6, 23),
(7, 12),
(7, 21),
(7, 23),
(7, 26),
(8, 9),
(9, 24),
(9, 26);

-- --------------------------------------------------------

--
-- Structure de la table `famille`
--

CREATE TABLE `famille` (
  `id` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `famille`
--

INSERT INTO `famille` (`id`, `login`, `mdp`, `nom`) VALUES
(1, 'dam@gmail.com', '$2y$10$G.5pfGtdbslrZYu7M1q8JuXHjzYp/NvLF/CxlDJlRG.Jlyq277.P.', 'gabi'),
(2, 'dupont1@gmail.com', '$2y$10$DHebmz8Y4LmKXcPBKuuZ.eJUO/cMWb2TBPMXnhwkAhdCJkQYwB3Ne', 'dupont1'),
(3, 'dupont@gmail.com', '$2y$10$jzV8f94OvBReuXLLTu/u3OVnlVlYYqPJzhc/Riu8TWeOJNlExJ7Ye', 'dupont'),
(4, 'famille@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Famille Dubois'),
(5, 'garibaldiyoshii@gmail.com', '$2y$10$j1rci7tN.JpDizWPJ4wmKut68gfBHqO8m3zbfH7IFZkjAj7SQvJbq', 'parent_mail'),
(6, 'martin@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Famille Martin'),
(7, 'misomay1@gmail.com', '$2y$10$tjaSxKSnzLtZ98o3YGhIkuktbM.xeyQ3j2pX7ktIv0dbf6vvxpWh6', 'ayay'),
(8, 'rav@gmail.com', '$2y$10$XujH3d8xYTpKr49ARuKF5OrM3KvcQhBB2LTHIGzzmE0AHgNoK8Cyy', 'Rav'),
(9, 'test@gmail.com', '$2y$10$m/WVBXpb.YOp4Clxn0U1aOMCWyJkgP62o.2FgN/ep8Q5KDUGxtzdC', 'Test'),
(10, 'aya.khajjou@hotmail.com', '$2y$10$EhUzdpPaaf4Ev7EG9h3Jsedxt5.3LWZnbUd8gsbFpOUIEmoHkMQUm', 'yaya'),
(11, 'YEYED@GMAIL.com', '$2y$10$7X3bI4sfXwD9WL5T2N/A0Oh5CJxS6OWgOJ0wRUBJOwfNohKbsd8vq', 'DUBOIS');

-- --------------------------------------------------------

--
-- Structure de la table `listeattente`
--

CREATE TABLE `listeattente` (
  `id` int(11) NOT NULL,
  `id_enfant` int(11) NOT NULL,
  `id_creneau` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `date_inscription` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `listeattente`
--

INSERT INTO `listeattente` (`id`, `id_enfant`, `id_creneau`, `position`, `date_inscription`) VALUES
(4, 12, 26, 1, '2026-05-07 11:05:49'),
(5, 13, 26, 2, '2026-05-07 11:30:42');

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `id_message_ref` int(11) DEFAULT NULL,
  `date_heure` datetime NOT NULL,
  `message` text NOT NULL,
  `sujet` varchar(200) NOT NULL,
  `id_famille` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `id_famille` int(11) NOT NULL,
  `id_enfant` int(11) NOT NULL,
  `id_creneau` int(11) NOT NULL,
  `type` enum('accepte','attente','annulation','modification') NOT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notification`
--

INSERT INTO `notification` (`id`, `id_famille`, `id_enfant`, `id_creneau`, `type`, `message`, `lu`, `date_creation`) VALUES
(1, 5, 13, 26, 'attente', 'Votre enfant ll ll a été placé en liste d\'attente pour l\'activité \"damit\" du 13/05/2026 (10:20 - 00:20).\n\nPosition actuelle : #2.', 0, '2026-05-07 11:30:42'),
(2, 10, 5, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 1, '2026-05-09 17:27:50'),
(3, 10, 7, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 1, '2026-05-09 17:27:53'),
(4, 9, 9, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 0, '2026-05-09 17:27:54'),
(5, 5, 12, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 0, '2026-05-09 17:27:56'),
(6, 5, 13, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 0, '2026-05-09 17:27:58'),
(7, 8, 1, 5, 'modification', 'L\'activite \"Atelier Arts plastiques\" a ete modifiee par l\'administration.\n\nNouveau nom : Atelier Arts plastiques\nNouvelle capacite : 4 places\n\nVotre inscription reste valide. Connectez-vous pour voir les details.', 0, '2026-05-09 17:32:47'),
(8, 11, 6, 23, 'modification', 'L\'activite \"Atelier Arts plastiques\" a ete modifiee par l\'administration.\n\nNouveau nom : Atelier Arts plastiques\nNouvelle capacite : 4 places\n\nVotre inscription reste valide. Connectez-vous pour voir les details.', 0, '2026-05-09 17:32:49'),
(9, 10, 7, 23, 'modification', 'L\'activite \"Atelier Arts plastiques\" a ete modifiee par l\'administration.\n\nNouveau nom : Atelier Arts plastiques\nNouvelle capacite : 4 places\n\nVotre inscription reste valide. Connectez-vous pour voir les details.', 1, '2026-05-09 17:32:50'),
(10, 10, 5, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 1, '2026-05-09 18:00:31'),
(11, 10, 7, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 1, '2026-05-09 18:00:33'),
(12, 9, 9, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 0, '2026-05-09 18:00:35'),
(13, 5, 12, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 0, '2026-05-09 18:00:36'),
(14, 5, 13, 26, 'annulation', 'L\'activite \"damit\" du 13/05/2026 (10:20 - 00:20) a ete annulee par l\'administration.\n\nNous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.', 0, '2026-05-09 18:00:38'),
(15, 8, 1, 5, 'modification', 'L\'activité \"Atelier Arts plastiques\" a été modifiée par l\'administration.\n\nNouveau nom : Atelier Arts plastiques\nNouvelle capacité : 5 places\n\nVotre inscription reste valide. Connectez-vous pour voir les détails.', 0, '2026-05-09 21:44:11'),
(16, 11, 6, 23, 'modification', 'L\'activité \"Atelier Arts plastiques\" a été modifiée par l\'administration.\n\nNouveau nom : Atelier Arts plastiques\nNouvelle capacité : 5 places\n\nVotre inscription reste valide. Connectez-vous pour voir les détails.', 0, '2026-05-09 21:44:13'),
(17, 10, 7, 23, 'modification', 'L\'activité \"Atelier Arts plastiques\" a été modifiée par l\'administration.\n\nNouveau nom : Atelier Arts plastiques\nNouvelle capacité : 5 places\n\nVotre inscription reste valide. Connectez-vous pour voir les détails.', 0, '2026-05-09 21:44:15');

-- --------------------------------------------------------

--
-- Structure de la table `reset_token`
--

CREATE TABLE `reset_token` (
  `id` int(11) NOT NULL,
  `id_famille` int(11) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reset_token`
--

INSERT INTO `reset_token` (`id`, `id_famille`, `token`, `expire_at`, `used`) VALUES
(1, 5, '1dc73e8e640edfa44624a45217680754a391d063b4dddb4d102a06eff90105c0', '2026-05-09 21:27:20', 0);

-- --------------------------------------------------------

--
-- Structure de la table `responsable`
--

CREATE TABLE `responsable` (
  `id` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `responsable`
--

INSERT INTO `responsable` (`id`, `login`, `mdp`, `nom`, `role`) VALUES
(1, 'admin@ateliers.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Julien Rostaing', 'super_admin'),
(2, 'claire@gmail.com', '$2y$10$ULofPonb5OBIV3epJc1ED.9vh3JDLgDZGu9LOAJctkiNIVPPqFNkW', 'Claire', 'admin'),
(3, 'ueu@gmail.com', '$2y$10$jRafM6rBw93wIbVfy5CUG.XnXi1FsNfShUiC/QaDqLmSpDqPuUGWa', 'yeye', 'admin');

-- --------------------------------------------------------

--
-- Structure de la table `salle`
--

CREATE TABLE `salle` (
  `id` varchar(100) NOT NULL,
  `batiment` text NOT NULL,
  `capacite` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `salle`
--

INSERT INTO `salle` (`id`, `batiment`, `capacite`) VALUES
('A101', 'Bâtiment A - Rez-de-chaussée', 25),
('B201', 'Bâtiment B - 1er étage', 15),
('C301', 'Bâtiment C - Salle de musique', 12),
('GYM', 'Gymnase principal', 30);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_etat_creneau`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_etat_creneau` (
`id_creneau` int(11)
,`date` date
,`debut` time
,`fin` time
,`id_salle` varchar(100)
,`nom_activite` varchar(100)
,`capacite` int(11)
,`nb_confirmes` bigint(21)
,`nb_attente` bigint(21)
,`places_restantes` bigint(22)
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_etat_creneau`
--
DROP TABLE IF EXISTS `v_etat_creneau`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_etat_creneau`  AS SELECT `c`.`id` AS `id_creneau`, `c`.`date` AS `date`, `c`.`debut` AS `debut`, `c`.`fin` AS `fin`, `c`.`id_salle` AS `id_salle`, `c`.`nom_activite` AS `nom_activite`, `a`.`capacite` AS `capacite`, count(distinct `ec`.`id_enfant`) AS `nb_confirmes`, count(distinct `la`.`id_enfant`) AS `nb_attente`, `a`.`capacite`- count(distinct `ec`.`id_enfant`) AS `places_restantes` FROM (((`creneau` `c` join `activite` `a` on(`a`.`nom` = `c`.`nom_activite`)) left join `enfant_creneau` `ec` on(`ec`.`id_creneau` = `c`.`id`)) left join `listeattente` `la` on(`la`.`id_creneau` = `c`.`id`)) GROUP BY `c`.`id` ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activite`
--
ALTER TABLE `activite`
  ADD PRIMARY KEY (`nom`);

--
-- Index pour la table `creneau`
--
ALTER TABLE `creneau`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_salle` (`id_salle`),
  ADD KEY `fk_nom_activite` (`nom_activite`);

--
-- Index pour la table `enfant`
--
ALTER TABLE `enfant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enfant_famille` (`id_famille`);

--
-- Index pour la table `enfant_creneau`
--
ALTER TABLE `enfant_creneau`
  ADD PRIMARY KEY (`id_enfant`,`id_creneau`),
  ADD KEY `id_creneau` (`id_creneau`);

--
-- Index pour la table `famille`
--
ALTER TABLE `famille`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Index pour la table `listeattente`
--
ALTER TABLE `listeattente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_enfant` (`id_enfant`,`id_creneau`),
  ADD KEY `id_creneau` (`id_creneau`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_message_ref` (`id_message_ref`),
  ADD KEY `id_famille` (`id_famille`),
  ADD KEY `id_responsable` (`id_responsable`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_famille` (`id_famille`),
  ADD KEY `id_enfant` (`id_enfant`),
  ADD KEY `id_creneau` (`id_creneau`);

--
-- Index pour la table `reset_token`
--
ALTER TABLE `reset_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `id_famille` (`id_famille`);

--
-- Index pour la table `responsable`
--
ALTER TABLE `responsable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Index pour la table `salle`
--
ALTER TABLE `salle`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `creneau`
--
ALTER TABLE `creneau`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `enfant`
--
ALTER TABLE `enfant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `famille`
--
ALTER TABLE `famille`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `listeattente`
--
ALTER TABLE `listeattente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `reset_token`
--
ALTER TABLE `reset_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `responsable`
--
ALTER TABLE `responsable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `creneau`
--
ALTER TABLE `creneau`
  ADD CONSTRAINT `fk_id_salle` FOREIGN KEY (`id_salle`) REFERENCES `salle` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_nom_activite` FOREIGN KEY (`nom_activite`) REFERENCES `activite` (`nom`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `enfant`
--
ALTER TABLE `enfant`
  ADD CONSTRAINT `fk_enfant_famille` FOREIGN KEY (`id_famille`) REFERENCES `famille` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `enfant_creneau`
--
ALTER TABLE `enfant_creneau`
  ADD CONSTRAINT `enfant_creneau_ibfk_1` FOREIGN KEY (`id_enfant`) REFERENCES `enfant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enfant_creneau_ibfk_2` FOREIGN KEY (`id_creneau`) REFERENCES `creneau` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `listeattente`
--
ALTER TABLE `listeattente`
  ADD CONSTRAINT `listeattente_ibfk_1` FOREIGN KEY (`id_enfant`) REFERENCES `enfant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `listeattente_ibfk_2` FOREIGN KEY (`id_creneau`) REFERENCES `creneau` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`id_message_ref`) REFERENCES `message` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`id_famille`) REFERENCES `famille` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`id_responsable`) REFERENCES `responsable` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_famille`) REFERENCES `famille` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`id_enfant`) REFERENCES `enfant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_3` FOREIGN KEY (`id_creneau`) REFERENCES `creneau` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reset_token`
--
ALTER TABLE `reset_token`
  ADD CONSTRAINT `reset_token_ibfk_1` FOREIGN KEY (`id_famille`) REFERENCES `famille` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
