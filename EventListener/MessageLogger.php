<?php

namespace HB\StampieBundle\EventListener;

use Stampie\Extra\Event\MessageEvent;
use Stampie\Extra\StampieEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageLogger implements EventSubscriberInterface
{
    protected $messages = array();

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            StampieEvents::PRE_SEND => 'preSend',
        );
    }

    public function preSend(MessageEvent $event)
    {
        $this->messages[] = $event->getMessage();
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
