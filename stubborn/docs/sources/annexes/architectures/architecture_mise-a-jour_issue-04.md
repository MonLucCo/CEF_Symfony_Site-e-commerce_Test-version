# 📘 architecture_mise-a-jour_issue-4.md — Authentification & Vérification email

## 🎯 Objectif  

Mettre en place l’inscription, la connexion, la vérification email et les rôles.

## 🏗️ Architecture mise à jour

### 1. `RegistrationController`

- Inscription  
- Envoi email de vérification  
- Gestion des flash messages

### 2. `EmailVerifier`

- Génération d’URL signée  
- Validation du lien  
- Mise à jour de `isVerified`

### 3. Sécurité

- Ajout des rôles :  
  - `ROLE_USER`  
  - `ROLE_ADMIN`  
- Configuration de `security.yaml`

### 4. Outils

- Mailpit pour l’emailing  
- Worker Messenger pour la consommation des messages

---
