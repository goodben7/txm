<?php

namespace App\Service;

use App\Contract\NotificationSenderInterface;
use App\Entity\Notification;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class WhatsappNotificationSender implements NotificationSenderInterface
{
    private const string ULTRAMSG_API_URL = 'https://api.ultramsg.com';

    public function __construct(
        private HttpClientInterface $client,
        private string              $whatsappApiKey,
        private string              $whatsappInstanceId
    )
    {
    }

    public function send(Notification $notification): void
    {
        $endpoint = self::ULTRAMSG_API_URL . "/{$this->whatsappInstanceId}/messages/chat";

        try {
            $response = $this->client->request('POST', $endpoint, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'token' => $this->whatsappApiKey,
                    'to' => $this->formatPhoneNumber($notification->getTarget()),
                    'body' => $this->formatMessage($notification),
                    'verify_peer' => false,
                    'verify_host' => false
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                $content = $response->getContent(false);
                throw new \RuntimeException('Failed to send Whatsapp message: ' . $content);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Whatsapp service error: ' . $e->getMessage());
        }
    }
    
    /**
     * Format the message with notification data
     */
    private function formatMessage(Notification $notification): string
    {
        $message = $notification->getBody();
        $data = $notification->getData();
        
        if (empty($data)) {
            return $message;
        }
        
        // Add title if available
        if ($notification->getTitle()) {
            $message = "*{$notification->getTitle()}*\n\n" . $message;
        }
        
        $message .= "\n\n";
        
        // Format based on notification type
        if ($notification->getType() === 'dlv_cre') {
            // Special formatting for delivery notifications
            if (isset($data['Numéro de suivi']) || isset($data['Numéro'])) {
                $trackingNumber = $data['Numéro de suivi'] ?? $data['Numéro'] ?? '';
                $message .= "*NUMÉRO DE SUIVI*: {$trackingNumber}\n";
            }
            
            if (isset($data['Date prévue']) || isset($data['Date'])) {
                $date = $data['Date prévue'] ?? $data['Date'] ?? '';
                $message .= "*DATE DE LIVRAISON*: {$date}\n";
            }
            
            if (isset($data['Type'])) {
                $message .= "*TYPE*: {$data['Type']}\n";
            }
            
            if (isset($data['Statut'])) {
                $message .= "*STATUT*: {$data['Statut']}\n";
            }
            
            $message .= "\n";
            
            if (isset($data['Adresse de ramassage'])) {
                $message .= "*Adresse de ramassage*:\n{$data['Adresse de ramassage']}\n\n";
            }
            
            if (isset($data['Adresse de livraison']) || isset($data['Adresse'])) {
                $address = $data['Adresse de livraison'] ?? $data['Adresse'] ?? '';
                $message .= "*Adresse de livraison*:\n{$address}\n\n";
            }
            
            if (isset($data['Description'])) {
                $message .= "*Description*:\n{$data['Description']}\n\n";
            }
            
            if (isset($data['Informations supplémentaires']) || isset($data['Informations'])) {
                $info = $data['Informations supplémentaires'] ?? $data['Informations'] ?? '';
                $message .= "*Informations supplémentaires*:\n{$info}\n";
            }
        } else {
            // Standard formatting for other notification types
            $message .= "*Informations complémentaires*:\n";
            foreach ($data as $key => $value) {
                if ($key !== 'action_url' && $key !== 'action_text') {
                    $message .= "- {$key}: *{$value}*\n";
                }
            }
        }
        
        // Add action URL if available
        if (isset($data['action_url']) && !empty($data['action_url'])) {
            $actionText = $data['action_text'] ?? 'Voir les détails';
            $message .= "\n{$actionText}: {$data['action_url']}\n";
        }
        
        return $message;
    }

    public function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        return $cleaned;
    }

    public function support(string $sentVia): bool
    {
        return $sentVia === Notification::SENT_VIA_WHATSAPP;
    }
}