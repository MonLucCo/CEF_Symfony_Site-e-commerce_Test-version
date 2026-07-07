# CEF_Symfony_Site-e-commerce_Test-version

Etablissement d'un site de e-commerce (avec système de réservation / d'achat en ligne) avec Symfony selon les spécifications du **Centre Européen de Formation** (CEF).

![Licence MIT](https://img.shields.io/badge/License-MIT-green.svg)
![Statut du dépôt](https://img.shields.io/badge/Status-finalis%C3%A9-blue)

> 🔗 [Accès au document du projet - PDF](./stubborn/docs/documentation-projet.pdf)
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

## 🧭 Installation et démarrage du projet (prise en main)

Pour que le projet puisse démarrer, il est nécessaire :

- de disposer d'un environnement applicatif (étape A)
- de cloner le dépôt (étape B)
- de configurer localement l'application (étape C)
- de démarrer l'application (étape D)
- de vérifier le bon fonctionnement (étape E)

### 🚀 Étape A - Installation de l'environnement

#### 1. Installation recommandée : **WSL2 (Ubuntu)**

L’application Symfony fonctionne **beaucoup plus efficacement sous Linux**.  
Il est donc recommandé de cloner et d’exécuter le projet **dans WSL2**.

##### Installation WSL2

```bash
wsl --install
```

Puis installer Ubuntu depuis le Microsoft Store.

---

#### 2. Installation de MySQL

Dans WSL :

```bash
sudo apt update
sudo apt install mysql-server
sudo service mysql start
```

Créer la base :

```bash
mysql -u root -p
CREATE DATABASE stubborn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

#### 3. Installation de Mailpit

Télécharger le binaire Linux :

```bash
wget https://github.com/axllent/mailpit/releases/latest/download/mailpit-linux-amd64
chmod +x mailpit-linux-amd64
sudo mv mailpit-linux-amd64 /usr/local/bin/mailpit
```

Lancer Mailpit :

```bash
mailpit
```

Interface :  
👉 <http://localhost:8025>

---

### 📥 Étape B - Clonage du dépôt

Dans WSL :

```bash
git clone https://github.com/MonLucCo/CEF_Symfony_Site-e-commerce_Test-version.git
cd CEF_Symfony_Site-e-commerce_Test-version
composer install
```

---

### ⚙️ Étape C - Configuration de l’application

Créer un fichier `.env.local` :

```ini
DATABASE_URL="mysql://root:@127.0.0.1:3306/stubborn"
MAILER_DSN=smtp://localhost:1025
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_PUBLIC_KEY=pk_test_xxx
APP_DEFAULT_LOCALE="fr"
```

> La langue d'affichage de l'application est soit le français (fr), soit l'anglais (en) selon la valeur de la variable d'environnement _APP_DEFAULT_LOCALE_.

---

### ▶️ Étape D - Lancement de l’application

#### Mode manuel

```bash
mailpit
symfony console messenger:consume async -vv
symfony server:start -d

```

> Pour charger des données dans la base MySQL du projet, utiliser le script Composer `app:fixtures`.

#### Mode automatisé (scripts Composer)

```bash
composer services:start
```

Arrêt :

```bash
composer services:stop
```

Redémarrage :

```bash
composer services:restart
```

---

### 🧪 Étape E - Lancement des tests

```bash
composer start:test
```

Ce script :

- initialise la base de test  
- charge les fixtures  
- exécute PHPUnit  

Mode verbeux :

```bash
composer start:test-verbose
```

---

## 📦 Contenu du projet

### 🧰 Fonctionnalités principales

- [x] Page d’accueil avec produits mis en avant  
- [x] Inscription + email d’activation  
- [x] Connexion / déconnexion  
- [x] Liste des produits + filtres de prix  
- [x] Fiche produit avec choix de taille  
- [x] Panier en session  
- [x] Paiement Stripe (Checkout)  
- [x] Back‑office (CRUD produits)  
- [x] Tests unitaires (panier + achat)  

---

### 🧪 Tests

Les tests PHPUnit couvrent :

- [x] Ajout / suppression d’articles dans le panier  
- [x] Calcul du total  
- [x] Simulation d’un achat Stripe (mock)  

---

### 📄 Documentation

- [x] Documentation complète du projet est fournie au format **PDF** dans le livrable final.

> Le livrable final est disponible :
>
> - 👉 [documentation-projet](./stubborn/docs/documentation-projet.pdf)
>
> Les sources Typora sont disponibles dans :
>
> - 👉 [sources de la documentation-projet](./stubborn/docs/sources/documentation-projet.md)
>
> Les annexes techniques sont disponibles dans :
>
> - 👉 [document technique du développement](./stubborn/docs/sources/annexes/)

---

## 🗂️ Historique du développement

> 🔗 [Accès aux phases](https://github.com/MonLucCo/CEF_Symfony_Site-e-commerce_Test-version/milestones/)
> 🔗 [Accès aux issues](https://github.com/MonLucCo/CEF_Symfony_Site-e-commerce_Test-version/issues/)
> 🔗 [Accès au Kanban](https://github.com/users/MonLucCo/projects/12/views/1)

---

## 🤝 Contributions

Ce dépôt est un **projet d’étude**.  
Les contributions externes ne sont **pas acceptées**.

---

## 📬 Contact

Projet réalisé dans le cadre du **Centre Européen de Formation**.  
Aucune utilisation commerciale.

---
