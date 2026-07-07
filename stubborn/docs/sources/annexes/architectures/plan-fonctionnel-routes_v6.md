# 📄 plan-fonctionnel-routes.md

**Version 6 :** _conforme sujet du CEF + intégration complète du panier + paiement Stripe + tests unitaires_

## 1. Introduction

Ce document présente **la cartographie fonctionnelle des routes** de l’application _Stubborn_, conformément au sujet du CEF.  
Il sert de référence pour :

- structurer les contrôleurs  
- définir les responsabilités fonctionnelles  
- établir les règles de sécurité (issue‑3, étape 9)  
- préparer les tests fonctionnels  
- documenter l’architecture finale  

---

## 2. Catégories de routes

L’application comporte cinq catégories :

- **Routes publiques** : accessibles sans authentification  
- **Routes utilisateur** : nécessitent un compte connecté  
- **Routes utilisateur vérifié** : nécessitent un compte activé par email  
- **Routes administrateur** : réservées au back‑office  
- **Routes techniques** : utilisées par Symfony ou Stripe

---

## 3. Objectifs de sécurité

- Protéger le back‑office (ROLE_ADMIN)  
- Protéger le panier et le paiement (ROLE_USER)  
- Protéger le paiement Stripe (ROLE_USER + IS_VERIFIED)  
- Laisser les routes publiques accessibles  
- Centraliser la logique “compte vérifié” via un Voter  
- Assurer des redirections cohérentes (login, compte non activé)

---

## 4. Arborescence fonctionnelle des routes

### 🌐 4.1 Routes publiques

#### `/` — Accueil  

- Présentation Stubborn  
- Produits mis en avant  
- Menu dynamique  
**Accès :** Public  
**Contrôleur :** `HomeController::index`
**Nom de route :** `app_home`
**Catégorie :** Public

---

#### `/login` — Connexion  

- Formulaire de connexion  
- Lien vers inscription  
**Accès :** Public  
**Contrôleur :** `SecurityController::login`
**Nom de route :** `app_login`
**Catégorie :** Public

---

#### `/register` — Inscription  

- Formulaire d’inscription  
- Envoi email de confirmation  
**Accès :** Public  
**Contrôleur :** `RegistrationController::register`
**Nom de route :** `app_register`
**Catégorie :** Public

---

#### `/register/resend` — Renvoi d'un email de confirmation  

- Envoi email de confirmation  
**Accès :** Public  
**Contrôleur :** `RegistrationController::resendVerificationEmail`
**Nom de route :** `app_register_resend`
**Catégorie :** Public

---

#### `/verify/email` — Activation du compte  

- Activation via lien email  
- Connexion automatique  
**Accès :** Public (sécurisé : signature HMAC)  
**Contrôleur :** `RegistrationController::verifyUserEmail`
**Nom de route :** `app_verify_email`
**Catégorie :** Technique

---

#### `/products` — Liste des produits  

- Affichage de tous les sweat-shirts  
- Filtre par prix  
**Accès :** Public  
**Contrôleur :** `ProductsController::index`
**Nom de route :** `app_products`
**Catégorie :** Public

---

#### `/product/{id}` — Fiche produit  

- Affichage d’un sweat-shirt  
- Choix de la taille  
- Ajout au panier  
**Accès :** Public  
**Contrôleur :** `ProductController::show`
**Nom de route :** `app_product_show`
**Catégorie :** Public

---

### 👤 4.2 Routes utilisateur (ROLE_USER)

#### `/account/not-verified` — Compte non activé  

- Page d’information  
- Renvoi email  
**Accès :** ROLE_USER  
**Contrôleur :** `AccountController::notVerified`
**Nom de route :** `app_account_not_verified`
**Catégorie :** Utilisateur

---

#### `/cart` — Panier  

- Liste des articles  
- Suppression  
- Total  
- Bouton “Finaliser ma commande”  
**Accès :** ROLE_USER  
**Contrôleur :** `CartController::index`
**Nom de route :** `app_cart_index`
**Catégorie :** Utilisateur

