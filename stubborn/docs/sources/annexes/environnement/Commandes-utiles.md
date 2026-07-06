# 📄 Commandes utiles

## 1. Introduction  

Ce document regroupe les commandes essentielles utilisées durant le développement du projet. Elles couvrent Symfony, Composer, Doctrine, Messenger, MySQL, les fixtures, ainsi que les outils annexes. L’objectif est de disposer d’un référentiel clair pour reproduire ou diagnostiquer les opérations techniques réalisées.

---

## 2. Commandes Symfony

### 2.1. Lancer le serveur Symfony  

```bash
symfony serve
```

### 2.2. Lancer le serveur en arrière-plan  

```bash
symfony serve -d
```

### 2.3. Lancer le serveur en autorisant l’accès depuis Windows  

> (nécessaire pour accéder au serveur WSL via l’IP 172.x.x.x)

```bash
symfony serve -d --allow-all-ip
```

### 2.4. Vérifier l’état du serveur  

```bash
symfony server:status
```

### 2.5. Arrêter le serveur  

```bash
symfony server:stop
```

### 2.6. Lister les routes  

```bash
symfony console debug:router
```

### 2.7. Lister les services  

```bash
symfony console debug:container
```

---

## 3. Commandes Composer

### 3.1. Installer les dépendances  

```bash
composer install
```

### 3.2. Mettre à jour les dépendances  

```bash
composer update
```

### 3.3. Ajouter un package  

```bash
composer require <package>
```

### 3.4. Supprimer un package  

```bash
composer remove <package>
```

---

## 4. Commandes Doctrine

### 4.1. Créer la base de données  

```bash
symfony console doctrine:database:create
```

### 4.2. Supprimer la base de données  

```bash
symfony console doctrine:database:drop --force
```

### 4.3. Générer une migration  

```bash
symfony console make:migration
```

### 4.4. Exécuter les migrations  

```bash
symfony console doctrine:migrations:migrate
```

### 4.5. Requêtes SQL directes  

```bash
symfony console doctrine:query:sql "SELECT * FROM user"
```

### 4.6. Réinitialiser les utilisateurs  

```bash
symfony console doctrine:query:sql "DELETE FROM user"
```

---

## 5. Commandes MySQL (WSL)

### 5.1. Se connecter à MySQL  

```bash
sudo mysql -u root -p
```

### 5.2. Lister les bases  

```sql
SHOW DATABASES;
```

### 5.3. Sélectionner une base  

```sql
USE <nom_base>;
```

### 5.4. Lister les tables  

```sql
SHOW TABLES;
```

### 5.5. Voir le contenu d’une table  

```sql
SELECT * FROM user;
```

### 5.6. Supprimer un utilisateur par ID  

```sql
DELETE FROM user WHERE id = 5;
```

### 5.7. Supprimer plusieurs utilisateurs par ID  

```sql
DELETE FROM user WHERE id IN (3, 5, 7, 12);
```

### 5.8. Supprimer tous les utilisateurs non vérifiés  

```sql
DELETE FROM user WHERE is_verified = 0;
```

### 5.9. Réinitialiser l’auto-incrément  

```sql
ALTER TABLE user AUTO_INCREMENT = 1;
```

### 5.10. Quitter MySQL  

```sql
EXIT;
```

---

## 6. Commandes Fixtures

### 6.1. Charger les fixtures  

```bash
symfony console doctrine:fixtures:load
```

### 6.2. Recharger les fixtures sans confirmation  

```bash
symfony console doctrine:fixtures:load --no-interaction
```

### 6.3. Charger un groupe de fixtures  

```bash
symfony console doctrine:fixtures:load --group=dev
```

---

## 7. Commandes make:* utilisées dans le projet

### 7.1. Générer le contrôleur d’inscription  

```bash
symfony console make:registration-form
```

### 7.2. Générer un contrôleur  

```bash
symfony console make:controller <NomController>
```

### 7.3. Générer une entité  

```bash
symfony console make:entity <NomEntite>
```

### 7.4. Générer un formulaire  

```bash
symfony console make:form <NomFormType>
```

### 7.5. Générer des fixtures  

```bash
symfony console make:fixtures
```

---

## 8. Commandes Messenger

### 8.1. Lancer le worker Messenger  

```bash
symfony console messenger:consume async -vv
```

### 8.2. Lancer le worker en continu  

```bash
symfony console messenger:consume async --limit=0 --time-limit=0
```

### 8.3. Vérifier les transports  

```bash
symfony console debug:messenger
```

### 8.4. Purger les messages échoués  

```bash
symfony console messenger:failed:remove
```

---

## 9. Commandes utiles pour les tests

### 9.1. Vérifier la configuration du Mailer  

```bash
symfony console debug:config framework mailer
```

### 9.2. Vérifier la configuration du VerifyEmail  

```bash
symfony console debug:config verify_email
```

### 9.3. Tester l’envoi d’un email  

```bash
symfony console debug:mailer
```

---

## 10. Commandes liées au cache

### 10.1. Vider le cache  

```bash
symfony console cache:clear
```

### 10.2. Vider le cache en environnement prod  

```bash
symfony console cache:clear --env=prod
```

---

## 11. Commandes système utiles (WSL)

### 11.1. Ouvrir VS Code dans WSL  

```bash
code .
```

### 11.2. Vérifier l’IP de WSL  

```bash
ip addr show eth0
```

### 11.3. Redémarrer MySQL  

```bash
sudo service mysql restart
```

### 11.4. Vérifier les ports ouverts  

```bash
ss -tulnp
```

### 11.5 Connaître les ports ouverts

```bash
sudo lsof -i
```

### 11.6 Arrêter un processus `<PID>`

```bash
sudo kill -9 <PID>
```

---

## 12. Conclusion  

Ce document rassemble les commandes essentielles utilisées durant le développement du projet. Il constitue une référence rapide pour reproduire les opérations techniques, diagnostiquer un problème ou réinitialiser l’environnement de travail.

---
