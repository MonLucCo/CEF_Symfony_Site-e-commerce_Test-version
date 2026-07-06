# 📄 Décisions architecturales

## 1. Introduction  

Ce document présente les principales décisions architecturales prises durant le développement du projet.  
Il expose les choix techniques, les alternatives envisagées, les raisons des arbitrages, ainsi que les impacts sur la structure globale du système.  
L’objectif est de conserver une trace claire et synthétique des orientations retenues.

---

## 2. Structure générale du projet

### 2.1. Architecture MVC de Symfony  

Le projet repose sur l’architecture MVC native de Symfony :

- **Modèle** : entités Doctrine et gestion de la base MySQL  
- **Vue** : templates Twig minimalistes  
- **Contrôleur** : logique métier centralisée dans `RegistrationController` et services associés  

Ce choix garantit une séparation claire des responsabilités et une maintenabilité optimale.

### 2.2. Organisation des services  

La logique complexe (génération de lien signé, validation, envoi d’email) a été externalisée dans des services dédiés :

- `EmailVerifier`  
- `MailerInterface` (injection Symfony)  

Ce découpage évite la surcharge du contrôleur et améliore la testabilité.

---

## 3. Gestion des emails et du workflow de vérification

### 3.1. Utilisation du bundle VerifyEmail  

Le bundle `symfonycasts/verify-email-bundle` a été retenu pour :

- la génération sécurisée de liens signés  
- la gestion de l’expiration  
- la validation cryptographique  
- la simplicité d’intégration  

Alternative envisagée : implémentation manuelle via `UrlSigner` → rejetée pour éviter une complexité inutile.

### 3.2. Durée d’expiration courte  

Une durée de **5 minutes** a été retenue pour le démonstrateur.  
Motivations :

- rapidité des tests  
- démonstration claire du mécanisme d’expiration  
- cohérence avec un environnement pédagogique  

Durée recommandée en production : **15 minutes**.

### 3.3. Suppression du renvoi automatique d’email  

Décision importante :  
➡️ *aucun renvoi automatique d’email en cas d’échec de validation.*

Motivations :

- éviter les risques de compromission  
- simplifier le workflow  
- garantir un comportement prévisible  
- renforcer la sécurité  

---

## 4. Gestion des erreurs et sécurité

### 4.1. Comportement en cas de lien compromis  

En cas d’erreur (signature altérée, expiration, paramètres invalides) :

- message d’erreur  
- déconnexion immédiate  
- invalidation de la session  
- redirection vers la page d’accueil  

Motivations :

- approche “zéro confiance”  
- éviter les fuites d’information  
- simplifier la logique métier  
- garantir un état propre après incident

### 4.2. Accès restreint à /verify/email  

La route `/verify/email` impose :

```js
IS_AUTHENTICATED_FULLY
```

Motivations :

- empêcher la validation d’un compte sans authentification  
- éviter les scénarios d’usurpation  
- garantir que l’utilisateur valide son propre compte

---

## 5. Gestion des traductions

### 5.1. Locale configurable via variable d’environnement  

La locale par défaut est définie dans `.env` :

```js
APP_DEFAULT_LOCALE=fr
```

Motivations :

- flexibilité  
- adaptation rapide à un autre contexte linguistique  

### 5.2. Fallbacks statiques  

Les fallbacks sont définis dans `translation.yaml` et non via `.env`.

Motivations :

- impossibilité technique d’injecter un tableau via env  
- stabilité de la configuration  
- simplicité de maintenance  

---

## 6. Gestion des emails en asynchrone

### 6.1. Utilisation de Messenger  

L’envoi d’email passe par le transport `async`.

Motivations :

- éviter les blocages lors de l’inscription  
- améliorer la réactivité du site  
- permettre une montée en charge ultérieure  

### 6.2. Worker dédié  

Un terminal spécifique est utilisé pour le worker Messenger.

Motivations :

- surveillance en temps réel  
- isolation des logs  
- meilleure compréhension du flux d’envoi  

---

## 7. Choix des outils de développement

### 7.1. Mailing

#### 7.1.1 MailHog  

MailHog a été retenu dans un premier temps pour :

- sa simplicité  
- son interface claire  
- sa compatibilité avec un environnement local  
- sa capacité à capturer tous les emails sans configuration complexe  

Alternative envisagée : Mailtrap → rejetée pour éviter une dépendance externe.

#### 7.1.2 MailTip

MailTip a été retenu en dernier lieu car il présente les mêmes avantages que MailHog quant à :

- sa simplicité
- sa compatibilité avec un environnement local
- sa simplicité de configuration

Il apporte une interface plus conviviale (clarté de l'UI et de l'UX) que MailHog.

### 7.2. WSL comme environnement principal  

Le développement est réalisé dans WSL pour :

- bénéficier d’un environnement Linux natif  
- éviter les problèmes de compatibilité Windows/PHP  
- garantir un fonctionnement identique à un serveur réel  

Windows est utilisé uniquement pour :

- l’interface graphique  
- le navigateur  
- MailHog UI  

---

## 8. Organisation interne du projet

### 8.1. Documentation interne non versionnée  

Un dossier `scratches/` a été créé pour stocker :

- notes techniques  
- schémas  
- commandes utiles  
- tests manuels  
- décisions architecturales  

Motivations :

- ne pas polluer le dépôt  
- conserver une trace utile pour la rédaction finale  
- garder une documentation plate, simple et efficace  

### 8.2. Back-office minimal  

Le back-office est volontairement réduit au strict nécessaire :

- visualisation des utilisateurs  
- vérification de l’état `isVerified`  

Motivations :

- éviter un développement inutile  
- se concentrer sur le workflow d’inscription  
- respecter le périmètre du sujet CEF  

---

## 9. Conclusion  

Les décisions architecturales prises dans ce projet visent à garantir :

- la simplicité  
- la sécurité  
- la cohérence  
- la maintenabilité  
- la reproductibilité des tests  

Elles constituent une base solide pour la rédaction finale et pour la compréhension globale du fonctionnement du système.

---
