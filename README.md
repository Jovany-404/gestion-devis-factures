# Gestion de devis et factures

Application web Laravel pour centraliser les clients et préparer la gestion des devis et factures.

## Fonctionnalités

- Tableau de bord avec le nombre de clients et les fiches récemment créées.
- Carnet clients : création, consultation, modification et suppression.
- Recherche par nom, entreprise, adresse e-mail ou ville.
- Interface en français, adaptée aux écrans mobiles.

La gestion des devis et des factures est prévue pour une prochaine étape.

## Installation locale avec XAMPP

Prérequis : PHP 8.2 ou supérieur, Composer et MySQL/MariaDB (inclus avec XAMPP).

Depuis le dossier du projet :

```sh
composer install
```

Configure ensuite la base de données dans `.env` (`DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD`), puis lance :

```sh
php artisan key:generate
php artisan migrate
php artisan serve
```

Ouvre l'adresse affichée par Artisan, en général <http://127.0.0.1:8000>.

## Tests

```sh
php artisan test
```

Les tests fonctionnels utilisent une base SQLite en mémoire et ne modifient pas la base configurée dans `.env`.
