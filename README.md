#Shopping Flux module v1.1

##Summary

### [fr_FR](#fr_FR)

1. [Installation](#fr_FR_Install)

2. [Utilisation](#fr_FR_Usage)


### en_US

1. [How to install](#en_US_Install)

2. [How to use](#en_US_Usage)


##  <a name="fr_FR"></a> fr_FR

### <a name="fr_FR_Install"></a> Installation

Pour installer ShoppingFlux, vous pouvez télécharger l'archive et la décomprésser dans le dossier
```
chemin-de-Thelia/local/modules
```

ou vous pouvez l'installer via git:

```
$ cd chemin-de-Thelia/local/modules
$ git clone https://github.com/thelia-modules/ShoppingFlux.git
```

### <a name="fr_FR_Usage"></a> Utilisation

Pour utiliser ShoppingFlux, vous devez créer une taxe de montant fixe ( Ecotaxe ) si elle n'existe pas déjà.
Ensuite allez sur la page de configuration du module, renseignez votre token shopping flux,
la langue dans laquelle vous souhaitez exporter votre catalogue, le module de livraison que vous utilisez (n'utilisez en aucun cas un module de livraison en point relais),ainsi que votre écotaxe.

Activez le bouton "En production" quand vous avez fini, puis enregistrez.

Vous pouvez ensuite utiliser le bouton exporter pour obtenir un fichier XML de votre catalogue et l'importer dans Shopping Flux, et le bouton "Récupérer les commandes" qui interroge Shopping Flux et récupère les denières commandes passées sur les marketplaces sélectionnées.

Notes:

- Exporter votre configuration sauvegarde aussi votre configuration.

- Vous pouvez automatiser la récupération des commandes via une tâche cron ([voir fig.1](#fig1))


##  <a name="en_US"></a> en_US

### <a name="en_US_Install"></a> How to install

You can install this module by downloading the archive and uncompressing it into

```
path-to-Thelia/local/modules
```

Or you may install it with git:
```
$ cd path-to-Thelia/local/modules
$ git clone https://github.com/thelia-modules/ShoppingFlux.git
```

### <a name="en_US_Usage"></a> How to use

Before using ShoppingFlux, you'll need to have a tax rule for Ecotax: if you don't have one,
just create a Fix Amout tax. Then go to the module's configuration page, give your Shopping Flux
token, the language you want to export your catalog, the delivery module that you will use
( do never use a pick-up & Go store delivery module ), and your ecotax.
Activate the button "In production" when you're done, then save.

You can use the export button to get an XML file of your catalog, in order to import it in
Shopping Flux service, and the button "Get orders" that asks Shopping Flux for new orders on
marketplaces

Notes:

- Export orders doesn't save the configuration

- You can automate the function "get orders" by creating a cron task ([see fig.1](#fig1))

## Other

<a name="fig1"></a>fig.1
```
$ (crontab -u username -l; echo "@hourly php chemin-de-Thelia/Thelia module:shoppingflux:getorders" ) | crontab -u username -
```

#Shopping Flux module v1.1

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

### <a name="fr_FR_Install"></a> Installation

Pour installer ShoppingFlux, vous pouvez télécharger les archives des deux modules et les
décompresser dans le dossier

```chemin-de-Thelia/local/modules```

ou vous pouvez les installer via git:

```
$ cd chemin-de-Thelia/local/modules
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
pour l'importer dans Shopping Flux, et le bouton "Récupérer les commandes" qui interroge Shopping Flux pour les dernières
commandes.

Notes:

- Exporter votre configuration sauvegarde aussi votre configuration.

- Vous pouvez automatiser la récupération des commandes via une tâche cron ([voir fig.1](#fig1))

##  <a name="en_US"></a> en_US

The second is optional, but if it's not present, an error signaling "Missing files " will be present
on the modules page.

### <a name="en_US_Install"></a> How to install

You can install this module by downloading the two archives and uncompressing them into
```path-to-Thelia/local/modules```

Or you may install them with git:
```
$ cd path-to-Thelia/local/modules
$ git clone https://github.com/thelia-modules/ShoppingFlux.git
```

### <a name="en_US_Usage"></a> How to use

Before using ShoppingFlux, you'll need to have a tax rule for Ecotax: if you don't have one,
just create a Fix Amout tax. Then go to the module's configuration page, give your Shopping Flux
token, the language you want to export your catalog, the delivery module that you will use
( do never use a pick-up & Go store delivery module ), and your ecotax.

Finally, activate the button "In production" and save.

The button "Export" allows you to get an XML file of your catalog, in order to import it in your Shopping Flux manager.

The button "Get orders" imports in Thelia all the orders placed from the marketplaces you chose.


Notes:

- Export orders doesn't save the configuration

- You can automate the function "get orders" by creating a cron task ([see fig.1](#fig1))


## Other

<a name="fig1"></a>fig.1
```
$ (crontab -u username -l; echo "@hourly php chemin-de-Thelia/Thelia module:shoppingflux:getorders" ) | crontab -u username -
```
