# CEF_Symfony_Site-e-commerce_Test-version

Etablissement d'un site de e-commerce (avec système de réservation / d'achat en ligne) avec Symfony selon les spécifications du CEF.

![Licence MIT](https://img.shields.io/badge/License-MIT-green.svg)
![Milestone Phase 2](https://img.shields.io/badge/Phase%202-Développement-green)
![Issues ouvertes](https://img.shields.io/github/issues/MonLucCo/CEF_Symfony_Site-e-commerce_Test-version)
![Statut du dépôt](https://img.shields.io/badge/Status%20dépôt-en%20développement-orange)

> 🔗 [Accès aux phases](https://github.com/MonLucCo/CEF_Symfony_Site-e-commerce_Test-version/milestones/)
> 🔗 [Accès aux issues](https://github.com/MonLucCo/CEF_Symfony_Site-e-commerce_Test-version/issues/)
> 🔗 [Accès au Kanban](https://github.com/users/MonLucCo/projects/12/views/1)
---

## 🛍️ Stubborn — Boutique en ligne (Symfony)

![Symfony](https://img.shields.io/badge/Symfony-8.0-black)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![Stripe](https://img.shields.io/badge/Stripe-Test_Mode-purple)
![Status](https://img.shields.io/badge/Project-Study_Assignment-green)

Projet réalisé dans le cadre d’un devoir du **Centre Européen de Formation**.  
L’objectif est de développer une boutique en ligne simple avec Symfony, incluant :

- Authentification (client + administrateur)  
- Catalogue produits  
- Panier en session  
- Paiement Stripe (mode test)  
- Back‑office administrateur  
- Tests unitaires  
- Documentation PDF  

---

## 🚀 Installation rapide

```bash
git clone https://github.com/MonLucCo/CEF_Symfony_Site-e-commerce_Test-version.git
cd CEF_Symfony_Site-e-commerce_Test-version
composer install
symfony server:start
```

Configurer ensuite votre fichier `.env.local` :

```js
DATABASE_URL="mysql://user:password@127.0.0.1:3306/stubborn"
MAILER_DSN=smtp://localhost
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_PUBLIC_KEY=pk_test_xxx
```

---

## 📦 Fonctionnalités principales

- [x] Page d’accueil avec produits mis en avant  
- [x] Inscription + email d’activation  
- [x] Connexion / déconnexion  
- [x] Liste des produits + filtres de prix  
- [x] Fiche produit avec choix de taille  
- [x] Panier en session  
- [x] Paiement Stripe (Checkout)  
- [ ] Back‑office (CRUD produits)  
- [ ] Tests unitaires (panier + achat)  

---

## 🧪 Tests

Les tests PHPUnit couvrent :

- [ ] Ajout / suppression d’articles dans le panier  
- [ ] Calcul du total  
- [ ] Simulation d’un achat Stripe (mock)  

---

## 📄 Documentation

- [ ] Documentation complète du projet est fournie au format **PDF** dans le livrable final.

---

## 🤝 Contributions

Ce dépôt est un **projet d’étude**.  
Les contributions externes ne sont **pas acceptées**.

---

## 📬 Contact

Projet réalisé dans le cadre du **Centre Européen de Formation**.  
Aucune utilisation commerciale.

---
