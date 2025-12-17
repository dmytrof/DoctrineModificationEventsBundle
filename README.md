DmytrofDoctrineModificationEventsBundle
====================

This bundle helps you to create and handle events on entities updates for your Symfony 7/8 application

## Installation

### Step 1: Install the bundle

    $ composer require dmytrof/doctrine-modification-events-bundle 
    
### Step 2: Enable the bundle

    <?php
        // config/bundles.php
        
        return [
            // ...
            Dmytrof\DoctrineModificationEventsBundle\DmytrofDoctrineModificationEventsBundle::class => ['all' => true],
        ];
