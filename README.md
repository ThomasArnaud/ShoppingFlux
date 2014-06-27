#Shopping Flux module v1.0
author: <bperche@openstudio.fr>

##Summary

### [fr_FR](#fr_FR)

1. [Pré-requis](#fr_FR_Requirements)

2. [Installation](#fr_FR_Install)

3. [Utilisation](#fr_FR_Usage)


### en_US

1. [Requirements](#en_US_Requirements)

2. [How to install](#en_US_Install)

3. [How to use](#en_US_Usage)


##  <a name="fr_FR"></a> fr_FR

### <a name="fr_FR_Requirements"></a> Pré-requis

Le module ShoppingFlux fait parti d'un lot de deux modules:

- Ce module: ShoppingFlux

- Le module ShoppingFluxPayment

Ce second est optionnel, mais s'il n'est pas présent, une erreur signalant des fichiers manquants
sera présente sur la liste des modules.

### <a name="fr_FR_Install"></a> Installation

Pour installer ShoppingFlux, vous pouvez télécharger les archives des deux modules et les
décomprésser dans le dossier

```chemin-de-Thelia/local/modules```

ou vous pouvez les installer via git:

```
$ cd chemin-de-Thelia/local/modules
$ git clone https://github.com/thelia-modules/ShoppingFluxPayment.git
$ git clone https://github.com/thelia-modules/ShoppingFlux.git
```

### <a name="fr_FR_Usage"></a> Utilisation

Pour utiliser ShoppingFlux, allez sur la page de configuration de ce module,
vous devez créer une taxe de montant fixe ( Ecotaxe ) si elle n'existe pas déjà,
puis allez sur la page de configuration du module, renseignez votre token shoppingflux,
La langue dans laquelle vous souhaitez exporter votre catalogue, le module de livraison
que vous utilisez ( n'utilisez en aucun cas un module de livraison en point relais ),
ainsi que votre écotaxe.
Activez le bouton "En production" quand vous avez fini, puis enregistrez.

Vous pouvez ensuite utiliser le bouton exporter pour obtenir un fichier XML de votre catalogue,
pour l'importer dans Shopping Flux, et le bouton "Récupérer les commandes" qui intéroge Shopping Flux pour les denières
commandes.

Notes:

- Exporter votre configuration sauvegarde aussi votre configuration.

- Vous pouvez automatiser la récupération des commandes via une tâche cron ([voir fig.1](#fr_FR_fig1))


<a name="fr_FR_fig1"></a>fig.1

```
$ (crontab -u username -l; echo "@hourly php chemin-de-Thelia/Thelia module:shoppingflux:getorders" ) | crontab -u username -
```


##  <a name="en_US"></a> en_US

### <a name="en_US_Requirements"></a> Requirements
### <a name="en_US_Install"></a> How to install
### <a name="en_US_Usage"></a> How to use