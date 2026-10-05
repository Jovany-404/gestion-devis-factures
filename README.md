# Gestion de devis et factures

Application Laravel destinée aux indépendants et petites équipes qui veulent suivre le cycle de vente dans un seul espace : clients, catalogue, devis, acceptations, factures et paiements. Les numéros, montants, taux de TVA et coordonnées émis sur un document sont conservés dans son historique.

## Parcours métier

1. Le premier utilisateur crée l’espace de travail et devient administrateur. L’inscription publique est alors fermée.
2. L’administrateur renseigne les coordonnées de facturation de son entreprise et invite les collaborateurs.
3. L’équipe ajoute les clients et les prestations au catalogue.
4. Un commercial prépare un devis. Son numéro annuel est attribué automatiquement, les lignes reprennent les tarifs du catalogue et les taxes sont calculées côté serveur.
5. Après l’envoi du PDF au client, le commercial enregistre l’envoi et la réponse du client dans l’application.
6. Un devis accepté est converti une seule fois en facture. La facture conserve une copie des coordonnées, lignes et tarifs du devis.
7. Après règlement, un administrateur ou un comptable enregistre le paiement intégral. Les listes peuvent être exportées au format CSV.

L’action « Marquer comme envoyé » enregistre un changement de statut ; elle **n’envoie pas d’e-mail**. Le PDF est téléchargé depuis l’écran du document et doit être transmis au client par votre canal habituel. Le statut « payé » enregistre un règlement intégral ; il ne se connecte pas à un prestataire bancaire.

## Fonctionnalités

- Fiches clients avec coordonnées de facturation et historique commercial.
- Catalogue de prestations, références, unités, prix HT et taux de TVA.
- Devis modifiables tant qu’ils sont en brouillon, numérotés `DEV-AAAA-NNNN`.
- Statuts de devis : brouillon, envoyé, accepté, refusé.
- Conversion transactionnelle et unique d’un devis accepté en facture `FAC-AAAA-NNNN`.
- Factures échues signalées comme « en retard » à partir de leur date d’échéance (sans modifier leur statut d’envoi).
- Factures avec coordonnées, lignes et montants figés, échéance, statut de paiement et PDF.
- Export CSV UTF-8 (BOM) avec séparateur `;`, dates et filtres par type, statut et période.
- Génération des PDF à la demande et en tâche de fond via la file Laravel.
- Comptes individuels avec rôles administrateur, commercial et comptable.
- API JSON versionnée (`/api/v1`) authentifiée par jetons Sanctum personnels, autorisés selon le rôle.
- Spécification OpenAPI : [`docs/openapi.yaml`](./docs/openapi.yaml) ; exemples d’intégration : [`docs/api.md`](./docs/api.md).

## Prérequis

- PHP 8.2 ou ultérieur avec `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl` et `zip` activés.
- Composer 2.7+.
- MySQL/MariaDB est facultatif ; SQLite est la configuration de départ.

## Installation avec XAMPP sous Windows

Dans PowerShell, depuis le dossier du projet :

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
New-Item -ItemType File -Path database\database.sqlite -ErrorAction SilentlyContinue
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Si `.env` existe déjà, ne le remplace pas par le modèle. Vérifie que les extensions PHP `pdo_sqlite` et `zip` sont activées dans le `php.ini` utilisé par le terminal. L’application est ensuite accessible à l’adresse affichée par Artisan, généralement <http://127.0.0.1:8000>. Crée le premier compte administrateur depuis l’écran de connexion.

Dans un deuxième terminal, démarre les tâches asynchrones, notamment le pré-générateur de PDF :

```powershell
php artisan queue:work --tries=3 --timeout=120
```

Le téléchargement d’un PDF le génère aussi à la demande si le worker n’a pas encore traité le travail.

## Espace de démonstration isolé

Pour essayer l’application avec une entreprise et des comptes clients fictifs, lancez `.\scripts\Start-Demo.ps1` dans PowerShell. Cette commande crée la base séparée `database\demo.sqlite`, y applique les migrations et démarre une seconde instance à <http://127.0.0.1:8001>. Votre base habituelle et vos données ne sont pas remplacées. Si une configuration Laravel est en cache, videz-la avec `php artisan config:clear` avant de lancer le script.

Tous les noms et dossiers ci-dessous sont fictifs. Les adresses utilisent le domaine réservé `.test` et ne reçoivent pas de courriels. Ces identifiants fixes sont réservés à la base locale de démonstration ; ne les utilisez jamais sur une instance exposée en production.

**Compte entreprise — Studio Noroît (démonstration)**

- E-mail : `gestion@studio-noroit.example.test`
- Mot de passe : `AtelierDemo-2026!`

**Comptes clients**

| Client | E-mail | Mot de passe | Prestation de démonstration |
|---|---|---|---|
| Claire Dubois · Café du Canal | `claire.dubois@cafe-du-canal.example.test` | `ClaireDemo-2026!` | Identité visuelle · devis à consulter |
| Thomas Lefèvre · Brûlerie des Chartrons | `thomas.lefevre@brulerie-chartrons.example.test` | `ThomasDemo-2026!` | Site vitrine · facture envoyée |
| Sarah Benali · Yoga des Quais | `sarah.benali@yoga-des-quais.example.test` | `SarahDemo-2026!` | Visibilité locale · facture réglée |

Les comptes clients arrivent dans un espace séparé de l’interface entreprise. Ils peuvent consulter et télécharger leurs propres documents et répondre à un devis envoyé ; ils ne peuvent ni accéder aux dossiers d’autres clients, ni ouvrir les fonctions d’administration.

Le catalogue de démonstration contient aussi la maintenance mensuelle. Les modifications apportées à cet environnement restent dans `database\demo.sqlite`. Pour réinitialiser complètement la démonstration, arrêtez son serveur puis supprimez `database\demo.sqlite` et `storage\app\demo-private`; le script recréera les données au prochain démarrage.

Pour utiliser MySQL, crée une base vide dans XAMPP, puis indique `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD` dans `.env` avant de lancer les migrations.

## Configuration de déploiement

- Configurer `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false` et une clé privée via `php artisan key:generate`.
- Utiliser la base MySQL/PostgreSQL et un worker supervisé pour un déploiement multi-utilisateur.
- Garder `.env`, les jetons API et les PDF dans un stockage non public ; ne jamais committer les secrets ou les dépendances installées.
- Configurer l’envoi d’e-mails séparément avant de proposer une transmission automatique des documents.
- Adapter les mentions légales et règles fiscales aux obligations de l’entreprise avant toute émission réelle.

## API

L’API est disponible sous `/api/v1`. Créez un jeton dans **Accès API** ; sa valeur intégrale ne s’affiche qu’une seule fois. Transmettez-le dans `Authorization: Bearer <jeton>` et demandez une réponse JSON avec `Accept: application/json`. Les opérations disponibles, schémas, permissions et exemples sont décrits dans [`docs/api.md`](./docs/api.md) et [`docs/openapi.yaml`](./docs/openapi.yaml).

## Tests et contrôle des dépendances

```powershell
php artisan test
php vendor\bin\pint --test
composer validate --no-check-publish
composer check-platform-reqs
```

Les tests fonctionnels utilisent SQLite en mémoire et ne modifient pas les données locales.
