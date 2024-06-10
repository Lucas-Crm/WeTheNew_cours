<?php

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RemoveItemCartListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
          FormEvents::POST_SUBMIT => 'onPostSublit',
        ];
    }

    public function onPostSublit(FormEvent $e): void
    {
        $form = $e->getForm();
        $cart = $e->getData();


        if(!$cart){
            return;
        }

        foreach($form->get('orderItems')->all() as $child){
            /**
             * @var ClickableInterface $removeBtn
             */
            $removeBtn = $child->get('remove');

            if($removeBtn->isClicked()){
                $cart->removeOrderItem($child->getData());
                return;
            }


        }

    }

}