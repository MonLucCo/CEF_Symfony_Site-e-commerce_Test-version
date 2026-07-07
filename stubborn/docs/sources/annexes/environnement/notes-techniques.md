# 📄 Notes techniques

## 1. Introduction

Ce document regroupe les éléments techniques essentiels rencontrés lors du développement du projet. Il constitue une base de référence interne permettant de comprendre les mécanismes clés utilisés dans l’application, sans entrer dans des détails superflus. Il n’est pas destiné à être intégré au dépôt final.

Les étapes de l’issue‑3 concernent :

- installation  
- configuration  
- mise en place du contrôleur  
- mise en place du service EmailVerifier  
- configuration du Mailer  
- configuration du Messenger  
- tests manuels  
- vérification du workflow  
- corrections successives  
- décisions techniques prises en cours de route  

---

## 2. Gestion des traductions (i18n)

### 2.1. Locale par défaut

La locale par défaut est définie via une variable d’environnement :

```js
APP_DEFAULT_LOCALE=fr
```

Cette valeur est injectée dans `framework.yaml` :

```yaml
framework:
    default_locale: '%env(APP_DEFAULT_LOCALE)%'
```

### 2.2. Fallbacks

Les fallbacks sont définis statiquement dans `translation.yaml` :

```yaml
translator:
    fallbacks: ['fr', 'en']
```

Cette approche est retenue en raison des limitations de Symfony concernant l’injection de tableaux via des variables d’environnement.

### 2.3. Arborescence des fichiers

Les fichiers de traduction sont placés dans :

```bash
translations/
    messages.fr.yaml
    messages.en.yaml
    validators.fr.yaml
    validators.en.yaml
```

---

## 3. Gestion des emails et du système de vérification

### 3.1. MailHog

MailHog est utilisé comme serveur SMTP local pour capturer les emails de test.  
Accès via : `http://localhost:8025`.

### 3.2. Envoi d’email

L’envoi est effectué via le composant Mailer de Symfony.  
Le contrôleur délègue la construction de l’email au service `EmailVerifier`.

### 3.3. Lien signé

Le lien de vérification est généré via `VerifyEmailHelperInterface`.  
Il contient :

- un identifiant utilisateur  
- l’email  
- une signature cryptographique  
- une date d’expiration  

### 3.4. Durée d’expiration

La durée retenue pour le démonstrateur est de **5 minutes**.  
Pour un système opérationnel, une durée de **15 minutes** est recommandée.

---

## 4. Worker Messenger

### 4.1. Utilisation

Le worker Messenger est utilisé pour traiter les emails en mode asynchrone :

```bash
symfony console messenger:consume async -vv
```

### 4.2. File d’attente

Les messages sont envoyés dans le transport `async`.  
Le worker doit être actif pour que les emails soient réellement envoyés.

---

## 5. Architecture du contrôleur Registration

### 5.1. Découpage

Le contrôleur `RegistrationController` délègue :

- la construction du mail → `EmailVerifier`  
- l’envoi du mail → `MailerInterface`  
- la validation du lien → `EmailVerifier`  

### 5.2. Workflow de vérification

En cas d’échec de validation du lien :

- un message d’erreur est affiché  
- l’utilisateur est déconnecté  
- la session est invalidée  
- redirection vers la page d’accueil  

Cette approche évite tout renvoi automatique d’email en cas de compromission.

---

## 6. Base de données MySQL

### 6.1. Vérification des données

Les tests nécessitent parfois la suppression ou la réinitialisation des utilisateurs :

```bash
DELETE FROM user;
```

### 6.2. Champ isVerified

Le champ `isVerified` est mis à jour uniquement après validation du lien signé.

---

## 7. Back-office minimal

### 7.1. Objectif

Le back-office est limité au strict nécessaire pour :

- visualiser les utilisateurs  
- vérifier l’état de vérification  
- manipuler les données pour les tests  

Aucune mise en forme graphique n’est recherchée.

---

## 8. Conclusion

Ces notes techniques constituent un support interne permettant de comprendre les mécanismes essentiels du projet. Elles facilitent la rédaction finale et la relecture du code sans alourdir le dépôt principal.

---

## 9. Historique technique de l’issue‑3

Cette section retrace les principales étapes techniques rencontrées lors du développement de l’issue‑3, afin de conserver une vision claire des décisions prises et des ajustements réalisés.

### 9.1. Mise en place initiale

- Création du contrôleur `RegistrationController`.  
- Génération du formulaire d’inscription via `make:registration-form`.  
- Mise en place du service `EmailVerifier` pour la gestion des liens signés.  
- Configuration du Mailer et vérification du bon fonctionnement via MailHog.

> MailHog a été choisi de préférence à Mailtrap car il permet en local une validation des emails.  
> Mailtrap est préférable pour s'assurer du bon fonctionnement SMTP.

### 9.2. Configuration du système de vérification

- Intégration du composant `symfonycasts/verify-email-bundle`.  
- Génération des URLs signées avec expiration.  
- Définition d’une durée d’expiration courte pour les tests (5 minutes).  
- Validation du fonctionnement du lien en conditions normales.

### 9.3. Gestion des erreurs et des cas limites

- Tests de modification manuelle du lien (signature altérée).  
- Tests de lien expiré.  
- Tests d’accès sans authentification.  
- Mise en place d’un comportement sécurisé en cas d’échec :  
  - affichage d’un message d’erreur,  
  - déconnexion de l’utilisateur,  
  - invalidation de la session,  
  - redirection vers la page d’accueil.

### 9.4. Tests manuels avec MailHog

- Vérification de la réception des emails.  
- Vérification du contenu HTML et du lien signé.  
- Tests successifs de clics sur le lien :  
  - avant expiration,  
  - après expiration,  
  - après modification,  
  - après validation.

### 9.5. Ajustements du workflow

- Simplification du comportement en cas de lien compromis.  
- Suppression du renvoi automatique d’email pour éviter les risques de compromission.  
- Conservation d’un flux clair :  
  - succès → validation,  
  - échec → déconnexion + retour à l’accueil.

### 9.6. Décisions techniques finales

- Conservation des fallbacks de traduction en configuration statique.  
- Utilisation d’une variable d’environnement uniquement pour la locale par défaut.  
- Mise en place d’un worker Messenger pour l’envoi asynchrone des emails.  
- Adoption d’un back-office minimal pour les tests.  
- Documentation interne stockée dans un dossier non versionné.

---
