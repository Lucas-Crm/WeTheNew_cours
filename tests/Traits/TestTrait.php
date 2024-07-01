<?php

namespace App\Tests\Traits;

trait TestTrait
{
    public function assertHasErrors(mixed $entity, int $number = 0 ): void
    {
        //Initialise le noyau symfony
        self::bootKernel();

        // On recupere les erreur potentiel
        $errors = self::getContainer()->get('validator')->validate($entity);

        //On instancie un tableau vide pour stocker les erreurs
        $messageErrors = [];

        //On boucle sur les erreurs
        foreach ($errors as $error){
            $messageErrors[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }

        $this->assertCount($number, $errors, implode(', ', $messageErrors));
    }
}