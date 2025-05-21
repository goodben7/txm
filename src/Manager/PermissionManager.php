<?php

namespace App\Manager;

use App\Model\Permission;

class PermissionManager
{
    private static ?PermissionManager $instance = null;

    private ?string $projectDir = null;

    public function __construct()
    {
        if (null === $this->projectDir) {
            $r = new \ReflectionObject($this);

            if (!is_file($dir = $r->getFileName())) {
                throw new \LogicException(sprintf('Cannot auto-detect project dir for kernel of class "%s".', $r->name));
            }

            $dir = $rootDir = \dirname($dir);
            while (!is_file($dir . '/composer.json')) {
                if ($dir === \dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = \dirname($dir);
            }
            $this->projectDir = $dir;
        }
    }

    public static function getInstance(): PermissionManager
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return array<Permission>
     */
    public function getPermissions(): iterable
    {
        $list = require sprintf('%s/config/permissions.php', $this->projectDir);

        return $list();
    }

    public function getPermissionsAsListChoices(): iterable
    {
        $choices = [];
        /** @var Permission $p */
        foreach ($this->getPermissions() as $p) {
            $choices[$p->getLabel()] = $p->getPermissionId();
        }

        return $choices;
    }
}