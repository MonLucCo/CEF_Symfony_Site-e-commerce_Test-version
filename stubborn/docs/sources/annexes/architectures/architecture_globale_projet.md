# 📘 architecture_globale_projet.md — Document final consolidé

## 1. Présentation générale

Projet e‑commerce Symfony conforme aux spécifications CEF, incluant :  

- catalogue produits  
- panier  
- paiement Stripe  
- authentification + vérification email  
- UX responsive  
- sécurité renforcée
- back-office

---

## 2. Architecture

### 2.1 Architecture backend

#### 2.1.1 Entités

- `Product`  
- `User`  

#### 2.1.2 Services

- `CartService`  
- `EmailVerifier`  
- `StripeService` (selon implémentation)

#### 2.1.3 Contrôleurs

- `HomeController`  
- `ProductsController`  
- `CartController`  
- `RegistrationController`  
- `OrderController`

---

### 2.2 Architecture frontend

#### 2.2.1 Templates Twig

- Base layout  
- Pages produits  
- Panier  
- Commande  
- Authentification

#### 2.2.2 CSS

- `app.css`  
- Grilles responsives  
- Composants visuels  
- Responsive global (menu, panier, commandes)

---

### 2.3 Sécurité & Vérification email

#### 2.3.1 Rôles

- `ROLE_USER`  
- `ROLE_ADMIN`

#### 2.3.2 Logique admin

- Peut tester l’emailing  
- Ne peut jamais être vérifié  
- Ne peut jamais commander  
- Messages UX spécifiques

#### 2.3.3 EmailVerifier

- URL signée  
- Expiration  
- Protection métier admin

---

### 2.4 Paiement Stripe

- Session de paiement  
- Validation du panier  
- Pages success / cancel  
- Gestion des erreurs

---

### 2.5 UX & Responsive

- Panier responsive  
- Actions responsives  
- Menu responsive  
- Pages commande responsives  
- Boutons centrés et harmonisés

---

## 3. Démarrage propre de l’application

Cette procédure garantit un environnement de développement stable, cohérent et reproductible. Elle doit être appliquée avant toute session de travail significative ou après une modification structurelle du projet.

### 3.1 Arrêt complet de l’environnement

- Arrêter le serveur Symfony.  
- Arrêter le serveur MySQL.  
- Arrêter le worker Messenger.  
- Arrêter l’outil d’emailing (Mailpit).  

### 3.2 Nettoyage de l’environnement

- Réinitialiser le cache de Symfony.  
- Régénérer l’autoload Composer.  
- (Optionnel) Nettoyer les sessions PHP pour repartir sur un état propre.  

### 3.3 Redémarrage des services

- Relancer MySQL.  
- Relancer Mailpit (SMTP + interface).  
- Relancer le serveur Symfony en mode détaché.  
- Relancer le worker Messenger pour la consommation des messages.  

### 3.4 Vérifications fonctionnelles

Après redémarrage, vérifier systématiquement :

#### 3.4.1 Sessions

- Connexion / déconnexion  
- Persistance de session  
- Comportement du panier  

#### 3.4.2 Panier

- Ajout / retrait / modification des quantités  
- Calcul des totaux  
- Vérification du stock par taille  

#### 3.4.3 Commande

- Accès au checkout  
- Redirection success / cancel  
- Cohérence du panier avant paiement  

#### 3.4.4 Emailing

- Réception des emails dans Mailpit  
- Fonctionnement du lien de vérification  
- Messages spécifiques pour les administrateurs  

#### 3.4.5 Worker Messenger

- Traitement correct des messages  
- Absence d’erreurs dans la consommation  

#### 3.4.6 Responsive

- Menu  
- Panier  
- Pages commande  
- Boutons et actions globales  

---
