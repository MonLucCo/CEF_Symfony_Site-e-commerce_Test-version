
# 📘 **2. ux-panier-et-commandes.md**  

**Version 1** _(Document UX — référence pour l’étape 4 et pour les futures évolutions)_

## 1. Objectif  

Décrire le comportement UX du panier et des pages commande selon les rôles et l’état du compte.

---

## 2. Comportement selon le rôle

### 🔹 Administrateur (ROLE_ADMIN)

- Ne peut pas commander  
- Message affiché :  
  _“Pas de commande pour un Administrateur. Utilisez un compte personnel.”_
- Peut tester l’emailing  
- Compte _ADMIN_ Ne peut jamais être vérifié (protection métier dans EmailVerifier)  
- Messages flash spécifiques lors de l’envoi et de l’utilisation du lien

### 🔹 Utilisateur non vérifié (ROLE_USER + !IS_VERIFIED)

- Ne peut pas commander  
- Message affiché :  
  _“Vous devez vérifier votre inscription par email avant de pouvoir effectuer une commande.”*  
- Bouton : *“Renvoyer l’email d’inscription”_
- Redirection + flash adaptés

### 🔹 Utilisateur vérifié (ROLE_USER + IS_VERIFIED)

- Accès au bouton _“Finaliser ma commande”_
- Checkout normal

---

## 3. Responsive du panier

### Tableau

- Encapsulé dans `.cart-table-wrapper`  
- Scroll horizontal sur mobile  
- Titres centrés  
- Colonnes prix / total alignées à droite  
- Boutons centrés et élargis

### Actions globales

- `.cart-actions` passe en colonne sur mobile  
- `flex-wrap: wrap`  
- `justify-content: space-around`

---

## 4. Pages commande (success / cancel)

- Titres centrés  
- Boutons espacés  
- Actions en colonne sur mobile  
- Responsive cohérent avec le panier  

---
