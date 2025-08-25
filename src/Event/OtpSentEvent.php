<?php

namespace App\Event;

use App\Entity\AuthSession;
use Symfony\Contracts\EventDispatcher\Event;

class OtpSentEvent extends Event
{
    public const string NAME = 'app.auth_session.otp_sent';
    
    public function __construct(private AuthSession $authSession)
    {
    }
    
    public function getAuthSession(): AuthSession
    {
        return $this->authSession;
    }
    
    public function getPhone(): string
    {
        return $this->authSession->getPhone();
    }
}