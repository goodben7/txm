<?php
namespace App\Event;

use App\Model\RessourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ActivityEvent extends Event {

    const ACTION_CREATE = 'created';
    const ACTION_VIEW = 'viewed';
    const ACTION_LIST = 'listed';
    const ACTION_EDIT = 'edited';
    const ACTION_DELETE = 'deleted';

    private ?string $ressourceClass;

    public function __construct(private ?RessourceInterface $ressource, private string $activity, ?string $ressourceClass = null)
    {
        $this->ressourceClass = $ressourceClass;

        if (null !== $ressource) {
            $this->ressourceClass = get_class($ressource);
        }

        if (!$this->ressourceClass) {
            throw new \InvalidArgumentException("ressource class name must be specified");
        }
    }

    /**
     * Get the value of ressource
     */ 
    public function getRessource(): ?RessourceInterface
    {
        return $this->ressource;
    }

    /**
     * Get the value of activity
     */ 
    public function getActivity(): string
    {
        return $this->activity;
    }

    /**
     * Get the value of ressourceClass
     */ 
    public function getRessourceClass(): string
    {
        return $this->ressourceClass;
    }

    public static function getEventName(string $ressourceFqcn, string $action): string {
        return sprintf('app.%s.%s', strtolower(str_replace('\\', '_', $ressourceFqcn)), $action);
    }
}