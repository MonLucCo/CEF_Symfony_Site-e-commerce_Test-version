# 📄 Schémas rapides

## 1. Introduction

Ce document regroupe des représentations schématiques des principaux workflows et structures techniques du projet. Les schémas sont volontairement textuels afin de rester compatibles avec un environnement Markdown simple et de faciliter leur réutilisation dans la documentation finale.
  
Ces schémas constituent une base visuelle simplifiée permettant de comprendre rapidement les mécanismes internes du projet. Ils facilitent la rédaction finale et servent de support de référence pour les étapes techniques majeures.

---

## 2. Application

### 2.1 Workflows de l'application

#### 2.1.1 Workflows liés à l'inscription

##### 2.1.1.1 Workflow d’inscription

```js
Utilisateur
    |
    |-- Accès à /register
    |       |
    |       |-- Saisie du formulaire
    |       |-- Validation des données
    |       |-- Création de l'utilisateur (isVerified = false)
    |       |-- Génération du lien signé
    |       |-- Envoi de l'email via Messenger
    |
    |-- Message de confirmation affiché
    |
    |-- Redirection vers la page d'accueil
```

---

##### 2.1.1.2 Workflow de vérification de l’email

###### 2.1.1.2.1 Cas nominal

```js
Utilisateur connecté
    |
    |-- Clic sur le lien signé
            |
            |-- Vérification de la signature
            |-- Vérification de l'expiration
            |-- Mise à jour isVerified = true
            |-- Message de succès
            |-- Redirection vers la page d'accueil
```

###### 2.1.1.2.2 Cas d’échec (lien compromis ou expiré)

```js
Utilisateur connecté
    |
    |-- Clic sur lien invalide
            |
            |-- Exception VerifyEmailExceptionInterface
            |-- Message d'erreur
            |-- Déconnexion
            |-- Invalidation de la session
            |-- Redirection vers la page d'accueil
```

---

##### 2.1.1.3 Architecture contrôleur → services → mailer

```js
RegistrationController
    |
    |---> EmailVerifier
    |         |
    |         |-- Génération du lien signé
    |         |-- Validation du lien
    |
    |---> MailerInterface
              |
              |-- Envoi de l'email (via Messenger si async)
```

---

##### 2.1.1.4 Séquence d’envoi d’email

```js
RegistrationController
    |
    |-- buildEmailConfirmation(User)
    |         |
    |         |-- EmailVerifier::generateSignature()
    |         |-- Construction du template
    |
    |-- MailerInterface::send()
              |
              |-- Transport async
              |-- File Messenger
              |-- Worker Messenger
              |-- SMTP (MailHog)
```

---

##### 2.1.1.5 Séquence de validation du lien

```js
EmailVerifier
    |
    |-- handleEmailConfirmation(Request, User)
            |
            |-- Extraction des paramètres
            |-- Vérification de la signature
            |-- Vérification de l'expiration
            |-- Mise à jour de l'utilisateur
            |-- Retour au contrôleur
```

---

#### 2.1.2 Workflows liés au paiement Stripe

Présentation des workflows liés au paiement Stripe et au OrderService.

##### 2.1.2.1 Workflow complet d’un achat

```js
Utilisateur (ROLE_USER + IS_VERIFIED)
    |
    |-- Accès /cart
    |-- Vérification des quantités
    |
    |-- Clic "Payer"
            |
            |-- /order/checkout
            |       |
            |       |-- OrderService::prepareCheckout()
            |       |-- StripeService::createCheckoutSession()
            |       |-- Redirection vers Stripe
            |
            |-- Paiement Stripe (mode test)
            |
            |-- Redirection vers /order/success
                    |
                    |-- OrderService::processSuccess()
                    |       |-- Mise à jour stock
                    |       |-- Filtrage items
                    |       |-- Sauvegarde last_order
                    |       |-- Vider panier
                    |
                    |-- Affichage success
                    |
                    |-- Lien "Envoyer confirmation"
                            |
                            |-- /order/send-confirmation
                                    |
                                    |-- OrderService::sendConfirmationEmail()
                                    |-- Nettoyage last_order
```

---

##### 2.1.2.2 Architecture OrderService

```js
OrderService
    |
    |-- prepareCheckout()
    |-- createStripeSession()
    |-- processSuccess()
    |-- sendConfirmationEmail()
```

---

##### 2.1.2.3 Flux Stripe Checkout

```js
Client
    |
    |-- Checkout Session (Stripe)
            |
            |-- line_items
            |-- success_url
            |-- cancel_url
            |-- locale
```

---

##### 2.1.2.4 Interaction des services

```js
OrderController
    |
    |-- OrderService
            |
            |-- CartService
            |-- StripeService
            |-- MailerInterface
            |-- EntityManagerInterface
```

---

### 2.2 Structure simplifiée de la base de données

```js
Table user
    id (int)
    email (string)
    password (string)
    isVerified (bool)
    roles (json)
    createdAt (datetime)
```

---

## 3. Environnement Windows / WSL : structure et fonctionnement

> **Un point essentiel pour comprendre l'environnement de développement du projet** :
>
> ➡️ *le projet Symfony ne vit pas seulement dans le code, mais dans un écosystème Windows + WSL + VS Code + réseau interne (localhost/WSL IP)*.

