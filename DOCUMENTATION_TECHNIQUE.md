# Documentation Technique - Forma_2025

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture générale](#architecture-générale)
3. [Structure du projet](#structure-du-projet)
4. [Fonctionnalités principales](#fonctionnalités-principales)
5. [Authentification et autorisation](#authentification-et-autorisation)
6. [Base de données](#base-de-données)
7. [Composants clés](#composants-clés)
8. [Configuration](#configuration)
9. [Points d'entrée](#points-dentrée)
10. [Sécurité](#sécurité)

---

## Vue d'ensemble

**Forma_2025** est une application web PHP dédiée à la gestion de formations, sessions et inscriptions. Elle permet à différents rôles d'utilisateurs (formateurs, gestionnaires, apprenants) de gérer et consulter des formations selon leurs permissions.

**Stack technologique :**
- **Backend :** PHP 8+
- **Frontend :** HTML/CSS (Tailwind CSS)
- **Base de données :** MySQL/MariaDB
- **Architecture :** MVC allégée avec inclusions

---

## Architecture générale

### Flux d'application

```
Requête HTTP
    ↓
Vérification session
    ↓
Protection d'accès (si nécessaire)
    ↓
Traitement de la requête
    ↓
Requête BD (PDO)
    ↓
Rendu du template HTML
    ↓
Réponse au client
```

### Modèle de contrôle d'accès

L'application utilise un système de **rôles et permissions** :
- **Rôle 1** : Apprenant/Utilisateur standard
- **Rôle 2** : Gestionnaire/Formateur
- **Rôle 3** : Administrateur

Les statuts (`id_statut`) offrent un contrôle granulaire supplémentaire.

---

## Structure du projet

```
Forma_2025/
├── index.php                          # Point d'entrée - Page de connexion
├── deconnexion.php                    # Déconnexion utilisateur
├── style.css                          # Styles personnalisés
├── robots.txt                         # Configuration robots
├── README.md                          # Readme du projet
│
├── include/                           # Inclusions et utilitaires
│   ├── bd.php                         # Connexion PDO à la BD
│   ├── header.php                     # En-têtes HTML communes
│   ├── protection.php                 # Système d'autorisation
│   ├── catch_get_error.php            # Gestion des erreurs GET
│   └── disconect.php                  # Utilitaire déconnexion
│
├── src/                               # Ressources statiques
│   ├── favicon.ico                    # Icône du navigateur
│   ├── MDL.png                        # Ressource graphique
│   └── index.php
│
├── formation/                         # Gestion des formations
│   ├── index.php                      # Liste des formations
│   ├── inscription/                   # Inscriptions aux sessions
│   │   └── index.php
│   ├── include/                       # Utilitaires formation
│   │   └── index.php
│   └── gestionaire/                   # Panel gestionnaire
│       ├── index.php                  # Tableau de bord
│       ├── historiser/                # Historique
│       │   └── index.php
│       ├── inscrire/                  # Gestion inscriptions
│       │   └── index.php
│       ├── liste/                     # Listes
│       │   └── index.php
│       ├── reglement/                 # Paramètres
│       │   └── index.php
│       ├── utilisateurs/              # Gestion utilisateurs
│       │   └── index.php
│       └── include/
│           └── index.php
│
└── catalogue/                         # Catalogue public
    ├── index.php                      # Liste des formations
    ├── modification/                  # Modification catalogue
    │   └── index.php
    └── include/
        └── index.php
```

---

## Fonctionnalités principales

### 1. Authentification
- **Fichier :** `index.php`
- **Processus :**
  - Vérification des identifiants (login/password)
  - Création de session avec informations utilisateur
  - Redirection selon le rôle

### 2. Consultation du catalogue
- **Fichier :** `catalogue/index.php`
- **Accès :** Rôles 2 et 3
- **Fonctionnalités :**
  - Liste des formations avec domaines, objectifs, coût
  - Affichage du nombre de sessions
  - Gestion des effectifs maximum

### 3. Gestion des formations
- **Fichier :** `formation/index.php`
- **Accès :** Rôles 1 et 2+
- **Fonctionnalités :**
  - Recherche par nom de formation
  - Tri des formations
  - Affichage des sessions à venir
  - Suivi des inscriptions utilisateur

### 4. Panel gestionnaire
- **Fichier :** `formation/gestionaire/index.php`
- **Accès :** Rôle 2 uniquement
- **Sous-modules :**
  - **Historiser** : Suivi historique
  - **Inscrire** : Gestion des inscriptions
  - **Liste** : Listes de sessions/apprenants
  - **Reglement** : Configuration des paramètres
  - **Utilisateurs** : Gestion des utilisateurs

### 5. Inscription aux formations
- **Fichier :** `formation/inscription/index.php`
- **Accès :** Rôle 1 (apprenants)
- **Fonctionnalités :**
  - Inscription à une session
  - Validation et statuts d'inscription

---

## Authentification et autorisation

### Système de sessions

```php
// Après une connexion réussie :
$_SESSION['id']          // ID utilisateur
$_SESSION['nom']         // Nom de famille
$_SESSION['prenom']      // Prénom
$_SESSION['fonction']    // Fonction de l'utilisateur
$_SESSION['id_role']     // Rôle (1, 2, 3)
$_SESSION['id_statut']   // Statut spécifique
```

### Fonction de protection

```php
protect(array $group_authorized = [1, 2, 3], 
        string $path_if_invalide = '/', 
        bool $strict = true): void
```

**Paramètres :**
- `$group_authorized` : Rôles autorisés
- `$path_if_invalide` : URL de redirection en cas d'accès refusé
- `$strict` : Mode strict (rôle exact) ou hiérarchique

**Exemple d'utilisation :**
```php
// Seuls les gestionnaires (rôle 2) et admins (rôle 3)
protect([2, 3], strict: true);

// Tous les utilisateurs connectés
protect();
```

### Fonction d'utilisateur courant

```php
$current = current_user();  // Retourne $_SESSION
```

### Fonction de vérification de statut

```php
if (user_has_status([1, 2, 3])) {
    // Utilisateur avec l'un de ces statuts
}
```

---

## Base de données

### Configuration

**Fichier :** `include/bd.php`

```php
$host   = "localhost"
$dbname = "FORMA"
$user   = "app"
$pass   = "Azerty31"
```

### Connexion PDO

```php
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

### Tables principales

| Table | Description |
|-------|-------------|
| `utilisateur` | Utilisateurs du système (id, login, password, nom, prenom, fonction, id_role, id_statut) |
| `formation` | Formations disponibles (id, labell, objectifs, cout, public, max_participant) |
| `session` | Sessions de formation (id, id_formation, date_de_session) |
| `domaine` | Domaines de compétences (id_domaine, labell) |
| `dom_forma` | Relation formation-domaine (id, id_domaine) |
| `inscription` | Inscriptions aux sessions (id, id_utilisateur, id_session, etat, ...) |

### Requêtes courantes

#### Lister les formations avec domaines et sessions

```sql
SELECT f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant, 
       GROUP_CONCAT(d.labell SEPARATOR ', ') AS domaines,
       COUNT(s.id) AS sessions_count
FROM formation f
LEFT JOIN dom_forma df ON f.id = df.id
LEFT JOIN domaine d ON df.id_domaine = d.id_domaine
LEFT JOIN session s ON f.id = s.id_formation
GROUP BY f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant
ORDER BY f.labell ASC
```

#### Vérifier les inscriptions actuelles d'un utilisateur

```sql
SELECT DISTINCT s.id_formation 
FROM inscription i 
JOIN session s ON i.id_session = s.id 
WHERE i.id_utilisateur = :user 
  AND s.date_de_session >= CURDATE() 
  AND i.etat IN ('enregistré', 'validé')
```

#### Rechercher une formation

```sql
WHERE f.labell LIKE :labell
```

---

## Composants clés

### 1. Fonction `h()` (Échappement HTML)

```php
function h($s) { 
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); 
}
```

**Utilité :** Prévenir les injections XSS en convertissant les caractères spéciaux.

### 2. Fonction `error()` (Gestion des erreurs GET)

```php
function error(): string {
    return isset($_GET['erreur']) 
        ? htmlspecialchars($_GET['erreur'], ENT_QUOTES, 'UTF-8') 
        : '';
}
```

**Utilité :** Récupérer et afficher les messages d'erreur sécurisés.

### 3. Redirection selon rôle

```php
function redirect(): void {
    if (isset($_SESSION['id_role']) && $_SESSION['id_role'] !== 1) {
        header("Location: catalogue/");
        exit();
    }
    header("Location: formation/");
    exit();
}
```

**Logique :**
- Rôle 1 → `formation/`
- Rôle 2/3 → `catalogue/`

---

## Configuration

### En-têtes HTML

**Fichier :** `include/header.php`

```html
<head>
    <title>CROSL FORMA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/src/favicon.ico">
</head>
```

### Design

- **CSS Framework :** Tailwind CSS 3+
- **Couleur primaire :** `.bg-m2l-primary` (émeraude)
- **Couleur secondaire :** `.bg-slate-900` (gris foncé)
- **Responsive :** Mobile-first avec breakpoints Tailwind

---

## Points d'entrée

### Page d'accueil / Connexion
- **URL :** `/index.php` ou `/`
- **Méthode :** GET (affichage) / POST (authentification)
- **Accès :** Public (non connecté)
- **Actions :**
  - Formulaire de connexion
  - Validation des identifiants
  - Création de session
  - Redirection selon rôle

### Catalogue des formations
- **URL :** `/catalogue/index.php`
- **Accès :** Rôles 2 et 3
- **Paramètres GET :** Aucun
- **Affichage :** Table des formations avec détails

### Page Formation
- **URL :** `/formation/index.php`
- **Accès :** Tous (connectés)
- **Paramètres GET :**
  - `NomForma` : Recherche textuelle
  - `tri` : Tri ('titre_asc', 'titre_desc')
- **Affichage :** Formations avec sessions et statuts

### Panel gestionnaire
- **URL :** `/formation/gestionaire/index.php`
- **Accès :** Rôle 2 uniquement
- **Sous-sections :** Voir structure du projet

### Déconnexion
- **URL :** `/deconnexion.php`
- **Action :** Destruction de session + redirection

---

## Sécurité

### Bonnes pratiques implémentées

1. **Préparation des requêtes SQL**
   ```php
   $prep = $pdo->prepare("SELECT * FROM utilisateur WHERE login = :login");
   $prep->execute([':login' => $login]);
   ```

2. **Échappement HTML**
   ```php
   <?= h($variable) ?>
   ```

3. **Protection d'accès par rôle**
   ```php
   protect([2], strict: true);
   ```

4. **Vérification de session**
   ```php
   if (!isset($_SESSION['id_role'])) {
       header('Location: /?erreur=non connecté');
   }
   ```

### Recommandations de sécurité

⚠️ **À améliorer :**
1. **Hachage des mots de passe** : Utiliser `password_hash()` et `password_verify()`
2. **HTTPS obligatoire** en production
3. **Rate limiting** sur la page de connexion
4. **CSRF tokens** sur les formulaires POST
5. **Logs d'audit** pour tracer les actions sensibles
6. **Expiration de session** après inactivité

### Exemple d'amélioration (hachage des mots de passe)

```php
// À la création/modification du mot de passe
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// À la connexion
$prep = $pdo->prepare("SELECT * FROM utilisateur WHERE login = :login");
$prep->execute([':login' => $login]);
$result = $prep->fetch();

if ($result && password_verify($password, $result['password'])) {
    // Connexion réussie
}
```

---

## Flux de connexion détaillé

```
1. Utilisateur accède à index.php
   ↓
2. Vérification : Session existante ?
   - OUI → Redirection selon rôle
   - NON → Affichage du formulaire
   ↓
3. Soumission du formulaire (POST)
   ↓
4. Récupération : login + password
   ↓
5. Requête BD : SELECT * FROM utilisateur WHERE login = ? AND password = ?
   ↓
6. Résultat trouvé ?
   - OUI → Création de session + Redirection
   - NON → Message d'erreur + Redirection vers index.php?erreur=...
```

---

## Flux de navigation utilisateur (Apprenant)

```
Connexion (index.php)
   ↓
Dashboard Formation (formation/index.php)
   - Consulter les formations
   - Rechercher/Trier
   ↓
Inscription à une session (formation/inscription/index.php)
   ↓
Confirmation + Retour au dashboard
   ↓
Déconnexion (deconnexion.php)
```

---

## Flux de navigation utilisateur (Gestionnaire)

```
Connexion (index.php)
   ↓
Choix : Catalogue (catalogue/index.php) ou Dashboard (formation/index.php)
   ↓
Panel Gestionnaire (formation/gestionaire/index.php)
   - Gestion des inscriptions
   - Gestion des utilisateurs
   - Historique et rapports
   - Configuration (règlement)
   ↓
Retour au catalogue ou Dashboard
   ↓
Déconnexion
```

---

## Variables d'environnement

**À ajouter (recommandé) :**

```php
// .env ou config.php
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'FORMA');
define('DB_USER', $_ENV['DB_USER'] ?? 'app');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'Azerty31');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost');
define('SESSION_TIMEOUT', $_ENV['SESSION_TIMEOUT'] ?? 1800); // 30 minutes
```

---

## Maintenance et évolution

### Points de maintenance courants

1. **Mise à jour des formations** : Table `formation`
2. **Gestion des sessions** : Table `session`
3. **Validation des inscriptions** : Table `inscription`
4. **Maintenance des utilisateurs** : Table `utilisateur`

### Amélirations suggérées

- [ ] Convertir vers une architecture MVC complète (Symfony/Laravel)
- [ ] Ajouter un système de cache
- [ ] Implémenter les logs d'erreurs
- [ ] Ajouter des tests unitaires
- [ ] Améliorer la sécurité (CSRF, hachage pwd)
- [ ] Pagination sur les listes longues
- [ ] Internationalization (i18n)
- [ ] API REST pour mobile

---

## Support et contact

**Application :** CROSL FORMA  
**Version :** 2025  
**Dernière mise à jour :** 16 avril 2026

Pour toute question technique, consultez la base de données ou les logs d'erreur de l'application.

---

**Fin de la documentation**