---

#### `/cart/add` — Ajouter au panier l'item `{id}/{size}`  

- Ajoute quantité au panier
**Accès :** ROLE_USER  
**Contrôleur :** `CartController::add`
**Nom de route :** `app_cart_add`
**Catégorie :** Utilisateur

---

#### `/cart/remove` — Retirer du panier l'item `{id}/{size}`

- Supprime du panier
**Accès :** ROLE_USER  
**Contrôleur :** `CartController::remove`
**Nom de route :** `app_cart_remove`
**Catégorie :** Utilisateur

---

#### `/cart/decrease` — Diminuer du panier l'item  `{id}/{size}`

- Diminue quantité du panier
**Accès :** ROLE_USER  
**Contrôleur :** `CartController::decrease`
**Nom de route :** `app_cart_decrease`
**Catégorie :** Utilisateur

---

#### `/cart/clear` — Vider le panier

- Vide le panier
**Accès :** ROLE_USER  
**Contrôleur :** `CartController::clear`
**Nom de route :** `app_cart_clear`
**Catégorie :** Utilisateur

---

### 👤 4.3 Routes utilisateur vérifié (ROLE_USER + IS_VERIFIED)

#### `/order/checkout` — Session de Paiement Stripe  

- Simulation paiement Stripe
- Validation commande  
**Accès :** ROLE_USER + IS_VERIFIED  
**Contrôleur :** `OrderController::checkout`
**Nom de route :** `app_order_checkout`
**Catégorie :** Utilisateur vérifié

---

#### `/order/success` — Paiement réussi  

**Accès :** ROLE_USER + IS_VERIFIED  
**Contrôleur :** `OrderController::success`
**Nom de route :** `app_order_success`
**Catégorie :** Utilisateur vérifié

---

#### `/order/cancel` — Paiement annulé  

**Accès :** ROLE_USER + IS_VERIFIED  
**Contrôleur :** `OrderController::cancel`
**Nom de route :** `app_order_cancel`
**Catégorie :** Utilisateur vérifié

---

#### `/order/send-confirmation` — Envoi d'un email de confirmation  

- Envoi d'un email de confirmation de la commande et du paiement
**Accès :** ROLE_USER + IS_VERIFIED  
**Contrôleur :** `OrderController::sendConfirmation`
**Nom de route :** `app_order_send_confirmation`
**Catégorie :** Utilisateur vérifié

---

### 🛠 4.4 Routes administrateur (ROLE_ADMIN)

#### `/admin` — Back-office  

- Liste des produits  
- Formulaires CRUD  
**Accès :** ROLE_ADMIN  
**Contrôleur :** `AdminController::index`
**Nom de route :** `app_admin`
**Catégorie :** Administrateur

---

#### `/admin/product/*` — CRUD produits  

- Ajout  
- Modification  
- Suppression  
**Accès :** ROLE_ADMIN  
**Contrôleur :** `AdminController::*`
**Nom de route :** `app_admin_product_*`
**Catégorie :** Administrateur

---

### 🔧 4.5 Routes techniques

#### `/logout`  

- Déconnexion  
**Accès :** Connecté  
**Contrôleur :** Géré par Symfony `SecurityController::logout`
**Nom de route :** `app_logout`
**Catégorie :** Technique

---

## 5. Schéma visuel (ASCII)

### 5.1 Mapping Routes - Accès

```bash
/
├── login
├── logout (connecté : ROLE_USER, ROLE_ADMIN)
├── register
│   └── register/resend
├── verify/email (clé : signature cryptographique)
├── products
├── product/{id}
├── account/not-verified (ROLE_USER)
├── cart (ROLE_USER)
│   ├── add
│   ├── decrease
│   ├── remove
│   └── clear
├── order (ROLE_USER + IS_VERIFIED)
│   ├── checkout
│   ├── success
│   ├── cancel
│   └── send-confirmation
└── admin (ROLE_ADMIN)
    └── product/*
```

