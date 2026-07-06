# Tests-Unitaires_issue-8.md — Panier, Achat, Sécurité & Démarrage automatisé

Ce document **Tests-Unitaires_issue-8.md** est la vue d’ensemble de :

- la **logique de test**  
- l’**architecture des tests**  
- l’**intégration Stripe en mode test**  
- le **workflow d’exécution via Composer**.

**Synthèse :**

- **HOM** : garantit la cohérence des pages publiques.  
- **SEC** : garantit la sécurité des routes sensibles.  
- **CRT** : garantit la fiabilité du panier et de la logique métier.  
- **ACH** : garantit le flux d’achat complet, y compris Stripe et l’email de confirmation.  
- Les scripts Composer orchestrent **base de test + fixtures + exécution des tests**.

---

## 1. Objectif de l’issue‑8

Mettre en place une **base complète de tests unitaires et fonctionnels** couvrant :

- **HOM** : pages d’accueil / navigation  
- **SEC** : sécurité, rôles, vérification email  
- **CRT** : panier (Cart) et logique métier  
- **ACH** : achat, Stripe, success/cancel, email de confirmation  

L’issue‑8 est le **socle de validation automatique** du projet, exécuté via les scripts Composer.

---

## 2. Architecture globale des tests

**Dossiers :**

- **`tests/Functional/`**  
  - `HomeTest` (HOM)  
  - `SecurityTest` (SEC)  
  - `CartTest` (CRT)  
  - `OrderTest` (ACH)  
- **`tests/Fixtures/`**  
  - Fixtures utilisateurs (user, admin, unverified)  
  - Fixtures produits (stock, tailles, prix)  

**Base commune :**

- **`WebTestCaseBase`**  
  - Helpers de connexion :  
    - **`loginAsUser()`**, **`loginAsAdmin()`**, **`loginAsUnverifiedUser()`**  
  - Helpers panier :  
    - **`addProduct()`**, **`decreaseProduct()`**, **`removeProduct()`**, **`clearCart()`**  
    - **`readCartTable()`**, **`readCartTotal()`**  
  - Helper i18n :  
    - **`t(string $key, array $params = [], ?string $domain = null)`**  
      pour assertions sur les textes traduits  
  - Helpers de navigation :  
    - **`visit()`**, etc.

---

## 3. Groupes de tests

### 3.1 HOM — Tests “Home” / Navigation

**Objectif :** vérifier les pages publiques et la navigation de base.

- **Pages testées :**
  - `/` (accueil)  
  - `/products` (liste produits)  
  - `/product/{id}` (fiche produit)  
- **Vérifications :**
  - présence des titres  
  - présence des liens principaux  
  - cohérence du menu  
  - affichage des produits (grille, images, prix)

---

### 3.2 SEC — Tests “Sécurité”

**Objectif :** valider les règles de sécurité et de vérification email.

- **Cas testés :**
  - accès aux routes protégées sans être connecté → redirection  
  - accès admin / non‑admin au back‑office  
  - comportement du compte non vérifié (`/account/not-verified`)  
  - logique de vérification email (`/verify/email`)  
- **Rôles :**
  - `ROLE_USER`  
  - `ROLE_ADMIN`  
  - `IS_VERIFIED` (via Voter / propriété User)

---

### 3.3 CRT — Tests “Cart” (Panier)

**Objectif :** valider toute la logique du panier.

- **Routes :**
  - `/cart`  
  - `/cart/add`  
  - `/cart/decrease`  
  - `/cart/remove`  
  - `/cart/clear`  
- **Scénarios :**
  - ajout de produit avec taille  
  - diminution jusqu’à 0  
  - suppression d’une ligne  
  - vidage complet du panier  
  - calcul du total  
- **UX :**
  - messages flash (warning, success)  
  - affichage des stocks par taille  
  - comportement admin / non‑vérifié / vérifié (messages spécifiques)

---

### 3.4 ACH — Tests “Achat” (Order + Stripe)

**Objectif :** valider le flux d’achat complet.

- **ACH‑01** : contrôle d’accès à `/order/checkout`  
  - non connecté → login / home  
  - admin → refus / redirection admin  
  - user non vérifié → `/account/not-verified`
- **ACH‑02** : panier vide → impossible de commander  
- **ACH‑03** : quantités invalides → checkout bloqué  
- **ACH‑04** : création PaymentIntent Stripe  
  - `goToCheckout()` via UI  
  - redirection HTTP  
  - `Location` commence par `https://checkout.stripe.com`
- **ACH‑05** : retour success → panier vidé + email envoyé  
  - `simulateStripeSuccess()`  
  - page success affichée  
  - clic sur bouton email (`data-test="order-btn-email"`)  
  - redirection `/products` + flash success  
  - panier vide
- **ACH‑06** : retour cancel → panier intact  
  - `simulateStripeCancel()`  
  - panier toujours présent

---

## 4. Intégration Stripe en mode test

### 4.1 Service `StripeService`

- Méthode : **`createCheckoutSession(array $lineCartItems): Session`**  
- En **mode test** (`APP_ENV=test`) :

```php
return Session::constructFrom([
    'id' => 'cs_test_fake',
    'url' => 'https://checkout.stripe.com/test-session',
]);
```

- En **dev/prod** : appel réel à `Session::create([...])`.

### 4.2 Logique testée

- ACH‑04 vérifie la **redirection** vers Stripe (URL)  
- ACH‑05 / ACH‑06 vérifient les **retours success/cancel** côté application  
- Aucun test ne tente d’automatiser la page Stripe elle‑même (hors périmètre).

---

## 5. Scripts Composer — Workflow de tests

Depuis `composer.json` :

- **`app:fixtures`**  
  - charge les fixtures de l'application (`--group=dev`)
  
- **`test:init-db`**  
  - supprime la base de test  
  - recrée le schéma (`doctrine:schema:create --env=test`)
  
- **`test:fixtures`**  
  - charge les fixtures de test (`--group=test`)
  
- **`test:run`**  
  - `@test:init-db`  
  - `@test:fixtures`  
  - `php bin/phpunit`
  
- **`test:run-verbose`**  
  - idem, avec `php bin/phpunit --testdox`
  
- **`services:start` / `services:stop` / `services:restart`**  
  - gestion MySQL  
  - lancement Mailpit (SMTP 1025, UI 8025)  
  - lancement des workers Messenger  
  - démarrage du serveur Symfony
  
- **`start:test` / `start:test-verbose`**  
  - restart des services  
  - exécution des tests (standard ou testdox)

> 👉 Le script `app:fixtures` permet de charger les données dans la base de l'application.
>
> 👉 Les scripts `start:test` / `start:test-verbose` permettent de démarrer l'application avec les tests intégrés.
>
> 👉 Ces scripts rendent les tests **répétables, automatisés, traçables** et intégrés au démarrage de l’application.

---
