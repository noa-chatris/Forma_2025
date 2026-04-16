USE FORMA;
INSERT INTO `interlocuteur` (`id`, `nom`, `prenom`, `email`, `telephone`, `fax`) VALUES
('I1', 'Leclerc', 'Anne', 'anne.leclerc@crosl.fr', 320556677, NULL),
('I2', 'Martin', 'Luc', 'luc.martin@liguefoot.fr', 320667788, NULL);

INSERT INTO `association` (`icom`, `nom`, `id`) VALUES
(570001, 'CROSL Lorraine', 'I1'),
(570002, 'Ligue de Football Lorraine', 'I2');

INSERT INTO `statut` (`id_statut`, `labell`) VALUES
('1', 'bénévole'),
('3', 'bypass'),
('2', 'employé');

INSERT INTO `role` (`id_role`, `labell`) VALUES
(3, 'directeur'),
(2, 'gestionaire'),
(1, 'utilisateur');

INSERT INTO `competence` (`id_competence`, `labell`) VALUES
(4, 'Communication digitale'),
(1, 'Comptabilité associative'),
(5, 'Gestion de projet'),
(3, 'Premiers secours'),
(2, 'Utilisation d’Excel');

INSERT INTO `domaine` (`id_domaine`, `labell`) VALUES
(3, 'Communication'),
(5, 'Développement durable'),
(1, 'Gestion'),
(2, 'Informatique'),
(4, 'Secourisme');

INSERT INTO `formation` (`id`, `labell`, `max_participant`, `objectifs`, `cout`, `public`) VALUES
(1, 'Comptabilité des associations', 15, 'Savoir tenir une comptabilité associative simple', 50, 'Salariés et bénévoles'),
(2, 'Initiation à Excel', 12, 'Découvrir les fonctions de base d’Excel', 40, 'Salariés'),
(3, 'Communication sur les réseaux sociaux', 10, 'Améliorer la visibilité des associations', 60, 'Bénévoles'),
(4, 'Initiation aux premiers secours', 10, 'Acquérir les gestes de premiers secours', 30, 'Tous publics'),
(5, 'Éco-gestion des clubs', 8, 'Sensibiliser au développement durable', 45, 'Salariés et bénévoles');

INSERT INTO `intervenant` (`id`, `nom`, `prenom`) VALUES
(1, 'Dupont', 'Marc'),
(2, 'Girard', 'Claire'),
(3, 'Bernard', 'Sophie');

INSERT INTO `session` (`id`, `date_limite`, `date_de_session`, `id_formation`) VALUES
(1, '2026-04-18', '2026-04-20', 1),
(2, '2026-04-22', '2026-09-25', 2),
(3, '2026-05-01', '2026-10-10', 3),
(4, '2026-05-05', '2026-10-20', 4),
(5, '2026-06-01', '2026-11-15', 5);

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `adresse`, `code_postal`, `ville`, `email`, `fonction`, `icom`, `id_role`, `id_statut`, `login`, `password`) VALUES
(1, 'Xaneth', 'Michel', '1 rue du Stade', 57000, 'Metz', 'm.xaneth@crosl.fr', 'Directeur Formation', 570001, 3, '2', 'Xmichel', 'test1'),
(2, 'Giroux', 'Julie', '2 rue des Sports', 57000, 'Metz', 'j.giroux@crosl.fr', 'Assistante Formation', 570001, 2, '3', 'Gjulie', 'test2'),
(3, 'Durand', 'Paul', '3 rue des Roses', 57100, 'Thionville', 'paul.durand@asso.fr', 'Trésorier', 570002, 1, '1', 'Dpaul', 'test3'),
(4, 'Lemoine', 'Sarah', '5 avenue de Nancy', 54000, 'Nancy', 'sarah.lemoine@club.fr', 'Secrétaire', 570002, 1, '1', 'Lsarah', 'test4'),
(5, 'Petit', 'Nicolas', '10 rue des Peupliers', 54500, 'Vandoeuvre', 'nicolas.petit@club.fr', 'Responsable com', 570002, 1, '2', 'Pnicolas', 'test5');

INSERT INTO `compétence_des_intervenant` (`id`, `id_competence`) VALUES
(1, 1),
(2, 2),
(3, 3),
(2, 4),
(1, 5);

INSERT INTO `domaine_de_compétence` (`id_domaine`, `id_competence`) VALUES
(1, 1),
(2, 2),
(4, 3),
(3, 4),
(1, 5),
(5, 5);

INSERT INTO `dom_forma` (`id`, `id_domaine`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

INSERT INTO `intervenant_au_formation` (`id_formation`, `id_intervenant`) VALUES
(1, 1),
(5, 1),
(2, 2),
(3, 2),
(4, 3);

INSERT INTO `public_de_formation` (`id`, `id_statut`) VALUES
(1, '1'),
(3, '1'),
(4, '1'),
(5, '1'),
(1, '2'),
(2, '2'),
(4, '2'),
(5, '2');

INSERT INTO `inscription` (`id_utilisateur`, `id_session`, `etat`) VALUES
(3, 1, 'enregistré'),
(3, 4, 'validé'),
(4, 3, 'enregistré'),
(5, 2, 'validé'),
(5, 5, 'enregistré');

GRANT SELECT, INSERT, UPDATE, DELETE ON *.* TO `app`@`localhost` IDENTIFIED BY PASSWORD 'Azerty31';