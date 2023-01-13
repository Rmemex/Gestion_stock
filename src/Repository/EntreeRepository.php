<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Entree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entree|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entree|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entree[]    findAll()
 * @method Entree[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entree::class);
    }

    public function filterEntreeByFrn($dates, $fournisseur){
        return $this->createQueryBuilder('e')
            ->andWhere('e.fournisseurId :idFrn and e.entr_date: dates')
            ->setParameter('idFrn', $fournisseur)
            ->setParameter('dates', $dates)
            // ->setParameters(['idFrn' => $fournisseur, 'dates'=> $date])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Entree[]
     */
    public function findSearch($date, $fournisseur): array
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('f', 'e')
            ->join('e.fournisseur', 'f');

        if (!empty($date)) {
            $query = $query
                ->andWhere('e.entr_date IN (:date)')
                ->setParameter(':date', $date);
        }

        if (!empty($fournisseur)) {
            $query = $query
                ->andWhere('f.id IN (:fournisseur)')
                ->setParameter(':fournisseur', $fournisseur);
        }
        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return Entree[]
    //  */
    // public function findSearch($date, $fournisseur): array
    // {
    //     $query = $this
    //         ->createQueryBuilder('e')
    //         ->select('f', 'e')
    //         ->join('e.fournisseur', 'f');

    //     if (!empty($date) && !empty($fournisseur)) {
    //         $query = $query
    //             ->andWhere('e.entr_date IN (:date) AND f.id IN (:fournisseur)')
    //             ->setParameter(':date', $date)
    //             ->setParameter(':fournisseur', $fournisseur);
    //     }
    //     return $query->getQuery()->getResult();
    // }

    public function filterByDate()
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e.entr_date');
        return $query->getQuery()->getResult();
    }

    // public function getSearch($fournisseur, $date)
    // {   
    //     $query = $this->_em->createQuery('
    //         SELECT e FROM App\Entity\Entree e 
    //         WHERE e.fournisseur_id=idFrn And e.entr_date=dates '
    //     )
    //         ->setParameter('idFrn',$fournisseur)
    //         ->setParameter('dates',$date);
    //     return $query->execute();
    // }   

    // /**
    //  * @return Entree[] Returns an array of Entree objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Entree
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
