CREATE DATABASE FORMA;
USE FORMA;

CREATE TABLE formation(
   id INT,
   labell VARCHAR(50) NOT NULL,
   max_participant INT NOT NULL,
   objectifs VARCHAR(300),
   cout INT NOT NULL,
   public VARCHAR(50),
   PRIMARY KEY(id)
)ENGINE=INNODB;

CREATE TABLE role(
   id_role INT,
   labell VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_role),
   UNIQUE(labell)
)ENGINE=INNODB;

CREATE TABLE domaine(
   id_domaine INT,
   labell VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_domaine),
   UNIQUE(labell)
)ENGINE=INNODB;

CREATE TABLE intervenant(
   id INT,
   nom VARCHAR(50) NOT NULL,
   prenom VARCHAR(50) NOT NULL,
   PRIMARY KEY(id)
)ENGINE=INNODB;

CREATE TABLE statut(
   id_statut VARCHAR(50),
   labell VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_statut),
   UNIQUE(labell)
)ENGINE=INNODB;

CREATE TABLE competence(
   id_competence INT,
   labell VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_competence),
   UNIQUE(labell)
)ENGINE=INNODB;

CREATE TABLE session(
   id INT,
   date_limite DATE NOT NULL,
   date_de_session VARCHAR(200) NOT NULL,
   id_formation INT NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(id_formation) REFERENCES formation(id)
)ENGINE=INNODB;

CREATE TABLE interlocuteur(
   id VARCHAR(50),
   nom VARCHAR(50) NOT NULL,
   prenom VARCHAR(50) NOT NULL,
   email VARCHAR(100) NOT NULL,
   telephone INT NOT NULL,
   fax INT,
   PRIMARY KEY(id)
)ENGINE=INNODB;

CREATE TABLE association(
   icom BIGINT,
   nom VARCHAR(50) NOT NULL,
   id VARCHAR(50) NOT NULL,
   PRIMARY KEY(icom),
   FOREIGN KEY(id) REFERENCES interlocuteur(id)
)ENGINE=INNODB;

CREATE TABLE utilisateur(
   id INT,
   nom VARCHAR(50) NOT NULL,
   prenom VARCHAR(50) NOT NULL,
   adresse VARCHAR(50),
   code_postal INT,
   ville VARCHAR(50),
   email VARCHAR(100) NOT NULL,
   fonction VARCHAR(50),
   icom BIGINT,
   id_role INT NOT NULL,
   id_statut VARCHAR(50) NOT NULL,
   login VARCHAR(60) NOT NULL,
   password VARCHAR(120) NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(icom) REFERENCES association(icom),
   FOREIGN KEY(id_role) REFERENCES role(id_role),
   FOREIGN KEY(id_statut) REFERENCES statut(id_statut)
)ENGINE=INNODB;

CREATE TABLE domaine_de_compétence(
   id_domaine INT,
   id_competence INT,
   PRIMARY KEY(id_domaine, id_competence),
   FOREIGN KEY(id_domaine) REFERENCES domaine(id_domaine),
   FOREIGN KEY(id_competence) REFERENCES competence(id_competence)
)ENGINE=INNODB;

CREATE TABLE dom_forma(
   id INT,
   id_domaine INT,
   PRIMARY KEY(id, id_domaine),
   FOREIGN KEY(id) REFERENCES formation(id),
   FOREIGN KEY(id_domaine) REFERENCES domaine(id_domaine)
)ENGINE=INNODB;

CREATE TABLE compétence_des_intervenant(
   id INT,
   id_competence INT,
   PRIMARY KEY(id, id_competence),
   FOREIGN KEY(id) REFERENCES intervenant(id),
   FOREIGN KEY(id_competence) REFERENCES competence(id_competence)
)ENGINE=INNODB;

CREATE TABLE intervenant_au_formation(
   id_formation INT,
   id_intervenant INT,
   PRIMARY KEY(id_formation, id_intervenant),
   FOREIGN KEY(id_formation) REFERENCES formation(id),
   FOREIGN KEY(id_intervenant) REFERENCES intervenant(id)
)ENGINE=INNODB;

CREATE TABLE inscription(
   id_utilisateur INT,
   id_session INT,
   etat VARCHAR(50),
   PRIMARY KEY(id_utilisateur, id_session),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id),
   FOREIGN KEY(id_session) REFERENCES session(id)
)ENGINE=INNODB;

CREATE TABLE public_de_formation(
   id INT,
   id_statut VARCHAR(50),
   PRIMARY KEY(id, id_statut),
   FOREIGN KEY(id) REFERENCES formation(id),
   FOREIGN KEY(id_statut) REFERENCES statut(id_statut)
)ENGINE=INNODB;
