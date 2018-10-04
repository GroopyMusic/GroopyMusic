[https://github.com/GroopyMusic/GroopyMusic](https://github.com/GroopyMusic/GroopyMusic)

# Un-Mute asbl

###### *Étapes importantes pour la collaboration au développement de la plateforme*

### GitHub

Pour le développement de la plateforme, nous utilisons Github.

Voici donc quelques étapes pour le setup :

  - Avoir un compte [Github](https://github.com/join)
  - demander à [Gonzague](https://github.com/Gounzy) de vous rajouter comme collaborateur.
  - cloner le projet sur votre machine (entrez la commande `git clone https://github.com/Gounzy/GroopyMusic.git` dans votre terminal).

Et voici quelques étapes pour le workflow à adopter :

 ⚠️ Ne jamais entrer la commande `git push origin master` ⚠️

  - Avant de commencer à travailler : `git checkout master && git pull origin master`
  - créer une branche sur laquelle travailler : `git checkout -b NOM_DE_LA_BRANCHE`
  - commit les changements : `git add .`, `git commit -m "mon message personnel et descriptif"`
  - pusher la branche sur github : `git push origin NOM_DE_LA_BRANCHE`


### Installer les dépendances avec Composer

Nous utilisons Composer pour gérer les dépendances *third-party* du projet. Il faut avant toutes choses l’installer : <https://getcomposer.org/download/>

Ensuite, il faut disposer d’un fichier `composer.json` qui contient toutes les dépendances du projet. Ce fichier est partagé pour que nous ayons tous les mêmes dépendances ; il se trouve à la racine du projet.

Pour installer les mises-à-jour, exécuter la commande
`php chemin/vers/composer.phar install`
(cette commande doit être lancée depuis le dossier qui contient `composer.json`).

Pour mettre à jour Composer, exécuter
`php chemin/vers/composer.phar self-update`

### Définir les paramètres locaux dans `parameters.yml`

Le fichier `app/config/parameters.yml` n’est pas partagé parce qu’il contient des données « secrètes ». Voici ce qu’il faut en faire :

```yaml
parameters:
   # Vos données de base de données locale
    database_host: 127.0.0.1
    database_port: null
    database_name: unmute
    database_user: root
    database_password: null

    # Les paramètres de transport mail, à copier
    mailer_transport: smtp
    mailer_host: smtp.un-mute.be
    mailer_user: no-reply@un-mute.be
    mailer_password: mot_de_passe_recu_de_gonzague

    # Mettre n’importe quoi
    secret: mdrmdrmdrmdrmdr

    # L'adresse de livraison des e-mails en développement, mettez la vôtre
    dev_delivery_address: gonzyer@gmail.com

```

### Mettre en place la base de données

Pour mettre en place la base de données, il faut d’abord exécuter
`php bin/console doctrine:database:create`

Puis, pour créer la structure de la base de données au fur et à mesure des mises-à-jour, la commande est `doctrine:schema:update --dump-sql` (pour voir le SQL qui va être exécuté) et `doctrine:schema:update –force` (pour effectivement exécuter le SQL).

Si c'est la première fois, il faut également ajouter toutes les "migrations" Doctrine avec la commande `doctrine:migrations:version --add --all` 

Pour toute modification d'entité entraînant une modification de la base de données, il faut exécuter `doctrine:migrations:diff` qui créera un fichier de migrations dans le répertoire app/DoctrineMigrations/

Pour mettre à jour la base de données lors d'un pull de la branche master par exemple, il faut exécuter `doctrine:migrations:migrate`
