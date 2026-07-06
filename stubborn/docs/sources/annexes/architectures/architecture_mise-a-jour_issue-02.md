# 📘 architecture_mise-a-jour_issue-2.md — Panier & Logique métier

## 🎯 Objectif  

Créer un panier fonctionnel avec gestion des quantités, tailles et stock.

## 🏗️ Architecture mise à jour

### 1. Service `CartService`

- Stockage du panier en session  
- Méthodes :  
  - `add()`  
  - `decrease()`  
  - `remove()`  
  - `clear()`  
  - `getTotal()`  
  - `getDetailedCart()`  

### 2. Mise à jour de `Product`

- Ajout de `getStockForSize()`  
- Gestion du stock par taille

### 3. Routes ajoutées

- `app_cart_add`  
- `app_cart_decrease`  
- `app_cart_remove`  
- `app_cart_clear`  
- `app_cart_index`

### 4. Templates

- `cart/index.html.twig` (version initiale)

---
