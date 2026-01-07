<?php

namespace  App\Src\Traitement\Utilitaire;

use App\Traitement\Utilitaire\Utilitaire;

class HtmlVue
{
    public static function classement(mixed ...$donnees): string
    {
        $logo = Utilitaire::afficher_image_circulaire(path: $donnees[2], class: $donnees[13]);
        $dernier = HtmlVue::icones(donnees: $donnees[12]);
        return <<<HTML
        <tr class="{$donnees[0]}">
            <td class = "{$donnees[14]} {$donnees[15]}">
                {$donnees[1]}
            </td>
            <td class ="{$donnees[14]}">
                $logo
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[3]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[4]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[5]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[6]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[7]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[8]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[9]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[10]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[11]}
            </td>
            <td class ="{$donnees[14]}">
               $dernier
            </td>
        </tr>
HTML;
    }

    public static function icone(int $code): string
    {
        $retour = "";
        switch ($code) {
            case Utilitaire::VICTOIRE:
                $retour = '<i class="typcn typcn-input-checked text-success"></i>';
                break;
            case Utilitaire::NUL:
                $retour = '<i class="typcn typcn-minus text-dark"></i>';
                break;
            case Utilitaire::DEFAITE:
                $retour = '<i class="typcn typcn-delete text-danger"></i>';
                break;
        }

        return $retour;
    }

    public static function icones(array $donnees): string
    {
        $retour = "";

        foreach ($donnees as $data) {
            $retour .= HtmlVue::icone(code: $data);
        }

        return $retour;
    }

    public static function card(array $donnees): string
    {
        return <<<HTML

        <div class="card">
			<!-- Entête de l'accordion -->
			<div class="card-header" id="headingOne">
				<div class="row mb-0">

					<div class="col-lg-3">
						<div class="row">
							<div class="col-lg-12">
								<h1 style="margin:0px;padding:0px;padding-left:20px;font-seize:3em;">1</h1>
							</div>
							<div class="col-lg-12">
								<button style="text-decoration:none;" class="btn btn-link btn-block text-left btn_orange" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
									PREMIER LEAGUE
								</button>
							</div>
						</div>
					</div>

					<div class="col-lg-3">
						<i class="typcn typcn-device-desktop h1"></i>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4">
						<hr class="hr_tb" />
					</div>
				</div>
			</div>

			<!-- Contenu accordion  -->
			<div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionPremier">
				<div class="card-body card_o">
					<div class="row">
						<div class="col-lg-6" id="stat_classement">
							<table>
								<tr class="t_tr">
									<th class="t_th">
										Classement
									</th>
									<th class="t_th">
										1EM
									</th>
									<th class="t_th">
										2EM
									</th>
									<th class="t_th">
										TR
									</th>
									<th class="t_th">
										%1EM
									</th>
									<th class="t_th">
										%2EM
									</th>
									<th class="t_th">
										[80-100%]
									</th>
								</tr>
								<tr class="t_tr_td">
									<td class="t_td">
										1-1
									</td>
									<td class="t_td">
										5
									</td>
									<td class="t_td">
										2
									</td>
									<td class="t_td">
										7
									</td>
									<td class="t_td">
										80%
									</td>
									<td class="t_td">
										20%
									</td>
									<td class="t_td">
										<i class="typcn typcn-input-checked text-success h2"></i>
									</td>
								</tr>											
							</table>
						</div>

						<div class="col-lg-6">

							<div class="row">
								<div class="col-lg-12">
									<h3 class="h_3">Les 2 équipes se marquent ou pas</h3>
								</div>
							</div>

							<div class="row mb-3">                                
								<div class="col-lg-12 d_b">
									<div class="row h_1">
										<div class="col-lg-12 text-center">
											(1er) <strong class="s_b">Arsenal</strong> <img style="width:40px;" src="{{asset('images/logo_defaut.png')}}">
											<span class="score">VS</span>
											<img style="width:40px;" src="{{asset('images/logo_defaut.png')}}"> <strong <strong class="s_b">Wolves</strong> (17e)
										</div>													
									</div>												

									<div class="row text-center h_1">
										<div class="col-lg-12">
											Lundi, 10 Janvier 2026 à 16:30
										</div>									
									</div>

									<div class="row h_1">
										<div class="col-lg-12 ">
											<table style="width:100%; text-align:center;">
												<tr>
													<th>Journée</th>
													<th>Classement</th>
													<th>[90-100%]</th>
													<th>Pari</th>
												</tr>
												<tr>
													<td>2e</td>
													<td>1-4</td>
													<td class="text-warning"><strong>95%</strong></td>
													<td class="text-danger"><strong>1EM</strong></td>
												</tr>															
											</table>
										</div>																				
									</div>											

								</div>											
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

HTML;
    }
}
