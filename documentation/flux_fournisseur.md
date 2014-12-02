Les flux fournisseurs
===

Les **flux fournisseurs** permettent l'�change de documents entre des collectivit�s territorials et leurs fournisseurs
(factures, bons de commande, ...).

Afin de mettre en place la communication entre un fournisseur et une collectivit� il est n�cessaire que le fournisseur 
s'inscrive sur Pastell. Cette inscription se fait suite � l'invitation d'une collectivit� initialement inscrite sur Pastell.

Avant de commencer
---
Il faut s'assurer qu'un r�le fournisseur existe sur la plateforme et dispose des droits suivants :

* utilisateur:lecture
* journal:lecture
* fournisseur-inscription:*

En fonction des flux que l'on souhaite utiliser, il peut-�tre n�cessaire d'ajouter les droits suivants: 
* fournisseur-facture:*
* fournisseur-message: *

Il est �galement n�cessaire d'avoir configurer un connecteur global de type "validation-fournisseur" et d'y avoir indiqu�
une entit� Pastell qui sera responsable de la validation finale des fournisseurs.


Invitation du fournisseur
---

Le flux *Invitation fournisseur* n�cessite un connecteur de type *mail-fournisseur-invitation* 
qui permet de sp�cifier les propri�t�s du mail d'invitation.


Une fois le flux configur�, un utilisateur de Pastell, ayant le droit d'�dition sur une collectivit� pour le flux "Invitation fournisseur" 
peut cr�er un document de ce type en sp�cifiant la raison sociale du fournisseur ainsi qu'une adresse �lectronique. 

L'utilisateur peut renvoyer l'invitation autant de fois qu'il le souhaite (perte du mail par le fournisseur par exemple).
Une fois le fournisseur inscrit, il n'est plus possible de lui r�-envoyer une invitation.

Il est possible d'envoyer un grand nombre d'invitation via un fichier au format CSV, cette possibilit� est � configurer dans le 
connecteur mail-fournisseur-invitation et demandera donc des droits d'administration.

Le mail d'invitation contient un lien permettant au fournisseur d'effectuer une pr�inscription sur Pastell en lui demandant un minimum 
d'information. Une fois le formulaire valid�, une entit� de type "fournisseur" est cr��e, ainsi qu'un compte utilisateur rattach� 
� cette entit�. L'utilisateur prend automatiquement le r�le *fournisseur*. Il est ainsi n�cessaire de bien valider que ce r�le existe sur Pastell
et dispose des bons droits.

Adh�sion du fournisseur
---

Le fournisseur doit d'abord soumettre ces informations d'adh�sions � une ou plusieurs collectivit�s avec laquelle il souhaite communiquer.

Lors de son inscription, un document de type "Adh�sion fournisseur" � �t� cr��, le fournisseur doit le compl�ter et l'envoyer � la ou 
aux collectivit�s qui l'ont pr�cedemment invit�.

Une fois que la collectivit� re�oit le document, elle peut soit :
* accepter le document
* refuser le document.

En cas de refus, le document est retourn� au fournisseur qui pourra alors le modifier et le soumettre � nouveau.

En cas d'acceptation, le document est envoy� � l'entit� Pastell d�fini par le connecteur global *validation-fournisseur*.
Cette entit� pourra � son tour accepter ou refuser le document.

Le refus, comme pr�c�demment, renvoi le document au fournisseur.

En cas d'acceptation, il est alors possible pour cette collectivit� et ce forunisseur d'utiliser les services de factures, de messagerie, etc.

Ce flux, ainsi que le flux d'invitation prennent en compte les situations suivantes:
* envoi d'une invitation � un fournisseur qui a d�j� un compte Pastell : dans ce cas, le fournisseur se logue sur la page issu du mail 
d'invitation et doit juste envoyer son document d'adh�sion � la nouvelle collectivit�. Si ce document � d�j� �t� mod�r�, alors
on ne passe pas par l'�tape de mod�ration.
* Modification du document d'adh�sion. Le fournisseur peut modifier son document d'adh�sion, mais dans ce cas, il doit le resoumettre 
� toutes les collectivit�s et, de ce fait, � la mod�ration.



Flux factures fournisseur
---

Ce flux n'est utilisable que pour les fournisseur qui ont envoy� un document d'adh�sion qui a �t� accept� par une collectivit� et par 
l'entit� mod�ratrice.

Le flux est initi� par le fournisseur qui choisi une collectivit� avec laquelle il est en relation, puis il saisie sa facture (le 
document PDF, ainsi que divers meta-donn�es).

La collectivit� qui re�oit la facture peut :
* la renvoyer en indiquant un commentaire et �ventuellement des pi�ces jointes
* l'envoyer dans son SI qui a pour effet de l'envoyer sur une GED (� configurer via un connecteur)

Une fois la facture envoyer au SI, il est possible d'en notifier la liquidation au fournisseur qui re�oit alors un email.