Pour que la documentation soit réellement utile lors de la rédaction finale, il faut exploiter :

- la structure des dossiers Windows vs WSL  
- comment VS Code navigue entre les deux  
- comment Symfony tourne réellement dans WSL  
- pourquoi `localhost` n’est pas le même dans Windows et dans WSL  
- comment fonctionne l’IP `172.x.x.x`  
- quels outils tournent où (MailHog, Symfony CLI, MySQL, etc.)

Cette section décrit l’organisation du développement entre Windows et WSL, ainsi que les implications sur les chemins, les outils et le réseau local.

---

### 3.1. Structure des dossiers

#### 3.1.1. Côté Windows

```js
C:\Users\<nom>\Documents\
C:\Users\<nom>\Projects\
```

Windows contient :

- les fichiers personnels  
- les outils graphiques (VS Code, navigateurs, MailHog UI)  
- l’accès aux dossiers WSL via :
  
  ```js
  \\wsl$\Ubuntu\home\<user>\
  ```

#### 3.1.2. Côté WSL (Ubuntu)

```js
/home/<user>/projects/<mon-projet-symfony>/
/etc/php/
/var/www/
/usr/bin/
```

WSL contient :

- le code Symfony  
- PHP  
- Composer  
- Symfony CLI  
- MySQL (si installé dans WSL)  
- le serveur Symfony (`symfony serve`)  
- le worker Messenger  

---

### 3.2. Fonctionnement de VS Code

VS Code est lancé depuis Windows, mais **travaille réellement dans WSL** grâce à l’extension :

```js
Remote - WSL
```

Cela permet :

- d’ouvrir un dossier Linux dans VS Code Windows  
- d’exécuter PHP, Composer, Symfony CLI dans WSL  
- d’éviter les problèmes de permissions  
- d’avoir un environnement Linux complet pour Symfony

---

### 3.3. Problème classique : localhost ≠ localhost

#### 3.3.1. Localhost dans WSL

Dans WSL :

```js
http://localhost:8000
```

→ correspond au serveur Symfony **dans WSL**.

#### 3.3.2. Localhost dans Windows

Dans Windows :

```js
http://localhost
```

→ correspond au réseau Windows, pas à WSL.

#### 3.3.3. Accéder au serveur WSL depuis Windows

Windows ne peut pas accéder à `localhost` de WSL.  
Il doit utiliser l’IP interne de WSL :

```js
ip addr show eth0
```

Exemple :

```js
172.28.219.113
```

Donc l’accès Windows se fait via :

```js
http://172.28.219.113:8000
```

---

### 3.4. Fonctionnement réseau : schéma simplifié

```js
Windows
    |
    |-- Navigateur (Chrome/Edge)
    |-- VS Code (interface)
    |-- MailHog UI (http://localhost:8025)
    |
    +--> Accès à WSL via IP 172.x.x.x
            |
            |-- PHP
            |-- Symfony CLI
            |-- Worker Messenger
            |-- Serveur Symfony
            |-- MySQL
```

---

### 3.5. Outils utilisés et où ils tournent

| Outil          | Emplacement                           | Rôle                      |
|----------------|---------------------------------------|---------------------------|
| VS Code        | Windows (interface) + WSL (exécution) | Éditeur de code           |
| Symfony CLI    | WSL                                   | Serveur local + commandes |
| PHP            | WSL                                   | Exécution du projet       |
| Composer       | WSL                                   | Gestion des dépendances   |
| MailHog (UI)   | Windows                               | Interface web             |
| MailHog (SMTP) | Windows                               | Serveur SMTP local        |
| Navigateur     | Windows                               | Tests du site             |
| MySQL          | WSL                                   | Base de données           |

---

### 3.6. Implications pour le développement

- Les commandes Symfony doivent être exécutées **dans WSL**.  
- Les fichiers doivent être stockés **dans WSL**, pas dans Windows.  
- Les tests dans le navigateur doivent utiliser **l’IP WSL**, pas `localhost`.  
- MailHog fonctionne côté Windows mais reçoit les emails envoyés depuis WSL.  
- VS Code doit être lancé via :
  
  ```js
  wsl
  code .
  ```

---

### 3.7. Résolution des problèmes courants

#### 3.7.1. Le site ne s’affiche pas dans Windows

→ utiliser l’IP WSL (`172.x.x.x`)  
→ vérifier que Symfony CLI écoute sur toutes les interfaces (`symfony serve -d`)

#### 3.7.2. VS Code n’est pas en mode WSL

→ vérifier la barre bleue en bas : `WSL: Ubuntu`  
→ sinon relancer via `code .` depuis WSL

#### 3.7.3. MailHog ne reçoit pas les emails

→ vérifier que le transport SMTP pointe vers `localhost:1025`  
→ vérifier que Messenger tourne

---

### 3.8 Conclusion

Cette section sur l'environnement clarifie :

- la séparation Windows / WSL  
- la navigation entre les deux environnements  
- les implications réseau  
- les outils utilisés et leur emplacement  
- les problèmes typiques et leurs solutions  

Elle complète les schémas du projet et aide à comprendre l’environnement de développement réel.

---
