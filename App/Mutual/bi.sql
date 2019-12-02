/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  aurelien
 * Created: 2 déc. 2019
 */



select *
FROM commande_services a
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72268 rows in set (0.31 sec)

select *
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72268 rows in set (0.38 sec)

select *
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;


--72268 rows in set (0.47 sec)



select *
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
INNER JOIN service e ON a.idService = e.idService 
INNER JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--54442 rows in set (0.60 sec)


select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
INNER JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--54442 rows in set (0.40 sec)



select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
INNER JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--54458 rows in set (0.41 sec)


select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72268 rows in set (0.45 sec)


select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
INNER JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
INNER JOIN ont h ON g.idONT = h.idONT 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;

--54444 rows in set (0.54 sec)

select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
INNER JOIN ont h ON g.idONT = h.idONT 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--54444 rows in set (0.54 sec)


select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
INNER JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;

--54444 rows in set (0.51 sec)



select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
---72268 rows in set (0.56 sec)



select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72268 rows in set (0.69 sec)




select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
  LEFT JOIN `local` m on m.idlocal = l.idlocal 
  LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
  INNER JOIN adresse o ON o.idAdresse = a.idAdresse 
 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--69025 rows in set (1.02 sec)


select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
  LEFT JOIN `local` m on m.idlocal = l.idlocal 
  LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
  LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72268 rows in set (0.98 sec)



select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
  LEFT JOIN `local` m on m.idlocal = l.idlocal 
  LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
  LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
WHERE a.idOperateur not in (8, 9, 10, 20, 24) AND a.idCommandeServices IS NOT NULL
GROUP BY a.idCommandeServices;

--72268 rows in set (1.00 sec)



select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
INNER JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;

--72213 rows in set (1.31 sec)


select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72263 rows in set (1.25 sec)




select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
LEFT JOIN equipement u ON u.idEquipement = l.Parent 
LEFT JOIN equipement v ON v.idEquipement = u.Parent 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72263 rows in set (1.32 sec)



select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
INNER JOIN equipement u ON u.idEquipement = l.Parent 
LEFT JOIN equipement v ON v.idEquipement = u.Parent 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--30344 rows in set (1.03 sec)




select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
LEFT JOIN equipement u ON u.idEquipement = l.Parent 
LEFT JOIN equipement v ON v.idEquipement = u.Parent 
LEFT JOIN SousTypeObjet w ON w.id = u.idSousTypeObjet -- ajout du 24/09 Besoin export S
LEFT join otactivation x ON x.idcommandeservices = a.idCommandeServices 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72263 rows in set (1.55 sec)





select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
LEFT JOIN equipement u ON u.idEquipement = l.Parent 
LEFT JOIN equipement v ON v.idEquipement = u.Parent 
LEFT JOIN SousTypeObjet w ON w.id = u.idSousTypeObjet -- ajout du 24/09 Besoin export S
LEFT join otactivation x ON x.idcommandeservices = a.idCommandeServices 
LEFT join olt y on y.idolt = x.idolt 
LEFT JOIN cr z ON a.idCommandeServices = z.idcommandeservices 
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--72263 rows in set (1.72 sec)





select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
LEFT JOIN equipement u ON u.idEquipement = l.Parent 
LEFT JOIN equipement v ON v.idEquipement = u.Parent 
LEFT JOIN SousTypeObjet w ON w.id = u.idSousTypeObjet -- ajout du 24/09 Besoin export S
LEFT join otactivation x ON x.idcommandeservices = a.idCommandeServices 
LEFT join olt y on y.idolt = x.idolt 
LEFT JOIN cr z ON a.idCommandeServices = z.idcommandeservices 


  LEFT join Stack stc on stc.id = z.idstack 
  LEFT join carte crt on crt.idcarte = z.idCarte 
  INNER JOIN ot ON ot.idOT = a.idOt

WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;
--48465 rows in set (1.55 sec)


select a.*
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
LEFT JOIN equipement u ON u.idEquipement = l.Parent 
LEFT JOIN equipement v ON v.idEquipement = u.Parent 
LEFT JOIN SousTypeObjet w ON w.id = u.idSousTypeObjet -- ajout du 24/09 Besoin export S
LEFT join otactivation x ON x.idcommandeservices = a.idCommandeServices 
LEFT join olt y on y.idolt = x.idolt 
LEFT JOIN cr z ON a.idCommandeServices = z.idcommandeservices 
LEFT join Stack stc on stc.id = z.idstack 
LEFT join carte crt on crt.idcarte = z.idCarte 
LEFT JOIN ot ON ot.idOT = a.idOt
LEFT JOIN histo_etape_commande_service aa ON a.idCommandeServices =  aa.idcommandeservices  
WHERE a.idOperateur not in (8, 9, 10, 20, 24)
GROUP BY a.idCommandeServices;

