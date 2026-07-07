# 📘 architecture_mise-a-jour_issue-6.md — UX Panier & Sécurité Admin

## 🎯 Objectif  

Améliorer l’UX du panier et sécuriser la logique admin / non vérifié.

## 🏗️ Architecture mise à jour

### 1. UX du panier

- Cas admin : message spécifique  
- Cas user non vérifié : message + bouton renvoi email  
- Cas user vérifié : bouton checkout

### 2. Sécurité métier

- Admin peut tester l’emailing  
- Admin ne peut jamais être vérifié  
- EmailVerifier protège la base  
- Retour simulé pour admin

### 3. Responsive

- `.cart-table-wrapper`  
- `.cart-actions` responsive  
- `.order-actions` responsive  
- Boutons centrés  
- Menu responsive (`flex-wrap`)

---
