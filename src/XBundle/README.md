# Site web X

Ce nouveau module permet à un artiste d'un genre quelconque de lancer un projet qui sera fincancé de manière participative. 
Exemple : Je suis comédien et j'aimerais organiser un spectacle dans la salle de mon village. Je vais donc ajouter ce projet sur le site X 
et ajouter des produits que j'aimerais vendre en même temps (les DVD's de mes sketchs). Les personnes interressées pourront donc soit faire une donation "pure" à mon projet, soit acheter l'un des produits proposés à la vente. 

## Getting Started

Pour installer le projet sur votre machine, suivez les instructions données dans le Readme de GroopyMusic. Vous devez également être connecté pour pouvoir tirer pleinement parti des fonctionnalités du site X. 


## Built With

* [Symfony 3](https://symfony.com/doc) - The web framework used
* [JPList](https://jplist.com/) - Filters managment
* [Stripe](https://stripe.com/) - Payment managment 


## Authors

* **Tom Wautelet** - *Initial work* - [Sigma-](https://github.com/Sigma-)

## Fonctionnalités terminées

* Affichage des projets sur la page des projets
* Système de points 
* Système de paiement à l'aide de Stripe (inclut donation et achat d'un produit à la fois)
* Système de filtre à l'aide de JPList
* Système de filtre par tag
* Système d'ajout de produits associés aux projets
* Landingpage - 80% finie

## Fonctionnalités non-terminées

* Page "Mes projets favoris" - Les projets auxquels l'utilisateur connecté a donné des points seront affichés sur cette page
* Le projet à l'affiche sur la landingpage est le projet de la semaine. Pour que cela fonctionne, il faudrait reset les points des projets chaque semaine dans le contrôleur de projets
* Lier l'entité Projects à Artist de AppBundle et possibilité pour l'utilisateur de lié son projet à un artiste d'un-mute
* Envoi d'un e-mail à l'utilisateur lorsque le paiement est réussi
* Affichage d'un caroussel (bootstrap) avec vidéos et photos lorsque l'utilisateur est sur la page d'affichage d'un projet (A l'aide de VideoType et ImageType de AppBundle)
* Edition-suppression d'un produit
* Edition d'un projet
* Vérification de la mise en page lorsque l'utilisateur filtre par fonds desc/asc, titre A-z. Il semble y avoir un soucis de position des projets sur la page

## Questions

* Pour toutes questions relatives au code du site web, veuillez me contacter par e-mail : tom.wautelet@student.unamur.be

