# 📄 Tests manuels

## 1. Introduction  

Ce document regroupe les scénarios de tests manuels réalisés durant le développement du projet. Il couvre les tests fonctionnels, les tests de sécurité, les tests liés au workflow d’inscription et de vérification d’email, ainsi que les tests d’intégration avec MailHog et Messenger.  
L’objectif est de disposer d’un référentiel clair permettant de reproduire les vérifications essentielles du système.

---

## 2. Prérequis

### 2.1. Environnement  

- Serveur Symfony lancé dans WSL :  

  ```js
  symfony serve -d --allow-all-ip
  ```

- Worker Messenger actif :  

  ```js
  symfony console messenger:consume async -vv
  ```

- MailHog actif côté Windows (UI accessible via `http://localhost:8025`)
- Base de données initialisée et migrations appliquées
- Aucun utilisateur existant dans la table `user` (si nécessaire) :  

  ```sql
  DELETE FROM user;
  ```

### 2.2. Navigateur  

- Accès via l’IP WSL, par exemple :  

  ```js
  http://172.x.x.x:8000
  ```

---

## 3. Tests fonctionnels

### 3.1. Test d’inscription complète  

**Objectif :** vérifier le workflow nominal.

**Étapes :**

1. Accéder à `/register`.
2. Saisir un email valide et un mot de passe conforme.
3. Soumettre le formulaire.
4. Vérifier l’apparition du message de confirmation.
5. Ouvrir MailHog et vérifier la réception de l’email.
6. Cliquer sur le lien de vérification.
7. Vérifier la redirection vers la page d’accueil.
8. Vérifier dans la base que `is_verified = 1`.

**Résultat attendu :**  
L’utilisateur est créé, l’email est envoyé, le lien valide l’utilisateur.

---

### 3.2. Test de connexion après vérification  

**Objectif :** vérifier que seul un utilisateur vérifié peut se connecter.

**Étapes :**

1. Tenter de se connecter avant validation → échec attendu.
2. Valider l’email.
3. Tenter de se connecter à nouveau.

**Résultat attendu :**  
Connexion impossible avant validation, possible après.

---

## 4. Tests d’erreur

### 4.1. Lien expiré  

**Objectif :** vérifier la gestion de l’expiration.

**Étapes :**

1. Réduire temporairement la durée d’expiration (ex. 1 minute dans `config/packages/verify_email.yaml`).
2. Générer un nouvel utilisateur.
3. Attendre l’expiration.
4. Cliquer sur le lien.

**Résultat attendu :**  

- Message d’erreur  
- Déconnexion  
- Redirection vers la page d’accueil  
- Aucun renvoi automatique d’email

---

### 4.2. Lien compromis (signature modifiée)  

**Objectif :** vérifier la sécurité du lien.

**Étapes :**

1. Copier le lien depuis MailHog.
2. Modifier un caractère dans la signature.
3. Accéder au lien modifié.

**Résultat attendu :**  

- Exception capturée  
- Message d’erreur  
- Déconnexion  
- Redirection vers la page d’accueil

---

### 4.3. Lien utilisé deux fois  

**Objectif :** vérifier la gestion d’un lien déjà consommé.

**Étapes :**

1. Cliquer une première fois → validation OK.
2. Cliquer une seconde fois.

**Résultat attendu :**  

- Message “déjà vérifié”  
- Redirection vers la page d’accueil  
- Aucun changement en base

---

## 5. Tests de sécurité

### 5.1. Accès à /verify/email sans être connecté  

**Étapes :**

1. Copier un lien valide.
2. Se déconnecter.
3. Accéder au lien.

**Résultat attendu :**  

- Accès refusé (IS_AUTHENTICATED_FULLY)  
- Redirection vers la page de connexion

---

### 5.2. Tentative de renvoi automatique d’email  

**Objectif :** vérifier qu’aucun renvoi automatique n’est effectué.

**Étapes :**

1. Provoquer une erreur de validation (lien expiré ou compromis).
2. Observer MailHog.

**Résultat attendu :**  
Aucun email supplémentaire n’est envoyé.

---

## 6. Tests d’intégration MailHog

### 6.1. Vérification du contenu de l’email  

**Étapes :**

1. Ouvrir MailHog.
2. Vérifier :
   - l’adresse expéditrice  
   - le sujet  
   - le lien signé  
   - le format HTML  

**Résultat attendu :**  
L’email contient un lien valide, lisible, et conforme au template.

---

### 6.2. Vérification du transport SMTP  

**Étapes :**

1. Vérifier que le transport est configuré sur `localhost:1025`.
2. Vérifier que Messenger envoie bien les messages.

**Résultat attendu :**  
Les emails apparaissent dans MailHog sans erreur.

