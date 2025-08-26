<?php

declare(strict_types=1);

use App\Model\Permission;

return static function (): iterable {

    yield Permission::new('ROLE_USER_CREATE', "Créér un utilisateur");
    yield Permission::new('ROLE_USER_LOCK', "Vérouiller/Déverrouiller un utilisateur");
    yield Permission::new('ROLE_USER_CHANGE_PWD', "Modifier mot de passe");
    yield Permission::new('ROLE_USER_DETAILS', "Consulter les détails d'un utilisateur");
    yield Permission::new('ROLE_USER_LIST', "Consulter la liste des utilisateurs");
    yield Permission::new('ROLE_USER_EDIT', "Editer les informations d'un utilisateur");
    yield Permission::new('ROLE_USER_DELETE', "Supprimer un utilisateur");
    yield Permission::new('ROLE_USER_SET_PROFILE', "Modifier le profil utilisateur");

    yield Permission::new('ROLE_PROFILE_CREATE', "Créer un profil utilisateur");
    yield Permission::new('ROLE_PROFILE_LIST', "Consulter la liste des profils utilisateur");
    yield Permission::new('ROLE_PROFILE_UPDATE', "Modifier un profil utilisateur");
    yield Permission::new('ROLE_PROFILE_DETAILS', "Consulter les détails d'un profil utilisateur");

    yield Permission::new('ROLE_ADDRESS_CREATE', "Créer une adresse");
    yield Permission::new('ROLE_ADDRESS_LIST', "Consulter la liste des adresses");
    yield Permission::new('ROLE_ADDRESS_DETAILS', "Consulter les détails d'une adresse");
    yield Permission::new('ROLE_ADDRESS_UPDATE', "Modifier une adresse");

    yield Permission::new('ROLE_CUSTOMER_CREATE', "Créer un client");
    yield Permission::new('ROLE_CUSTOMER_LIST', "Consulter la liste des clients");
    yield Permission::new('ROLE_CUSTOMER_DETAILS', "Consulter les détails d'un client");
    yield Permission::new('ROLE_CUSTOMER_UPDATE', "Modifier un client");
    yield Permission::new('ROLE_CUSTOMER_DELETE', "Supprimer un client");
    yield Permission::new('ROLE_CUSTOMER_ACTIVATE', "Activer un client"); 

    yield Permission::new('ROLE_DELIVERY_CREATE', "Créer une livraison");
    yield Permission::new('ROLE_DELIVERY_LIST', "Consulter la liste des livraisons");
    yield Permission::new('ROLE_DELIVERY_DETAILS', "Consulter les détails d'une livraison");
    yield Permission::new('ROLE_DELIVERY_UPDATE', "Modifier une livraison");
    yield Permission::new('ROLE_DELIVERY_CANCEL', "Annuler une livraison");
    yield Permission::new('ROLE_DELIVERY_VALIDATION', "Valider une livraison");
    yield Permission::new('ROLE_DELIVERY_PICKUP', "Prendre en charge une livraison");
    yield Permission::new('ROLE_DELIVERY_INPROGRESS', "Mettre une livraison en cours");
    yield Permission::new('ROLE_DELIVERY_DELAY', "Reporter une livraison");
    yield Permission::new('ROLE_DELIVERY_DELIVER', "Finaliser une livraison");
    yield Permission::new('ROLE_DELIVERY_REASSIGNATION', "Réassigner une livraison"); 
    
    yield Permission::new('ROLE_RECIPIENT_CREATE', "Créer un destinataire");
    yield Permission::new('ROLE_RECIPIENT_LIST', "Consulter la liste des destinataires");
    yield Permission::new('ROLE_RECIPIENT_DETAILS', "Consulter les détails d'un destinataire");
    yield Permission::new('ROLE_RECIPIENT_UPDATE', "Modifier un destinataire");
    yield Permission::new('ROLE_RECIPIENT_DELETE', "Supprimer un destinataire");

    yield Permission::new('ROLE_RECIPIENT_TYPE_CREATE', "Créer un type de destinataire");
    yield Permission::new('ROLE_RECIPIENT_TYPE_LIST', "Consulter la liste des types de destinataire");
    yield Permission::new('ROLE_RECIPIENT_TYPE_DETAILS', "Consulter les détails d'un type de destinataire");
    yield Permission::new('ROLE_RECIPIENT_TYPE_UPDATE', "Modifier un type de destinataire");
    
    yield Permission::new('ROLE_TOWNSHIP_CREATE', "Créer un township");
    yield Permission::new('ROLE_TOWNSHIP_LIST', "Consulter la liste des townships");
    yield Permission::new('ROLE_TOWNSHIP_DETAILS', "Consulter les détails d'un township");
    yield Permission::new('ROLE_TOWNSHIP_UPDATE', "Modifier un township");
    
    yield Permission::new('ROLE_ZONE_CREATE', "Créer une zone");
    yield Permission::new('ROLE_ZONE_LIST', "Consulter la liste des zones");
    yield Permission::new('ROLE_ZONE_DETAILS', "Consulter les détails d'une zone");
    yield Permission::new('ROLE_ZONE_UPDATE', "Modifier une zone");
    
    yield Permission::new('ROLE_ACTIVITY_LIST', "Consulter la liste des activités");
    yield Permission::new('ROLE_ACTIVITY_VIEW', "Consulter les détails d'une activité");

    yield Permission::new('ROLE_DELIVERY_PERSON_DETAILS', "Consulter les détails d'un livreur");
    yield Permission::new('ROLE_DELIVERY_PERSON_LIST', "Consulter la liste des livreurs");
    yield Permission::new('ROLE_DELIVERY_PERSON_CREATE', "Créer un livreur");
    yield Permission::new('ROLE_DELIVERY_PERSON_UPDATE', "Modifier un livreur");
    yield Permission::new('ROLE_DELIVERY_PERSON_DELETE', "Supprimer un livreur");

    yield Permission::new('ROLE_NOTIFICATION_LIST', "Consulter la liste des notifications");
    yield Permission::new('ROLE_NOTIFICATION_DETAILS', "Consulter les détails d'une notification");

    yield Permission::new('ROLE_DOC_DETAILS', "Consulter les détails d'un document");
    yield Permission::new('ROLE_DOC_LIST', "Consulter la liste des documents");
    yield Permission::new('ROLE_DOC_CREATE', "Créer un document");
    yield Permission::new('ROLE_DOC_REJECT', "Refuser un document"); 
    yield Permission::new('ROLE_DOC_VALIDATE', "Valider un document"); 
    yield Permission::new('ROLE_DOC_DELETE', "Supprimer un document");
    
    yield Permission::new('ROLE_SERVICE_DETAILS', "Consulter les détails d'un service");
    yield Permission::new('ROLE_SERVICE_LIST', "Consulter la liste des services");
    yield Permission::new('ROLE_SERVICE_CREATE', "Créer un service");
    yield Permission::new('ROLE_SERVICE_UPDATE', "Modifier un service");
    
    yield Permission::new('ROLE_STORE_DETAILS', "Consulter les détails d'un magasin");
    yield Permission::new('ROLE_STORE_LIST', "Consulter la liste des magasins");
    yield Permission::new('ROLE_STORE_CREATE', "Créer un magasin");
    yield Permission::new('ROLE_STORE_UPDATE', "Modifier un magasin");
    yield Permission::new('ROLE_STORE_ACTIVATE', "Activer un magasin"); 
    
    yield Permission::new('ROLE_PRODUCT_DETAILS', "Consulter les détails d'un produit");
    yield Permission::new('ROLE_PRODUCT_LIST', "Consulter la liste des produits");
    yield Permission::new('ROLE_PRODUCT_CREATE', "Créer un produit");
    yield Permission::new('ROLE_PRODUCT_UPDATE', "Modifier un produit");
    
    yield Permission::new('ROLE_PRODUCT_TYPE_DETAILS', "Consulter les détails d'un type de produit");
    yield Permission::new('ROLE_PRODUCT_TYPE_LIST', "Consulter la liste des types de produit");
    yield Permission::new('ROLE_PRODUCT_TYPE_CREATE', "Créer un type de produit");
    yield Permission::new('ROLE_PRODUCT_TYPE_UPDATE', "Modifier un type de produit");
    yield Permission::new('ROLE_PRODUCT_TYPE_DELETE', "Supprimer un type de produit");
    
    yield Permission::new('ROLE_ORDER_DETAILS', "Consulter les détails d'une commande");
    yield Permission::new('ROLE_ORDER_LIST', "Consulter la liste des commandes");
    yield Permission::new('ROLE_ORDER_CREATE', "Créer une commande");
    yield Permission::new('ROLE_ORDER_VALIDATION', "Valider une commande");
    yield Permission::new('ROLE_ORDER_REJECT', "Refuser une commande");
    yield Permission::new('ROLE_ORDER_INPROGRESS', "Mettre une commande en cours");
};
