# API HTTP v1

L’API JSON est servie sous `/api/v1`. La spécification contractuelle complète est [`openapi.yaml`](./openapi.yaml) et peut également être récupérée dans l’application à `/docs/openapi.yaml`.

## Authentification et sécurité

1. Connectez-vous à l’application, ouvrez **Accès API**, puis créez un jeton au nom de l’intégration.
2. Copiez la valeur affichée : elle ne pourra pas être consultée une seconde fois.
3. Envoyez le jeton comme Bearer token dans l'en-tete Authorization et ajoutez Accept: application/json.
4. Révoquez les jetons inutilisés depuis **Accès API**. Les jetons d’un collaborateur sont révoqués à la suppression de son compte.

Chaque compte a ses propres droits :

| Rôle | Lecture des clients, articles et documents | Création / transition de devis | Enregistrement du paiement |
|---|---:|---:|---:|
| Administrateur | Oui | Oui | Oui |
| Commercial | Oui | Oui | Non |
| Comptable | Oui | Non | Oui |

Les jetons sont stockés sous forme hachée par Sanctum et n’accordent jamais davantage de droits que le rôle de leur propriétaire. Limite : 60 requêtes par minute et par jeton. Une erreur de validation renvoie `422` avec un objet `errors`; un jeton absent, expiré ou révoqué renvoie `401`; un rôle ou une capacité insuffisante renvoie `403`.

## Conventions

- Les montants retournés sont des chaînes décimales à deux chiffres. La devise est renvoyée sous `currency` avec chaque article et chaque document : `EUR` pour l’installation standard, `XOF` pour la démonstration Studio Noroît. Les montants XOF sont arrondis au franc dans l’interface ; dans l’API, ils restent sérialisés avec deux décimales.
- Les dates sont au format ISO `YYYY-MM-DD`; les dates de création sont ISO 8601.
- Les listes paginées utilisent une enveloppe `data` et des métadonnées `meta`.
- Les montants et taux transmis par le client ne sont **pas** acceptés comme source de vérité : lors de la création d’un devis, le serveur relit le tarif et le taux de TVA de chaque article actif.
- Les numéros de devis et facture sont attribués dans une transaction au moment de l’enregistrement, jamais fournis par le client.
- Une facture envoyée dont l’échéance est passée est présentée comme « en retard » dans l’interface et l’export ; son état stocké reste `sent` jusqu’à son paiement.
- La facture est créée depuis un devis accepté (`POST /quotes/{id}/convert`). La conversion est atomique et ne peut être faite qu’une fois.
- Marquer un document « envoyé » enregistre le statut et programme la génération du PDF ; cette opération n’envoie pas d’e-mail.

## Endpoints

| Méthode | Chemin | Droit minimal | Résultat |
|---|---|---|---|
| `GET` | `/clients?search=atelier&page=1` | `documents:read` | Clients paginés, recherche nom, société ou e-mail |
| `POST` | `/clients` | `documents:write` | Créer une fiche client (`201`) |
| `GET` | `/articles?page=1` | `documents:read` | Articles actifs et prix unitaires |
| `GET` | `/quotes?status=sent&page=1` | `documents:read` | Liste paginée des devis |
| `POST` | `/quotes` | `documents:write` | Créer un devis brouillon (`201`) |
| `GET` | `/quotes/{id}` | `documents:read` | Détails, totaux et lignes |
| `POST` | `/quotes/{id}/send` | `documents:write` | Passer le brouillon à envoyé |
| `POST` | `/quotes/{id}/accept` | `documents:write` | Accepter un devis envoyé |
| `POST` | `/quotes/{id}/reject` | `documents:write` | Refuser un devis envoyé |
| `POST` | `/quotes/{id}/convert` | `documents:write` | Convertir un devis accepté en facture (`201`) |
| `GET` | `/invoices?status=sent&page=1` | `documents:read` | Liste paginée des factures |
| `GET` | `/invoices/{id}` | `documents:read` | Détails, échéance et lignes |
| `POST` | `/invoices/{id}/send` | `documents:write` | Passer le brouillon à envoyé |
| `POST` | `/invoices/{id}/pay` | `payments:write` | Enregistrer le paiement intégral |

L’identifiant utilisé dans les routes est l’identifiant numérique du document, et non son numéro lisible. Les actions de transition invalide renvoient `422`; les routes refusent également un identifiant d’un autre type avec `404`.

## Créer un client

```http
POST /api/v1/clients
Authorization: Bearer <JETON_API>
Accept: application/json
Content-Type: application/json
```

```json
{
  "name": "Camille Martin",
  "company": "Atelier Martin",
  "email": "comptabilite@atelier-martin.example",
  "phone": "+33 6 00 00 00 00",
  "address": "12 rue des Lilas",
  "postal_code": "69001",
  "city": "Lyon"
}
```

## Créer un devis

Les articles doivent exister dans le catalogue et être actifs. `quantity` accepte une précision de deux décimales. Au moins une ligne et un client sont requis.

```http
POST /api/v1/quotes
Authorization: Bearer <JETON_API>
Accept: application/json
Content-Type: application/json
```

```json
{
  "client_id": 1,
  "issue_date": "2026-10-05",
  "valid_until": "2026-11-04",
  "notes": "Intervention en semaine.",
  "terms": "Bon pour accord avant le début de la prestation.",
  "lines": [
    { "article_id": 1, "quantity": 2 },
    { "article_id": 3, "quantity": 1.5 }
  ]
}
```

Extrait de la réponse :

```json
{
  "data": {
    "id": 27,
    "type": "quote",
    "number": "DEV-2026-0001",
    "status": "draft",
    "subtotal": "375.00",
    "tax_total": "75.00",
    "total": "450.00",
    "currency": "EUR",
    "lines": [
      {
        "description": "Prestation catalogue",
        "unit": "heure",
        "quantity": "2.00",
        "unit_price": "125.00",
        "tax_rate": "20.00",
        "subtotal": "250.00",
        "tax_amount": "50.00",
        "total": "300.00"
      },
      {
        "description": "Conseil",
        "unit": "heure",
        "quantity": "1.00",
        "unit_price": "125.00",
        "tax_rate": "20.00",
        "subtotal": "125.00",
        "tax_amount": "25.00",
        "total": "150.00"
      }
    ]
  }
}
```

## Convertir un devis accepté

```http
POST /api/v1/quotes/27/send
POST /api/v1/quotes/27/accept
POST /api/v1/quotes/27/convert
Authorization: Bearer <JETON_API>
Accept: application/json
```

La dernière réponse contient la facture créée (par exemple `FAC-2026-0001`), avec son échéance et les lignes identiques au devis accepté. Une deuxième tentative de conversion est rejetée afin d’éviter une double facturation.

## Marquer une facture payée

```http
POST /api/v1/invoices/28/send
POST /api/v1/invoices/28/pay
Authorization: Bearer <JETON_API>
Accept: application/json
```

Le paiement partiel et les pièces jointes ne sont pas gérés par cette version.

## Erreurs de validation

```json
{
  "message": "The lines.0.article_id field is required.",
  "errors": {
    "lines.0.article_id": ["L’article sélectionné est obligatoire."]
  }
}
```

Les intitulés traduits de validation peuvent dépendre de la langue des fichiers Laravel installés ; les clés de champ et statuts API restent stables. Les fichiers téléchargeables PDF et CSV sont disponibles dans l’espace web authentifié.