---

## 7. Tests du workflow complet

### 7.1. Scénario complet utilisateur  

**Étapes :**

1. Inscription  
2. Réception email  
3. Validation  
4. Connexion  
5. Déconnexion  
6. Reconnexion  

**Résultat attendu :**  
Le cycle complet fonctionne sans erreur.

---

## 8. Conclusion  

Ces tests manuels permettent de valider l’ensemble du workflow d’inscription et de vérification d’email, ainsi que les aspects de sécurité associés. Ils constituent une base fiable pour la rédaction finale et pour la vérification du bon fonctionnement du système.

---

## 9. Organisation des terminaux et outils utilisés pour les tests

> Point **crucial** pour comprendre et reproduire les tests :
>
> ➡️ *les tests ne sont pas seulement des scénarios fonctionnels, ils reposent sur une orchestration précise de plusieurs terminaux spécialisés.*

Les tests manuels nécessitent l’utilisation simultanée de plusieurs terminaux et outils répartis entre WSL et Windows. Cette section décrit leur rôle, leur configuration et leur utilisation dans le cadre du projet.

---

## 9.1. Terminaux WSL

### 9.1.1. Terminal « wsl – système »  

Terminal principal utilisé pour les opérations système :

- installation de packages  
- mises à jour  
- gestion des services Linux  
- vérification des chemins et permissions  

Exemples d’utilisation :

```bash
sudo apt update
sudo apt install mysql-server
sudo service mysql restart
```

---

### 9.1.2. Terminal « wsl – mysql »  

Terminal dédié aux commandes MySQL exécutées en mode administrateur.

Exemples d’utilisation :

```bash
sudo mysql -u root -p
SHOW DATABASES;
DELETE FROM user WHERE id IN (3, 5, 7);
```

Ce terminal reste ouvert pendant toute la durée des tests nécessitant des manipulations directes de la base.

---

### 9.1.3. Terminal « wsl – MailHog »  

Terminal dédié à l’exécution de MailHog côté Windows, mais surveillé depuis WSL pour les logs SMTP.

Exemple :

```bash
mailhog
```

Ce terminal permet de vérifier :

- la réception des emails  
- les erreurs SMTP éventuelles  
- les interactions avec Messenger  

---

### 9.1.4. Terminal « wsl – ServerApp »  

Terminal dédié au serveur Symfony.

Utilisations principales :

- démarrage du serveur  
- arrêt du serveur  
- vidage du cache  
- vérification des routes  

Exemples :

```bash
symfony serve -d --allow-all-ip
symfony server:stop
symfony console cache:clear
```

Ce terminal reste actif pendant toute la durée des tests.

---

### 9.1.5. Terminal « wsl – worker-messenger »  

Terminal dédié au worker Messenger, indispensable pour l’envoi asynchrone des emails.

Commande utilisée :

```bash
symfony console messenger:consume async -vv
```

Ce terminal doit rester ouvert en continu pour permettre :

- l’envoi des emails  
- la gestion des files d’attente  
- la visualisation des logs Messenger  

---

## 9.2. Outils Windows

### 9.2.1. Navigateur Edge  

Le navigateur est utilisé pour :

- accéder à l’application Symfony via l’IP WSL  
- consulter l’interface MailHog  
- ouvrir les ressources techniques (documentation Symfony, GitHub, etc.)

#### Onglets typiques

- **Ressources techniques** : documentation Symfony, articles, guides  
- **MailHog** :  

  ```js
  http://localhost:8025
  ```

  Interface de visualisation des emails  
- **AppSymfony** :  

  ```js
  http://172.x.x.x:8000
  ```

  Accès au site Symfony (dashboard, routes, formulaires)

---

## 9.3. Synthèse de l’orchestration des terminaux

```js
WSL
 ├── Terminal système
 │     └── installations, services, maintenance
 ├── Terminal MySQL
 │     └── gestion directe de la base
 ├── Terminal MailHog
 │     └── logs SMTP
 ├── Terminal ServerApp
 │     └── serveur Symfony + cache
 └── Terminal worker-messenger
       └── envoi des emails

Windows
 ├── Navigateur Edge
 │     ├── ressources techniques
 │     ├── MailHog UI
 │     └── AppSymfony (via IP WSL)
 └── Interface graphique générale
```

---

## 9.4. Importance de cette organisation

Cette organisation permet :

- une séparation claire des responsabilités  
- une surveillance en temps réel des services critiques  
- une exécution fluide des tests manuels  
- une compréhension précise du fonctionnement du système  
- une reproductibilité totale des tests  

Elle constitue un élément essentiel pour comprendre l’environnement de développement et pour reproduire les tests dans un contexte similaire.

---
