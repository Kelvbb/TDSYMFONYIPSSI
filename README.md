# Paradox — Blog Symfony

Blog développé avec Symfony 7, proposant la publication d'articles, la modération des commentaires et une API REST.

## Fonctionnalités

- **Articles** : publication d'articles avec catégories et image (URL externe)
- **Commentaires** : système de commentaires avec modération (approbation/rejet)
- **Utilisateurs** : inscription, profil, activation des comptes par un administrateur
- **Administration** : tableau de bord, gestion des articles, catégories, utilisateurs et commentaires
- **API REST** : API Platform exposant les entités (accès réservé aux admins)

## Prérequis

- PHP 8.2+
- Composer
- MySQL 8+ ou MariaDB 11+
- Extensions PHP : `ctype`, `iconv`, `json`, `mbstring`, `pdo_mysql`, `xml`, `dom`

## Installation

```bash
# Cloner le dépôt
git clone <url-du-repo>
cd TP-symfony

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env .env.local
# Éditer .env.local et configurer DATABASE_URL
```

## Configuration

Dans `.env.local` :

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/paradox?serverVersion=mariadb-11.8.2"
APP_SECRET="votre-secret-aleatoire"
```

## Base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

## Jeux de données

### Fixtures (données de démonstration)

Charger des données de test (admin, utilisateur, catégories, articles, commentaires) :

```bash
php bin/console doctrine:fixtures:load
```

**Comptes créés par les fixtures :**

| Email              | Mot de passe | Rôle  |
|--------------------|--------------|-------|
| admin@paradox.local | admin123     | Admin |
| marie@example.com   | user123      | User  |

**Contenu :** 3 catégories (Technologie, Voyage, Lifestyle), 3 articles, 3 commentaires.

### Créer un administrateur manuellement

```bash
php bin/console app:create-admin email@example.com motdepasse [Prénom] [Nom]
```

## Lancement

```bash
# Serveur de développement Symfony
symfony serve
# ou
php -S localhost:8000 -t public/
```

Accéder à l'application : `http://localhost:8000`

## Rôles et accès

| Zone       | Rôle requis | Description                      |
|------------|-------------|----------------------------------|
| `/`        | Public      | Accueil, blog, connexion         |
| `/profile` | ROLE_USER   | Profil utilisateur               |
| `/admin`   | ROLE_ADMIN  | Administration                   |
| `/api`     | ROLE_ADMIN  | API REST (login via `/api/login`)|

**Note** : Un compte créé par inscription est désactivé par défaut. Un administrateur doit l'activer pour permettre la connexion.

## Structure du projet

```
src/
├── Controller/       # Contrôleurs (Blog, Admin, API, Security, etc.)
├── Entity/           # User, Post, Category, Comment
├── Form/             # Formulaires Symfony
├── Repository/       # Repositories Doctrine
└── Security/         # UserChecker (validation compte actif)

templates/            # Vues Twig
config/               # Configuration Symfony
migrations/           # Migrations Doctrine
```

## Stack technique

- **Framework** : Symfony 7.0
- **ORM** : Doctrine
- **API** : API Platform 4
- **Front** : Twig, Bootstrap 5
- **CORS** : Nelmio CORS Bundle

---

## Documentation

### Routes

#### Espace public

| Route        | Méthode | Description                    |
|--------------|---------|--------------------------------|
| `/`          | GET     | Page d'accueil                 |
| `/blog`      | GET     | Liste des articles             |
| `/blog/{id}` | GET, POST | Article + formulaire commentaire |
| `/login`     | GET, POST | Connexion                    |
| `/logout`    | GET     | Déconnexion                    |
| `/register`  | GET, POST | Inscription                  |

#### Espace utilisateur (ROLE_USER)

| Route           | Méthode | Description        |
|-----------------|---------|--------------------|
| `/profile`      | GET     | Profil utilisateur |
| `/profile/edit` | GET, POST | Modifier le profil |

#### Espace admin (ROLE_ADMIN)

| Route                    | Méthode | Description                  |
|--------------------------|---------|------------------------------|
| `/admin`                 | GET     | Tableau de bord              |
| `/admin/posts`           | GET     | Liste des articles           |
| `/admin/posts/new`       | GET, POST | Nouvel article             |
| `/admin/posts/{id}/edit` | GET, POST | Modifier un article        |
| `/admin/posts/{id}/delete` | POST  | Supprimer un article       |
| `/admin/categories`      | GET     | Liste des catégories         |
| `/admin/categories/new`  | GET, POST | Nouvelle catégorie         |
| `/admin/categories/{id}/edit` | GET, POST | Modifier une catégorie  |
| `/admin/categories/{id}/delete` | POST | Supprimer une catégorie |
| `/admin/users`           | GET     | Liste des utilisateurs       |
| `/admin/users/{id}/toggle-active` | POST | Activer/désactiver un compte |
| `/admin/comments`        | GET     | Modération des commentaires  |
| `/admin/comments/{id}/approve` | POST | Approuver un commentaire |
| `/admin/comments/{id}/reject`  | POST | Rejeter un commentaire  |

#### API (ROLE_ADMIN)

| Route        | Description                      |
|--------------|----------------------------------|
| `/api`       | Point d'entrée API Platform      |
| `/api/login` | Connexion pour l'API             |
| `/api/logout`| Déconnexion API                  |

### Documentation API REST

L'API expose les ressources suivantes (JSON-LD, JSON) :

| Ressource   | Endpoint         | Opérations              |
|-------------|------------------|-------------------------|
| Categories  | `/api/categories` | GET, POST, PATCH, DELETE |
| Comments    | `/api/comments`   | GET, POST, PATCH, DELETE |
| Posts       | `/api/posts`      | GET, POST, PATCH, DELETE |
| Users       | `/api/users`      | GET, POST, PATCH, DELETE |

**Documentation interactive :** `http://localhost:8000/api/docs`

### Entités

| Entité    | Propriétés principales                          | Relations                         |
|-----------|--------------------------------------------------|-----------------------------------|
| **User**  | email, password, roles, firstName, lastName, profilePicture, isActive | → Post, → Comment                |
| **Post**  | title, content, publishedAt, picture             | ← User, ← Category, → Comment     |
| **Category** | name                                         | → Post                            |
| **Comment** | content, createdAt, status (pending/approved/rejected) | ← User, ← Post                |

### Schéma des relations

```
User 1───* Post
  |         |
  |         *───1 Category
  |
  *───* Comment  *───1 Post
```

---

## Licence

Propriétaire
