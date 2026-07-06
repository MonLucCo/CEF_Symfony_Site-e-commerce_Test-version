# 📘 architectur_mise-a-jour_issue-5.md — Commande & Stripe

## 🎯 Objectif  

Mettre en place le paiement Stripe et les pages commande.

## 🏗️ Architecture mise à jour

### 1. `OrderController`

- Checkout Stripe  
- Success  
- Cancel  

### 2. Intégration Stripe

- Service Stripe  
- Session de paiement  
- Validation du panier avant paiement

### 3. Templates

- `order/success.html.twig`  
- `order/cancel.html.twig`

### 4. Décisions techniques

- Pas d’entité Order (hors périmètre CEF)  
- Validation stricte du panier  
- Gestion des erreurs Stripe

---
