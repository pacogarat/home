<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TagRepository extends EntityRepository
{
    public function getTagsFromArray($array) {
        $result = $this->getEntityManager()->createQuery(
            'SELECT t FROM AppBundle:Tag t WHERE t.id in (:tagsarray)'
        )
            ->setParameter('tagsarray', $array)
            ->execute();
        return $result;

    }
}
