# 📘 architecture_mise-a-jour_issue-1.md — Initialisation & Structure de base

## 🎯 Objectif de l’issue  

Mettre en place le projet Symfony, la structure initiale, les premières entités et les pages publiques.

## 🏗️ Architecture mise en place

### 1. Structure Symfony

- Installation du projet (`symfony new`)  
- Mise en place des dossiers :  
  - `src/Controller`  
  - `src/Entity`  
  - `templates/`  
  - `assets/styles/app.css`  
  - `public/`  

### 2. Pages publiques

- Page d’accueil (`HomeController`)  
- Page produits (`ProductsController`) — squelette  
- Page panier (`CartController`) — squelette

### 3. Entités initiales

- `Product` (nom, description, prix, tailles, stock par taille)  
- `User` (structure minimale, sans vérification email)

### 4. Décisions techniques

- Utilisation de Twig pour le rendu  
- Utilisation de Doctrine ORM  
- Mise en place d’un design simple basé sur CSS natif  
- Pas encore de responsive avancé

---
