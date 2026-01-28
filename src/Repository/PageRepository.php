<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementPage;
use App\Traitement\Controlleur\ControlleurPage;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function new(): Page
    {
        return new Page;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementPage(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurPage; 
        $this->setControlleur(controlleur: $objet);
    }

    public function pages() : array
    {
        return $this->findAll();
    }

    public function page(int $id_page) : ?Page
    {
        return $this->findOneBy(criteria: ['id' => $id_page]);
    }
}
