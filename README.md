# Gestion de devis et factures

Application web Laravel pour centraliser les clients et préparer la gestion des devis et factures.

## Fonctionnalités

- Tableau de bord avec le nombre de clients et les fiches récemment créées.
- Carnet clients : création, consultation, modification et suppression.
- Recherche par nom, entreprise, adresse e-mail ou ville.
- Interface en français, adaptée aux écrans mobiles.

La gestion des devis et des factures est prévue pour une prochaine étape.

## Installation locale avec XAMPP

Prérequis : PHP 8.2 ou supérieur, Composer et les extensions PHP `pdo_sqlite` et `zip` activées dans `php.ini` (incluses avec XAMPP). `zip` permet à Composer d'installer les dépendances depuis leurs archives. La configuration par défaut utilise SQLite : MySQL n'est pas nécessaire.

Depuis le dossier du projet, installe les dépendances :

```powershell
composer install
```

Prépare ensuite la clé de chiffrement et la base SQLite. Le fichier de base local est ignoré par Git et sera créé sur ta machine :

```powershell
if (-not (Test-Path database\database.sqlite)) {
    New-Item -Path database\database.sqlite -ItemType File
}
php artisan key:generate
php artisan migrate --force
php artisan serve
```

Ouvre l'adresse affichée par Artisan, en général <http://127.0.0.1:8000>. Ne partage jamais le fichier `.env` : il contient la clé et les réglages privés de ta machine.

Pour utiliser MySQL/MariaDB à la place de SQLite, crée d'abord la base dans XAMPP puis configure `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD` dans `.env`. Lance ensuite `php artisan migrate --force`.

## Tests

```sh
php artisan test
```

Les tests fonctionnels utilisent une base SQLite en mémoire et ne modifient pas la base configurée dans `.env`.
