# Projet Video Center

# Table des matières
1. [Comment utiliser le code](#Comment-utiliser-le-code)
2. [Reset DB](#Reset-DB)
3. [Configuration](#configuration)

# Comment utiliser le code

## Lancer le serveur Symfony

Ouvrir un terminal dans la racine du projet et entrer:

```
symfony serve -d
```

## Arreter le serveur Symfony

Ouvrir un terminal dans la racine du projet et entrer:

```
symfony server:stop
```

# Reset DB

Pour reset la DB, il faut dans un premier temps supprimer les migrations dans `/migrations` et ensuite lancer cette commande dans un terminal ouvert a la racine du projet:

```
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Avec pipe:

```
php bin/console doctrine:database:drop --force | php bin/console doctrine:database:create | php bin/console make:migration | php bin/console doctrine:migrations:migrate
```

# Configuration

Avant de lancer le code il faut également vérifier le fichier `.env` qui se trouve a la racine du projet et mettre son mailtrap.

```
MAILER_DSN=  URL MAILTRAP
```