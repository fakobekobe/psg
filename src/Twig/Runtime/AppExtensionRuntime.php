<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Twig\Environment as TwigE;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\ConfigurationRepository;
use App\Repository\AnneeAcademiqueRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    
    public function __construct(
        private TwigE $twig,
        private TokenStorageInterface $security,
        private ManagerRegistry $registry,
    )
    {
        // Inject dependencies if needed
    }

  public function app_modal_btn(FormView $form, string $titre, string $nomFormulaire = "form", string $nomModal = 'ajouter'): string
  {
    $nomBTN = ucfirst(string: $nomModal);
    return <<<HTML
    <!-- Modal -->
    <div class="modal fade" id="{$nomModal}Backdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="{$nomModal}BackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="{$nomModal}BackdropLabel">$titre</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-form">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">

            {$this->getForm(form: $form, nomFormulaire: $nomFormulaire)}

          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary btn-sm" id="{$nomModal}" name="{$nomModal}">              
              <i class="typcn typcn-plus"></i>
              {$this->getNomClaire(nom: $nomBTN)}
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="annuler{$nomModal}" data-dismiss="modal">
              <i class="typcn typcn-delete"></i> 
              Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal -->

HTML;
  }

  public function app_modal_show(string $titre, string $html = "getHTMLGroupe"): string
  {
    return <<<HTML
    <!-- Modal -->
    <div class="modal fade" id="afficherBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="afficherBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <h4 class="modal-title text-primary" id="afficherBackdropLabel">$titre</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-form">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">

            {$this->$html()}

          </div>
          <div class="modal-footer justify-content-center bg-light">
            <button type="button" class="btn btn-primary btn-sm" id="annulerafficher" data-dismiss="modal">
              <i class="typcn typcn-input-checked"></i> 
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal -->

HTML;
  }

  private function getForm(FormView $form, string $nomFormulaire): string
  {
    return $this->twig->render(name: "_includes/". $nomFormulaire .".html.twig", context: ["form" => $form]);
  }

  /**
   * nom_classe permet de retourner la class de l'utilisateur
   * @return string
   */
  public function nom_classe() : string
  {
    $class = $this->security->getToken()->getUser()::class;
    return strtolower(string: (explode(separator: "\\",string: $class))[2]);
  }

  private function getNomClaire(string $nom) : string
  {
    return (explode(separator: "_",string: $nom))[0];
  }

  public function puce_alphabet(int $key) : string
  {
    $data = ['a','b','c','d','e',
        'f','g','h','i','j','k','l',
        'm','n','o','p','q','r','s',
        't','u','v','w','x','y','z'];
    return $data[$key];
  }

  public function get_contenu(array $liste, string $contenu) : string
  {
    $retour = "";
    foreach($liste['question'] as $data)
    {
      foreach($liste['question'] as $key => $valeur)
      {
        if($valeur == $contenu) 
        {
          $retour = $liste['reponse'][$key];
          break;
        }
      }
      if(trim(string: $retour))
      {
        break;
      }
    }

    return $retour;
  }

  private function getHTMLGroupe(): string
  {
    return <<<HTML
    <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Nom complet :
                  </h5>
              </div>
              <div class="col-lg-6">
                <h5 id="d-nomcomplet">
                  </h5>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Email :
                  </h5>
              </div>
              <div class="col-lg-6">
                <h5 id="d-email">
                  </h5>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Est actif :
                  </h5>
              </div>
              <div class="col-lg-6">
                <h5 id="d-actif">
                  </h5>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Est vérifié :
                  </h5>
              </div>
              <div class="col-lg-6">
                <h5 id="d-verifie">
                  </h5>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Groupes :
                  </h5>
              </div>
              <div class="col-lg-6">
                <p id="d-groupe" class="m-0 p-0">
                </p>
              </div>
            </div>
HTML;
  }

  private function getHTMLUtilisateur(): string
  {
    return <<<HTML
    <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Groupe :
                  </h5>
              </div>
              <div class="col-lg-6">
                <h5 id="d-nom">
                  </h5>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Nombre d'utilisateurs :
                  </h5>
              </div>
              <div class="col-lg-6">
                <h5 id="d-nombre">
                  </h5>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 text-right">
                  <h5>
                    Utilisateurs :
                  </h5>
              </div>
              <div class="col-lg-6">
                <p id="d-utilisateur" class="m-0 p-0">
                </p>
              </div>
            </div>
HTML;
  }

  /*
  public function app_configuration(): array
  {
    $configurationRepo = new ConfigurationRepository(registry: $this->registry);
    $AnneeAcademiqueRepo = new AnneeAcademiqueRepository(registry: $this->registry);
    $data = [];
    $objet = $configurationRepo->new();
    if($objet->getId())
    {
      $anneeCourante = $AnneeAcademiqueRepo->findOneBy(criteria: ['id' => $objet->getAnneeCourante()]);
      $data[0] = $objet->getDenomination();
      $data[1] = $objet->getSigle();
      $data[2] = $objet->getContact();
      $data[3] = $objet->getAdresse();
      $data[4] = $anneeCourante->getLibelle();
    }else{
      $data[0] = null;
      $data[1] = null;
      $data[2] = null;
      $data[3] = null;
      $data[4] = null;
    }
    return $data;
  }
  */
}
