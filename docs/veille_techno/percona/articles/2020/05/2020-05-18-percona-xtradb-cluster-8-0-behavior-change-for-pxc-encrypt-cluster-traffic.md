---
title: Percona XtraDB Cluster 8.0 Behavior Change for pxc-encrypt-cluster-traffic
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-8-0-behavior-change-for-pxc-encrypt-cluster-traffic/
  post_id: 22378
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2020-05-18T17:15:50'
published_at_gmt: '2020-05-18T17:15:50'
modified_at: '2026-04-27T21:38:37'
modified_at_gmt: '2026-04-27T21:38:37'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Software
- Percona XtraDB Cluster
tag_slugs:
- mysql
- mysql-and-variants
- percona-software
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-8.0-Behavior-Change-for-pxc-encrypt-cluster-traffic.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster 8.0 Behavior Change for pxc-encrypt-cluster-traffic

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-8-0-behavior-change-for-pxc-encrypt-cluster-traffic/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2020-05-18T17:15:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona has enforced stronger security in Percona XtraDB Cluster (PXC) 8, but this requires some attention during the rollout of the new server version, so let see the why and what. In PXC there are two different kinds of traffic: client-server exchange (ie: application traffic), and replication traffic. The latter refers to any SST/IST, write-set, … Continued

## Structure detectee

- H2: Let’s Start With The Basics:
- H2: Case 1 – Upgrade From PXC 5.7 No SSL
- H2: Case 2 – Upgrade From 5.7 With pxc-encrypt-cluster-traffic
- H2: Case 3 – New Install PXC8 Without pxc-encrypt-cluster-traffic
- H2: Case 4 – New Install With pxc-encrypt-cluster-traffic
- H3: Conclusion
- H3: Reference

## Images et graphiques reperes

- featured / image: [Percona XtraDB Cluster 8.0 Behavior Change for pxc-encrypt-cluster-traffic](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-8.0-Behavior-Change-for-pxc-encrypt-cluster-traffic.png)
- content / image: [Percona XtraDB Cluster 8.0 Behavior Change for pxc-encrypt-cluster-traffic](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-8.0-Behavior-Change-for-pxc-encrypt-cluster-traffic-300x168.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