--72263 rows in set (2.10 sec)






select a.idCommandeServices, group_concat(idEtapecommandeservice,":",aa.datedebut)
FROM commande_services a
INNER JOIN reseau b ON a.idreseau = b.idReseau 
INNER JOIN dsp c ON b.idDsp = c.idDSP 
INNER JOIN operateur d ON a.idOperateur = d.idOperateur 
LEFT JOIN service e ON a.idService = e.idService 
LEFT JOIN catalogue_service f ON e.idcatalogueservice = f.idCatalogueService 
LEFT JOIN port_ethernet g ON e.idPortEthernet = g.idPortEthernet 
LEFT JOIN ont h ON g.idONT = h.idONT 
LEFT JOIN type_ont i on h.idtypeont = i.idtypeont 
LEFT JOIN route_optique j ON a.idRoute = j.idRO
LEFT JOIN port_pto k ON j.idPortpto = k.id 
LEFT JOIN equipement l ON l.idEquipement = k.idEquipement 
LEFT JOIN `local` m on m.idlocal = l.idlocal 
LEFT JOIN batiment n ON m.idBatiment = n.idBatiment 
LEFT JOIN adresse o ON o.idAdresse = a.idAdresse 
LEFT JOIN type_voie p ON p.idTypeVoie = o.idTypeVoie 
LEFT JOIN ville q ON q.idVille = o.idVille 
LEFT JOIN code_postal r ON r.idcode_postal = o.idcodepostal 
LEFT JOIN flux_commande_services s ON a.idcommande = s.idCommande 
INNER JOIN type_commande t ON a.idTypeCommande = t.idTypeCommande 
LEFT JOIN equipement u ON u.idEquipement = l.Parent 
LEFT JOIN equipement v ON v.idEquipement = u.Parent 
LEFT JOIN SousTypeObjet w ON w.id = u.idSousTypeObjet -- ajout du 24/09 Besoin export S
LEFT join otactivation x ON x.idcommandeservices = a.idCommandeServices 
LEFT join olt y on y.idolt = x.idolt 
LEFT JOIN cr z ON a.idCommandeServices = z.idcommandeservices 
LEFT join Stack stc on stc.id = z.idstack 
LEFT join carte crt on crt.idcarte = z.idCarte 
LEFT JOIN ot ON ot.idOT = a.idOt
LEFT JOIN histo_etape_commande_service aa ON a.idCommandeServices =  aa.idcommandeservices  
LEFT JOIN etape_commande_service etcs ON aa.idEtapecommandeservice = etcs.id 
WHERE a.idOperateur not in (8, 9, 10, 20, 24) and aa.idEtapecommandeservice in (1,5,6,15,22,28)
GROUP BY a.idCommandeServices;

--72263 rows in set, 56 warnings (3.71 sec)


mysql> show warnings;
+---------+------+--------------------------------------+
| Level   | Code | Message                              |
+---------+------+--------------------------------------+
| Warning | 1260 | Row 46794 was cut by GROUP_CONCAT()  |
| Warning | 1260 | Row 126387 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 237073 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 237481 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 238206 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 264645 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 297717 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 308194 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 309287 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 345839 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 349111 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 358854 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 369967 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 371467 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 371701 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 373916 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 377433 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 384300 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 387010 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 387694 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 388686 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 389236 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 389784 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 389836 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 390215 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 390663 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 390998 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 392540 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 392666 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 392916 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 402746 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 402967 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 403241 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 403485 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 408272 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 410109 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 410263 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 410412 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 410510 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 417553 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 439510 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 440745 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 447410 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 447805 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 448280 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 453801 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 463365 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 464581 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 486090 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 487146 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 491345 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 504652 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 507132 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 509174 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 511554 was cut by GROUP_CONCAT() |
| Warning | 1260 | Row 514345 was cut by GROUP_CONCAT() |
+---------+------+--------------------------------------+
56 rows in set (0.00 sec)