### 5.2 Mapping - Accès - Routes

```bash
# ROUTES PUBLIQUES
Public
│
├── /
├── /login
├── /register
│   └── register/resend
├── /products
├── /product/{id}
└── /verify/email        (Technique – signature cryptographique)

# ROUTES UTILISATEUR (ROLE_USER)
ROLE_USER
│
├── /logout              (Technique – session)
├── cart
│   ├── add
│   ├── decrease
│   ├── remove
│   └── clear
└── /account/not-verified

# ROUTES UTILISATEUR VÉRIFIÉ (ROLE_USER + IS_VERIFIED)
ROLE_USER + IS_VERIFIED
│
├── /logout              (Technique – session)
└── /order
    ├── /order/checkout
    ├── /order/success
    ├── /order/cancel
    └── /order/send-confirmation

# ROUTES ADMINISTRATEUR (ROLE_ADMIN)
ROLE_ADMIN
│
├── /logout              (Technique – session)
└── /admin
    └── /admin/product/*

# ROUTES TECHNIQUES
Technique
│
├── /logout              (connecté : ROLE_USER, ROLE_ADMIN)
└── /verify/email        (publique : signature cryptographique)
```

---

## 6. Tableau de synthèse

| Route                        | Rôle               | Accès                   | Catégorie           | Méthode       |
|------------------------------|--------------------|-------------------------|---------------------|---------------|
| `/`                          | Accueil            | Public                  | Public              | GET           |
| `/login`                     | Connexion          | Public                  | Public              | GET/POST      |
| `/register`                  | Inscription        | Public                  | Public              | GET/POST      |
| `/register/resend`           | Renvoi Email       | Public                  | Public              | POST          |
| `/products`                  | Liste produits     | Public                  | Public              | GET           |
| `/product/{id}`              | Fiche produit      | Public                  | Public              | GET           |
| `/account/not-verified`      | Compte non activé  | ROLE_USER               | Utilisateur         | GET           |
| `/cart`                      | Panier             | ROLE_USER               | Utilisateur         | GET           |
| `/cart/add`                  | Ajouter panier     | ROLE_USER               | Utilisateur         | POST          |
| `/cart/decrease`             | Diminuer panier    | ROLE_USER               | Utilisateur         | POST          |
| `/cart/remove`               | Retirer panier     | ROLE_USER               | Utilisateur         | POST          |
| `/cart/clear`                | Vider panier       | ROLE_USER               | Utilisateur         | POST          |
| `/order/checkout`            | Paiement           | ROLE_USER + IS_VERIFIED | Utilisateur vérifié | GET           |
| `/order/success`             | Paiement réussi    | ROLE_USER + IS_VERIFIED | Utilisateur vérifié | GET           |
| `/order/cancel`              | Paiement annulé    | ROLE_USER + IS_VERIFIED | Utilisateur vérifié | GET           |
| `/order/send-confirmation`   | Email confirmation | ROLE_USER + IS_VERIFIED | Utilisateur vérifié | POST          |
| `/admin`                     | Back-office        | ROLE_ADMIN              | Administrateur      | GET           |
| `/admin/product/*`           | CRUD produits      | ROLE_ADMIN              | Administrateur      | GET/POST      |
| `/logout`                    | Déconnexion        | ROLE_USER / ROLE_ADMIN  | Technique           | GET (Symfony) |
| `/verify/email`              | Activation         | Public (sécurisé)       | Technique           | GET           |

---

## 7. Conclusion

Ce document constitue :

- la **référence** des routes du projet  
- la base de l’étape 9 (protection des routes)  
- un support pour la documentation finale PDF  
- un guide pour les tests fonctionnels  
- un outil de cohérence pour tout le développement  

Il est maintenant **complet, conforme au sujet du CEF, simple à relire, et parfaitement exploitable**.

---
