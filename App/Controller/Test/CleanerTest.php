<?php

namespace App\Controller\Test;

use PHPUnit\Framework\TestCase;
use \App\Controller\Cleaner;
use \Glial\Synapse\FactoryController;
use \App\Library\Debug;
use \Glial\Synapse\Controller;


class CleanerTest extends TestCase
{

    static public function mydb()
    {
        $data = '{"statut_position":["position"],"pon":["position","cr","otacquittement","otresiliation","otactivation"],"code_postal":["adresse","historique_ipe","HistoriqueCRMADPM","HistoriqueOntEligibilite","ville_cp"],"type_voie":["adresse","compte"],"rivoli":["adresse"],"ville":["adresse","historique_ipe","HistoriqueCRMADPM","HistoriqueOntEligibilite","reporting_data","ville_cp"],"statut_adresse":["adresse"],"type_adresse":["adresse"],"site":["adresse","adduction_site","document","batiment","projet_deploiement_site"],"etape_racco":["histo_racco_commande_service"],"user":["histo_racco_commande_service","CommentaireInterneProtocolePM","histoProtocolePM","historique_ipe","document","HistoriqueCRMADPM","commentaire_commande","histo_etape_commande_service","HistoriqueOntEligibilite","projet_deploiement_site","etape_deploiement","etape_deploiement","otacces","reporting","enregistrement","corbeille","reseau_users","otelligibiliteservice","otelligibilite","ot","version_document","Creneau"],"commande_services":["histo_racco_commande_service","CrRaccordement","NotifReprovisionning","CrStoc","cr","NotifRaccoKo","ArCommandeAcces","otacquittement","commentaire_commande","histo_etape_commande_service","task","ligne_de_commande","otresiliation","CrCommandeAcces","CrmadAcces","ar_commande_services","AnnulationCommandeAcces","otacces","otelligibiliteservice","CmdStoc","CrAnnulationCommandeAcces","otelligibilite","ot","otactivation","crmad_services","crmes","NotifEcrasement","Creneau"],"CodeErreur":["histo_racco_commande_service","histoProtocolePM","histo_etape_commande_service","ar_commande_services","ar_commande_services","ot"],"compte":["contact","batiment_compte_convention","site","convention"],"type_contact":["contact"],"cassette_modele":["cassette","cassette_modele_equipement_modele"],"emplacement_cassette":["cassette"],"chemin":["adduction_site"],"escalier":["etage","colonne_montante","otacces","otelligibiliteservice","otelligibilite","ot","local"],"dsp":["reseau","historique_ipe","HistoriqueCRMADPM","HistoriqueOntEligibilite","reporting_data","Creneau"],"type_planning":["planning"],"ot":["cr","commande_services","Intervention"],"Stack":["cr","otacquittement"],"type_ont":["cr","template","ont"],"ont":["cr","commande_services","commande_acces","ot","port_ethernet"],"carte":["cr","otacquittement","otresiliation","pon","otactivation"],"olt":["cr","otacquittement","otresiliation","Stack","carte","otactivation"],"cmdInfoPM":["CommentaireInterneProtocolePM","histoProtocolePM","arCmdInfoPM"],"HistoriqueCRMADPM":["CrMadPM","crmadpm_30","crmadpm_22"],"statutProtocolePM":["histoProtocolePM"],"type_materiel_pm":["equipement"],"equipement":["equipement","route_optique","lien_pmr_pmt","lien_pmr_pmt","cmdInfoPM","tube","document","batiment","emplacement_cassette","HistoriqueCRMADPM","commande_services","site","otacces","service_passif","otelligibiliteservice","otelligibilite","ot","port_pto"],"typePBO":["equipement"],"status":["equipement"],"SousTypeObjet":["equipement","otacces","ot"],"planning":["equipement"],"equipement_modele":["equipement"],"local":["equipement","abonne","document","commande_services","commande_acces","otacces","otelligibiliteservice","otelligibilite","ot","chemin","chemin"],"reseau":["equipement","historique_ipe","cmdInfoPM","HistoriqueCRMADPM","commande_services","template","commande_acces","HistoriqueOntEligibilite","site","catalogue_service","reseau_users"],"type_objet":["equipement"],"objet":["champs"],"cable":["extraction","tube"],"type_compte":["compte"],"type_etape_deploiement":["transition_etape_deploiement","transition_etape_deploiement","etape_deploiement"],"constructeur":["route_optique"],"port_pto":["route_optique","otacces","otelligibiliteservice","otelligibilite","ot"],"operateur":["route_optique","cmdInfoPM","HistoriqueCRMADPM","commande_services","template","commande_acces","operateur_contact","catalogue_service","reporting_data"],"service_passif":["route_optique","commande_services"],"StatutRouteOptique":["route_optique"],"service":["route_optique","commande_services","commande_acces"],"version_flux":["historique_ipe","cmdInfoPM","HistoriqueCRMADPM"],"FichierProtocolePM":["cmdInfoPM"],"extraction":["tube","tube"],"couleur":["tube","couleur_table_couleur","fibre"],"type_document":["document"],"convention":["document","batiment_compte_convention"],"statut_document":["document"],"batiment":["document","escalier","batiment_compte_convention","otacces","batiment_contact","otelligibiliteservice","adresse_batiment","otelligibilite","ot","local"],"type_projection_geographique":["batiment"],"type_batiment":["batiment","otacces","ot"],"StatutBatiment":["batiment"],"position":["otacquittement","otresiliation","ont","otactivation"],"port_ethernet":["otacquittement","otresiliation","otactivation","service"],"catalogue_service":["parametres","template","service_passif","catalogue_service_factu","service"],"route_optique":["commande_services","otacces","otacces","service_passif","otelligibiliteservice","ont","otelligibilite","ot","ot","service","lien_route_optique"],"client":["commande_services","otacces","ot","local"],"AnnulationCommandeAcces":["commande_services"],"statut_commande":["commande_services","commande_acces"],"otacces":["commande_services","CrCommandeAcces"],"flux_commande_acces":["commande_services","commande_acces"],"type_commande":["commande_services","commande_acces"],"flux_commande_services":["commande_services"],"adresse":["commande_services","commande_acces","adresse_batiment","exclusion_adresse"],"etape_commande_service":["histo_etape_commande_service"],"etape_commande_service_secondaire":["histo_etape_commande_service"],"version_soft":["template","olt"],"template":["ligne_de_commande"],"commande":["ar_commande"],"port":["lien_optique","lien_optique","port_pto"],"type_lien_optique":["lien_optique"],"HistoriqueOntEligibilite":["OntEligibilite"],"type_site":["site"],"table_couleur":["couleur_table_couleur","cable_modele","cassette_modele_equipement_modele"],"action":["droit"],"etape":["droit","corbeille"],"valeur":["droit"],"champs":["droit","enregistrement"],"projet_deploiement_site":["etape_deploiement"],"type_adduction":["otacces","ot","local"],"etage":["otacces","otelligibiliteservice","otelligibilite","ot","local"],"CrRaccordement":["otacces"],"installateur":["otacces","ot","Creneau"],"societe":["dsp"],"contact":["batiment_contact"],"historique_ipe":["ipe","ipe_30","ipe_22"],"service_corbeille":["corbeille"],"type_erreur":["corbeille"],"statut_carte_port":["pon","carte"],"colonne_montante":["cable","chemin"],"type_cable":["cable"],"cable_modele":["cable"],"lien_optique":["fibre","lien_route_optique"],"tube":["fibre"],"statut_convention":["convention"],"ar_commande":["crmad"],"reporting":["reporting_data"],"tva":["catalogue_service_factu"],"configuration":["ont"],"statut_ont":["ont"],"type_carte":["carte"],"cr":["ot","Intervention"],"cassette":["port"],"type_chemin":["chemin"],"type_local":["local"],"document":["version_document"],"fichier":["version_document"],"statusCreneau":["Creneau"],"TypeIntervention":["Creneau"],"statutportpto":["port_pto"],"statut_port":["port_ethernet"]}';
        return $data;
    }

    public function testPushAndPop()
    {
        $stack = [];
        $this->assertSame(0, count($stack));

        array_push($stack, 'foo');
        $this->assertSame('foo', $stack[count($stack) - 1]);
        $this->assertSame(1, count($stack));

        $this->assertSame('foo', array_pop($stack));
        $this->assertSame(0, count($stack));
    }

    static public function testGetOrderBy()
    {

        
        $data = json_decode(self::mydb(), true);
        
        //Debug::debug($data);
        
        
        $node = new Controller("Cleaner", "getOrderBy2", json_encode(array($data, "ipe", 'ASC')));
        $node->setOut(FactoryController::RESULT);
        $node->recursive = true;

        echo $node->getController();
        
        //$node->display();
        
        
        
        
        //Debug::debug($res);

        
        //

    }
}