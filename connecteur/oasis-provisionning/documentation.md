Attention, avant de proc�der � la mise � jour, il convient de supprimer le connecteur global OASIS pr�c�dent.
(sinon, il y aura un bug dans le protocole de supression d'une instance)


Il faut donc :
* cr�er un connecteur global OASIS provisionning en sp�cifiant 
- les secrets pour les URL de cr�ation et de supression ;
- l'url qui sera utilis� pour la cr�ation du connecteur d'authentification OpenID ( https://accounts.ozwillo-preprod.eu/ dans ce cas) ;
- le r�le qui sera attribu� � l'utilisateur qui cr�e l'instance (admin par exemple), ce r�le est attribu� uniquement sur la collectivit� cr�e.

* Associer le connecteur global OASIS provisionning

A ce moment-l�, Pastell sait r�pondre au requ�te de cr�ation d'instance. Il enregistre celle-ci directement comme fichier attach� au connecteur ( Instances en attente ).

Pour le moment, il n'est possible que de traiter manuellement les demandes de cr�ation (on peut l'automatiser si n�cessaire) : il suffit de cliquer sur "Traitement de la premi�re instance en attente".
Cela cr�e la collectivit�, l'utilisateur et un connecteur d'authentification OpenID attach� � la collectivit�. Cela envoie �galement le message d'acquittement � OASIS.

L'utilisateur peux alors se connecter sur Pastell via le portail OASIS.

Pastell r�pond �galement au demande de supression des instances en d�sactivant la collectivit� associ� � l'instance.


L'admin de collectivit� peut (une fois connect� via OpenID) v�rifi� et synchroniser les utilisateurs dans le connecteur OpenID.
