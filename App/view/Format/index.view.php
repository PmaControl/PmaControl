<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Format SQL') ?></h3>
        </div>
        <div class="well">
            <div class="row">
                <div class="col-md-12">
                    Type your SQL here:<br />
                </div>
                <div class="col-md-12">
                    <textarea name="sql" rows="5" class="form-control"><?php
                        if (!empty($data['sql'])) {
                            echo $data['sql'];
                        }
                        ?></textarea>
                </div>
                <div class="col-md-12">
                    <br />
                    <input class="btn btn-primary" type="submit" value="Submit">

                </div>
            </div>
        </div>
    </div>
</form>

<?php
use \App\Library\Debug;

if (!empty($data['sql_formated'])) {
    //echo '<pre style="color: black; background-color: white;">';

    echo SqlFormatter::highlight($data['sql']);
    echo SqlFormatter::format($data['sql']);
    //echo '<hr>';

   // echo PhpMyAdmin\SqlParser\Utils\Formatter::format($data['sql'], array('type' => 'html'));

    foreach ($data['sql_formated'] as $query) {

        //echo $query;

        //strip_tags($query, '<span><br>')
        //echo str_replace('<br/><br/>', "<br/>", $query);
    }

    //echo '</pre>';
}


/*

echo \SqlFormatter::format("SELECT
    dsp.nomDsp 'DSP',
    res.nom 'Reseau',
    dsp.codeoi 'Code OI',
    pto.Reference 'Reference Prise',
    spto.libelle 'Statut PTO',
    ppto.reference 'Port PTO',
    l.codeLocal 'Référence Local',
    ''  AS 'Conditions Syndic',
    b.reference 'Référence Batiment',
    b.nom 'Nom Batiment',
    sb.libelle 'Statut Batiment',
    DATE_FORMAT(b.dateelligibilite, '%Y%m%d') 'Date Eligibilite Batiment',
    tb.libelle 'Type Batiment',
    DATE_FORMAT(c.date_signature, '%Y%m%d') 'Date Signature Convention',
    (CASE b.accordGestionnaireNecessaire WHEN 0 THEN '' ELSE 'Oui' END) AS 'Accord Gestionnaire Nécessaire',
    '' AS 'Nom Gestionnaire',
    DATE_FORMAT(b.dateMiseEnServiceCommercialeImmeuble, '%Y%m%d') 'Date MES Commerciale Batiment',
    b.immeubleNeuf 'Immeuble Neuf',
    DATE_FORMAT(b.datePrevLivraisonImmeubleNeuf, '%Y%m%d') 'Date Previsionnelle Livraison Immeuble Neuf',
    b.nombreLogement 'Nb Logements Batiment',
    b.longueurLigneImmeuble 'Longueur Ligne Immeuble',
    b.susceptibleRaccordableDemande 'Raccordable à la demande',
    'RGF93' as 'Type Projection Geo',
    b.coordonneeX 'CoordonnéeX',
    b.coordonneeY 'CoordonnéeY',
    b.codeInsee 'Insee',
    r.codeRivoli 'Rivoli',
    a.idra 'Code Hexacle',
    a.numeroVoie 'Numero Voie',
    a.complementNumeroVoie 'Complement Numero Voie',
    tv.libelle 'Type Voie', a.nomVoie 'Libelle Voie',
    a.codePostal 'Code Postal',
    a.commune 'Commune',
    a.codeHexacleVoie 'Code HexaVoie',
    a.codeBan 'Code BAN',
    a.type_zone 'Type Zone',
    s.refSite 'Reference Site',
    esc.reference 'Reference Escalier',
    esc.libelle 'Escalier',
    etg.libelle 'Reference Etage',
    etg.info 'Etage',
    cm.reference 'Colonne montante',
    l.Porte 'Porte',
    ont.refONT 'Reference ONT',
    sont.libelle 'Statut ONT',
    ro.route 'Route Optique',
    sro.libelle 'Statut ROP',
    ro.codeCableBB 'Cable',
    cam.reference 'TypeCable',
    ro.armoirecoupleur 'Armoire Coupleur',
    ro.refCoupleur 'Référence Coupleur',
    ro.sortiecoupleur 'Sortie Coupleur',
    ro.baieOptique 'Référence Armoire PM',
    ro.Tiroir 'Tiroir PM',
    ro.Ligne 'Ligne',
    ro.Colonne 'Colonne',
    ro.tubeCableRacco 'Numero Tube',
    ro.couleurTubeCableRacco 'Couleur Tube',
    ro.foCableRacco 'Numero Fibre',
    ro.couleurFoCableRacco 'Couleur Fibre',
    pm.Reference 'Référence PM',
    pmr.Reference 'Référence PMR',
    pbo.Reference 'Référence PBO',
    tpbo.Libelle 'Type PBO',
    pbo.localisation 'Localisation PBO',
    ta.Libelle 'Type Racco PB PTO',
    DATE_FORMAT(pbo.DateInstallation, '%Y%m%d') 'Date MAD PBO',
    ''  AS 'DOE',
    l.info 'Commentaire'
    FROM equipement pto
    LEFT JOIN status spto ON spto.idStatuts = pto.idStatuts
    LEFT JOIN port_pto ppto ON ppto.idEquipement = pto.idEquipement
    LEFT JOIN local l ON l.idLocal = pto.idLocal
    LEFT JOIN TypeAdduction ta ON ta.id = l.idTypeAdduction
    LEFT JOIN equipement pbo ON pbo.idEquipement = pto.Parent
    LEFT JOIN equipement pm ON pm.idEquipement = pbo.Parent
    -- LEFT JOIN batiment b ON b.idBatiment = l.idBatiment
    -- LEFT JOIN equipement pm ON pm.idEquipement = b.pm_id
    LEFT JOIN lien_pmr_pmt lpm ON lpm.pmt_idEquipement = pm.idEquipement
    LEFT JOIN equipement pmr ON pmr.idEquipement = lpm.pmr_idEquipement
    LEFT JOIN SousTypeObjet tpbo ON pbo.idSousTypeObjet = tpbo.id
    LEFT JOIN batiment b ON b.idBatiment = l.idBatiment
    LEFT JOIN TypeProjectionGeographique tpg ON tpg.id = b.type_projection_geographique_id
    LEFT JOIN StatutBatiment sb ON sb.idstatutbatiment = b.idstatutbatiment
    LEFT JOIN type_batiment tb ON tb.idTypeBatiment = b.idTypeBatiment
    LEFT JOIN batiment_compte_convention bcc ON bcc.batiment_idBatiment = b.idBatiment
    LEFT JOIN convention c ON c.id = bcc.convention_id
    LEFT JOIN compte g ON g.id = bcc.compte_id
    LEFT JOIN adresse_batiment ab ON (ab.batiment_idBatiment = b.idBatiment AND ab.adresse_principale = 1)
    LEFT JOIN adresse a ON a.idAdresse = ab.adresse_idAdresse
    LEFT JOIN rivoli r ON r.idRivolie = a.idRivolie
    LEFT JOIN type_voie tv ON tv.idTypeVoie = a.idTypeVoie
    LEFT JOIN site s ON s.idSite = a.idSite
    LEFT JOIN escalier esc ON esc.idbatiment = b.idBatiment
    LEFT JOIN etage etg ON etg.idEscalier = esc.idEscalier
    LEFT JOIN colonne_montante cm ON cm.escalier_id = esc.idEscalier
    LEFT JOIN route_optique ro ON ro.idPortpto = ppto.id
    LEFT JOIN StatutRouteOptique sro ON sro.id = ro.idStatutRO
    LEFT JOIN cable ca ON ca.reference = ro.codeCableBB
    LEFT JOIN cable_modele cam ON cam.id = ca.cable_modele_id
    LEFT JOIN ont ON ont.idRo = ro.idRO
    LEFT JOIN statut_ont sont ON sont.id = ont.idstatutont
    LEFT JOIN reseau res ON res.idReseau = pto.idreseau
    LEFT JOIN dsp ON dsp.idDSP = res.idDsp
    WHERE
    pm.Reference='HT-TEC-0054S'");
 /*
 *
 *
 *
 */