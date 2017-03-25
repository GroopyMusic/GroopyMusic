#Un-mute asbl

##*Étapes importantes pour la collaboration au développement de la plateforme*
###Rejoindre le Slack du projet

Pour communiquer facilement et efficacement au sein du projet nous utiliserons Slack. L'espace de communication est disponnible [ici](https://un-mute.slack.com/) 
Une demande d'ajout peut être envoyée à gonzyer@gmail.com

###Forker le projet github

###Installer les dépendances avec Composer

Nous utilisons Composer pour gérer les dépendances *third-party* du projet. Il faut avant toutes choses l’installer : <https://getcomposer.org/download/>

Ensuite, il faut disposer d’un fichier `composer.json` qui contient toutes les dépendances du projet. Ce fichier est partagé pour que nous ayons tous les mêmes dépendances ; il se trouve à la racine du projet.

Pour installer les mises-à-jour, exécuter la commande
`php chemin/vers/composer.phar update`
(cette commande doit être lancée depuis le dossier qui contient `composer.json`).

Pour mettre à jour Composer, exécuter
`php chemin/vers/composer.phar self-update`

###Définir les paramètres locaux dans `parameters.yml`

Le fichier `app/config/parameters.yml n’est pas partagé parce qu’il contient des données « secrètes ». Voici ce qu’il faut en faire : 
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
    mailer_password: groopyramide
    
    # Mettre n’importe quoi
    secret: mdrmdrmdrmdrmdr
    
    # L'adresse de livraison des e-mails en développement, mettez la vôtre
    dev_delivery_address: gonzyer@gmail.com

```

###Mettre en place la base de données

Pour mettre en place la base de données, il faut d’abord exécuter
`php bin/console doctrine:database:create`

Puis, pour créer la structure de la base de données au fur et à mesure des mises-à-jour, la commande est `doctrine:schema:update --dump-sql` (pour voir le SQL qui va être exécuté) et `doctrine:schema:update –force` (pour effectivement exécuter le SQL)

La commande suivante vide d’abord l’entièreté de la base de données (!!!) puis la remplit avec des données définies dans des fichiers de « fixtures ». 
`doctrine:fixtures:load` puis répondre `y` à la question posée.

Les fixtures actuels insèrent deux utilisateurs de test dans la base de données : artist@un-mute.be (mdp: test) et fan@un-mute.be (mdp: test). Des phases et paliers de test sont également insérés.  







