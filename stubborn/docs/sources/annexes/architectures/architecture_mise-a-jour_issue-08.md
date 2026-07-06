# 📘 architecture_mise-a-jour_issue-8.md — Tests fonctionnels & Intégration Stripe (étape 3)

## 🎯 Objectif de l’issue‑8  

Mettre en place **l’ensemble des tests fonctionnels du panier et du processus d’achat**, incluant :

- la gestion du panier  
- la validation des quantités  
- la sécurité utilisateur / admin / non‑vérifié  
- l’intégration Stripe en mode test  
- les retours success / cancel  
- l’envoi d’email de confirmation  
- la stabilisation du socle de tests

Cette issue constitue la **base de validation automatisée** du projet.

---

## 🏗️ Architecture mise à jour

### 1. Consolidation de `WebTestCaseBase`

- Ajout des helpers du panier :  
  `addProduct()`, `decreaseProduct()`, `removeProduct()`, `clearCart()`, `readCartTable()`, `readCartTotal()`
- Ajout des helpers de connexion :  
  `loginAsUser()`, `loginAsAdmin()`, `loginAsUnverifiedUser()`
- Amélioration du helper i18n `t()` :  
  - support des paramètres  
  - support multi‑domaines  
  - cohérence avec les traductions Twig
- Centralisation des helpers communs aux tests fonctionnels

---

### 2. Tests fonctionnels d’achat (`OrderTest`)

Implémentation complète des tests ACH‑01 → ACH‑06 :

| Test   | Objectif                              | Résultat |
|--------|---------------------------------------|----------|
| ACH‑01 | Contrôle d’accès au checkout          | OK       |
| ACH‑02 | Panier vide → redirection             | OK       |
| ACH‑03 | Quantités invalides → redirection     | OK       |
| ACH‑04 | Création session Stripe → redirection | OK       |
| ACH‑05 | Retour success → email + panier vidé  | OK       |
| ACH‑06 | Retour cancel → panier intact         | OK       |

Les helpers locaux `goToCheckout()`, `simulateStripeSuccess()`, `simulateStripeCancel()` ont été ajoutés.

---

### 3. StripeService — Mode test

Mise en place d’un **mock Stripe officiel** via :

```php
Session::constructFrom([
    'id' => 'cs_test_fake',
    'url' => 'https://checkout.stripe.com/test-session',
]);
```

Avantages :

- aucun appel API réel  
- typage strict respecté  
- tests 100 % déterministes  
- redirection Stripe testable  
- cohérence avec le SDK Stripe  

---

### 4. Templates mis à jour

- Ajout des `data-test` pour les tests fonctionnels  
- Nettoyage des styles inline (déplacés dans `app.css`)  
- Amélioration de la cohérence visuelle (panier, success, cancel)

---

### 5. Résultat global

L’issue‑8 fournit :

- un socle de tests stable  
- une architecture de test professionnelle  
- une intégration Stripe maîtrisée  
- une UX cohérente  
- une base solide pour la documentation finale PDF

---
