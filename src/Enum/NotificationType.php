<?php

namespace App\Enum;

class NotificationType
{
    // === LIVRAISON ===
    public const string DELIVERY_CREATED = 'dlv_cre'; // Création de la livraison
    public const string DELIVERY_ASSIGNED = 'dlv_asg'; // Livreur affecté à une livraison
    public const string DELIVERY_PICKED_UP = 'dlv_pup'; // Colis retiré
    public const string DELIVERY_IN_TRANSIT = 'dlv_trn'; // Colis en cours de livraison
    public const string DELIVERY_COMPLETED = 'dlv_cmp'; // Livraison réussie
    public const string DELIVERY_FAILED = 'dlv_fail'; // Échec de livraison
    public const string DELIVERY_CANCELED = 'dlv_cnl'; // Livraison annulée
    public const string DELIVERY_UPDATED = 'dlv_upd'; // Infos de livraison modifiées
    public const string DELIVERY_DELAYED = 'dlv_dly'; // Retard signalé

    // === UTILISATEUR / CLIENT ===
    public const string NEW_ACCOUNT_CREATED = 'usr_new'; // Nouveau compte client ou livreur
    public const string ACCOUNT_ACTIVATED = 'usr_act'; // Compte activé
    public const string PASSWORD_RESET = 'usr_pwd'; // Demande ou confirmation de réinitialisation
    public const string PROFILE_UPDATED = 'usr_upd'; // Infos de profil modifiées

    // === NOTIFICATIONS DE SUIVI ===
    public const string DELIVERY_STATUS_UPDATED = 'ntf_dlv'; // Mise à jour de statut (push client)
    public const string NEW_MESSAGE = 'ntf_msg'; // Nouveau message reçu (chat client/livreur ?)
    public const string NOTIFICATION_SENT = 'ntf_snt'; // Confirmation d’envoi d’une notif

    // === SYSTÈME / ADMIN ===
    public const string SYSTEM_UPDATE = 'sys_upd'; // Notification d'une mise à jour système
    public const string ROLE_ASSIGNED = 'usr_rol'; // Rôle modifié par admin
    public const string ERROR_REPORTED = 'sys_err'; // Erreur système notifiée
    public const string MAINTENANCE_ALERT = 'sys_mnt'; // Alerte de maintenance planifiée

    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

    public static function getGrouped(): array
    {
        return [
            'delivery' => [
                self::DELIVERY_CREATED,
                self::DELIVERY_ASSIGNED,
                self::DELIVERY_PICKED_UP,
                self::DELIVERY_IN_TRANSIT,
                self::DELIVERY_COMPLETED,
                self::DELIVERY_FAILED,
                self::DELIVERY_CANCELED,
                self::DELIVERY_UPDATED,
                self::DELIVERY_DELAYED,
            ],
            'user' => [
                self::NEW_ACCOUNT_CREATED,
                self::ACCOUNT_ACTIVATED,
                self::PASSWORD_RESET,
                self::PROFILE_UPDATED,
            ],
            'tracking' => [
                self::DELIVERY_STATUS_UPDATED,
                self::NEW_MESSAGE,
                self::NOTIFICATION_SENT,
            ],
            'system' => [
                self::SYSTEM_UPDATE,
                self::ROLE_ASSIGNED,
                self::ERROR_REPORTED,
                self::MAINTENANCE_ALERT,
            ],
        ];
    }
}
