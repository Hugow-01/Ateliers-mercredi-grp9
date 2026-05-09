-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 09:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `info9`
--

-- --------------------------------------------------------

--
-- Table structure for table `activité`
--

CREATE TABLE `activité` (
  `nom` varchar(100) NOT NULL,
  `capacite` int(11) NOT NULL,
  `syllabus` text NOT NULL,
  `tranche_age` varchar(5) NOT NULL,
  `theme` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activité`
--

INSERT INTO `activité` (`nom`, `capacite`, `syllabus`, `tranche_age`, `theme`) VALUES
('Atelier Arts plastiques', 20, 'Cet atelier permet aux enfants d\'exprimer leur créativité à travers le dessin, la peinture et la sculpture. Chaque séance explore une technique différente : aquarelle, collage, argile. Tout le matériel est fourni.', '', ''),
('Atelier Cuisine & nature', 15, 'Initiation à la cuisine saine et aux produits de saison. Les enfants préparent des recettes simples et apprennent à connaître les fruits et légumes. Tablier fourni.', '', ''),
('Atelier Jeux & motricité', 15, 'Parcours sportifs et jeux collectifs pour se dépenser et développer la coordination. Au programme : jeux d\'équipe, parcours d\'obstacles, sports adaptés. Tenue de sport recommandée.', '', ''),
('Atelier Lecture & créativité', 10, 'Lectures interactives, contes et ateliers d\'écriture créative. Les enfants inventent leurs propres histoires et les illustrent. Idéal pour les petits lecteurs curieux.', '', ''),
('Atelier Musique & rythme', 12, 'Découverte des instruments et éveil musical. Les enfants explorent le rythme, la mélodie et le chant dans une ambiance joyeuse. Aucune connaissance musicale requise.', '', ''),
('damit', 3, 'testmail', '6-14', 'Créativité & arts'),
('football', 24, 'cool', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `creneau`
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
-- Dumping data for table `creneau`
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
(26, '2026-05-13', '10:20:00', '00:20:00', 'B201', 'damit');

-- --------------------------------------------------------

--
-- Table structure for table `enfant`
--

CREATE TABLE `enfant` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `login_famille` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enfant`
--

INSERT INTO `enfant` (`id`, `nom`, `prenom`, `age`, `login_famille`) VALUES
(1, 'Ducobu', 'dam', 8, 'rav@gmail.com'),
(5, 'dubois', 'alex', 5, 'yaya@gmail.com'),
(6, 'dubois', 'alice', 5, 'YEYED@GMAIL.com'),
(7, 'mollet', 'enzo', 12, 'yaya@gmail.com'),
(8, 'dupont', 'quentin', 6, 'dupont@gmail.com'),
(9, 'Test', 'Test', 3, 'test@gmail.com'),
(11, 'test2', 'test2', 17, 'test@gmail.com'),
(12, 'gg', 'Gelo', 8, 'garibaldiyoshii@gmail.com'),
(13, 'll', 'll', 9, 'garibaldiyoshii@gmail.com'),
(14, 'may', 'may', 8, 'misomay1@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `enfant_creneau`
--

CREATE TABLE `enfant_creneau` (
  `id_enfant` int(11) NOT NULL,
  `id_creneau` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enfant_creneau`
--

INSERT INTO `enfant_creneau` (`id_enfant`, `id_creneau`) VALUES
(1, 5),
(6, 17),
(6, 23),
(8, 9),
(9, 24),
(7, 12),
(7, 21),
(7, 23),
(5, 14),
(5, 10),
(5, 26),
(7, 26),
(9, 26);

-- --------------------------------------------------------

--
-- Table structure for table `famille`
--

CREATE TABLE `famille` (
  `login` varchar(100) NOT NULL,
  `mdp` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `famille`
--

INSERT INTO `famille` (`login`, `mdp`, `nom`) VALUES
('dupont1@gmail.com', '$2y$10$DHebmz8Y4LmKXcPBKuuZ.eJUO/cMWb2TBPMXnhwkAhdCJkQYwB3Ne', 'dupont1'),
('dupont@gmail.com', '$2y$10$jzV8f94OvBReuXLLTu/u3OVnlVlYYqPJzhc/Riu8TWeOJNlExJ7Ye', 'dupont'),
('famille@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Famille Dubois'),
('garibaldiyoshii@gmail.com', '$2y$10$j1rci7tN.JpDizWPJ4wmKut68gfBHqO8m3zbfH7IFZkjAj7SQvJbq', 'parent_mail'),
('martin@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Famille Martin'),
('misomay1@gmail.com', '$2y$10$tjaSxKSnzLtZ98o3YGhIkuktbM.xeyQ3j2pX7ktIv0dbf6vvxpWh6', 'ayay'),
('rav@gmail.com', '$2y$10$XujH3d8xYTpKr49ARuKF5OrM3KvcQhBB2LTHIGzzmE0AHgNoK8Cyy', 'Rav'),
('test@gmail.com', '$2y$10$m/WVBXpb.YOp4Clxn0U1aOMCWyJkgP62o.2FgN/ep8Q5KDUGxtzdC', 'Test'),
('yaya@gmail.com', '$2y$10$EhUzdpPaaf4Ev7EG9h3Jsedxt5.3LWZnbUd8gsbFpOUIEmoHkMQUm', 'yaya'),
('YEYED@GMAIL.com', '$2y$10$7X3bI4sfXwD9WL5T2N/A0Oh5CJxS6OWgOJ0wRUBJOwfNohKbsd8vq', 'DUBOIS');

-- --------------------------------------------------------

--
-- Table structure for table `listeattente`
--

CREATE TABLE `listeattente` (
  `id` int(11) NOT NULL,
  `id_enfant` int(11) NOT NULL,
  `id_creneau` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listeattente`
--

INSERT INTO `listeattente` (`id`, `id_enfant`, `id_creneau`, `position`, `date_inscription`) VALUES
(4, 12, 26, 1, '2026-05-07 11:05:49'),
(5, 13, 26, 2, '2026-05-07 11:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `id_message_ref` int(11) DEFAULT NULL,
  `date_heure` datetime NOT NULL,
  `message` text NOT NULL,
  `sujet` varchar(200) NOT NULL,
  `login_famille` varchar(100) NOT NULL,
  `login_responsable` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `login_famille` varchar(100) NOT NULL,
  `id_enfant` int(11) NOT NULL,
  `id_creneau` int(11) NOT NULL,
  `type` enum('accepte','attente') NOT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `login_famille`, `id_enfant`, `id_creneau`, `type`, `message`, `lu`, `date_creation`) VALUES
(1, 'garibaldiyoshii@gmail.com', 13, 26, 'attente', 'Votre enfant ll ll a été placé en liste d\'attente pour l\'activité \"damit\" du 13/05/2026 (10:20 - 00:20).\n\nPosition actuelle : #2.', 0, '2026-05-07 11:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `responsable`
--

CREATE TABLE `responsable` (
  `login` varchar(100) NOT NULL,
  `mdp` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `responsable`
--

INSERT INTO `responsable` (`login`, `mdp`, `nom`, `role`) VALUES
('admin@ateliers.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Julien Rostaing', 'super_admin'),
('claire@gmail.com', '$2y$10$ULofPonb5OBIV3epJc1ED.9vh3JDLgDZGu9LOAJctkiNIVPPqFNkW', 'Claire', 'admin'),
('ueu@gmail.com', '$2y$10$jRafM6rBw93wIbVfy5CUG.XnXi1FsNfShUiC/QaDqLmSpDqPuUGWa', 'yeye', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `salle`
--

CREATE TABLE `salle` (
  `id` varchar(100) NOT NULL,
  `batiment` text NOT NULL,
  `capacite` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salle`
--

INSERT INTO `salle` (`id`, `batiment`, `capacite`) VALUES
('A101', 'Bâtiment A - Rez-de-chaussée', 25),
('B201', 'Bâtiment B - 1er étage', 15),
('C301', 'Bâtiment C - Salle de musique', 12),
('GYM', 'Gymnase principal', 30);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_etat_creneau`
-- (See below for the actual view)
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
-- Structure for view `v_etat_creneau`
--
DROP TABLE IF EXISTS `v_etat_creneau`;

CREATE ALGORITHM=UNDEFINED DEFINER=`info9`@`localhost` SQL SECURITY DEFINER VIEW `v_etat_creneau`  AS SELECT `c`.`id` AS `id_creneau`, `c`.`date` AS `date`, `c`.`debut` AS `debut`, `c`.`fin` AS `fin`, `c`.`id_salle` AS `id_salle`, `c`.`nom_activite` AS `nom_activite`, `a`.`capacite` AS `capacite`, count(distinct `ec`.`id_enfant`) AS `nb_confirmes`, count(distinct `la`.`id_enfant`) AS `nb_attente`, `a`.`capacite`- count(distinct `ec`.`id_enfant`) AS `places_restantes` FROM (((`creneau` `c` join `activité` `a` on(`a`.`nom` = `c`.`nom_activite`)) left join `enfant_creneau` `ec` on(`ec`.`id_creneau` = `c`.`id`)) left join `listeattente` `la` on(`la`.`id_creneau` = `c`.`id`)) GROUP BY `c`.`id`, `a`.`capacite` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activité`
--
ALTER TABLE `activité`
  ADD PRIMARY KEY (`nom`);

--
-- Indexes for table `creneau`
--
ALTER TABLE `creneau`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_salle` (`id_salle`),
  ADD KEY `fk_nom_activite` (`nom_activite`);

--
-- Indexes for table `enfant`
--
ALTER TABLE `enfant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_fam_enf` (`login_famille`);

--
-- Indexes for table `enfant_creneau`
--
ALTER TABLE `enfant_creneau`
  ADD KEY `fk_id_enf` (`id_enfant`),
  ADD KEY `fk_id_cren` (`id_creneau`);

--
-- Indexes for table `famille`
--
ALTER TABLE `famille`
  ADD PRIMARY KEY (`login`);

--
-- Indexes for table `listeattente`
--
ALTER TABLE `listeattente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enfant_creneau` (`id_enfant`,`id_creneau`),
  ADD KEY `fk_la_enfant` (`id_enfant`),
  ADD KEY `fk_la_creneau` (`id_creneau`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_fam` (`login_famille`),
  ADD KEY `fk_log_resp` (`login_responsable`),
  ADD KEY `fk_id_mess_ref` (`id_message_ref`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notif_famille` (`login_famille`),
  ADD KEY `fk_notif_enfant` (`id_enfant`),
  ADD KEY `fk_notif_creneau` (`id_creneau`);

--
-- Indexes for table `responsable`
--
ALTER TABLE `responsable`
  ADD PRIMARY KEY (`login`);

--
-- Indexes for table `salle`
--
ALTER TABLE `salle`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `creneau`
--
ALTER TABLE `creneau`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `enfant`
--
ALTER TABLE `enfant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `listeattente`
--
ALTER TABLE `listeattente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `creneau`
--
ALTER TABLE `creneau`
  ADD CONSTRAINT `fk_id_salle` FOREIGN KEY (`id_salle`) REFERENCES `salle` (`id`),
  ADD CONSTRAINT `fk_nom_activite` FOREIGN KEY (`nom_activite`) REFERENCES `activité` (`nom`);

--
-- Constraints for table `enfant`
--
ALTER TABLE `enfant`
  ADD CONSTRAINT `fk_log_fam_enf` FOREIGN KEY (`login_famille`) REFERENCES `famille` (`login`);

--
-- Constraints for table `enfant_creneau`
--
ALTER TABLE `enfant_creneau`
  ADD CONSTRAINT `fk_id_cren` FOREIGN KEY (`id_creneau`) REFERENCES `creneau` (`id`),
  ADD CONSTRAINT `fk_id_enf` FOREIGN KEY (`id_enfant`) REFERENCES `enfant` (`id`);

--
-- Constraints for table `listeattente`
--
ALTER TABLE `listeattente`
  ADD CONSTRAINT `fk_la_creneau` FOREIGN KEY (`id_creneau`) REFERENCES `creneau` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_la_enfant` FOREIGN KEY (`id_enfant`) REFERENCES `enfant` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_id_mess_ref` FOREIGN KEY (`id_message_ref`) REFERENCES `message` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_log_fam` FOREIGN KEY (`login_famille`) REFERENCES `famille` (`login`),
  ADD CONSTRAINT `fk_log_resp` FOREIGN KEY (`login_responsable`) REFERENCES `responsable` (`login`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notif_creneau` FOREIGN KEY (`id_creneau`) REFERENCES `creneau` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notif_enfant` FOREIGN KEY (`id_enfant`) REFERENCES `enfant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notif_famille` FOREIGN KEY (`login_famille`) REFERENCES `famille` (`login`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
