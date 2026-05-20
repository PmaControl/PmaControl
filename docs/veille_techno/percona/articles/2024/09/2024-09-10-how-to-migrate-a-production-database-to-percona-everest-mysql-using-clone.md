---
title: How to Migrate a Production Database to Percona Everest (MySQL) Using Clone
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-migrate-a-production-database-to-percona-everest-mysql-using-clone/
  post_id: 28930
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2024-09-10T13:45:45'
published_at_gmt: '2024-09-10T13:45:45'
modified_at: '2026-03-26T20:25:58'
modified_at_gmt: '2026-03-26T20:25:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
- ProxySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:percona-xtrabackup
- search:pmm
- search:proxysql
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- Open Source
category_slugs:
- insight-for-dbas
- mysql
- open-source
tags:
- Database Migration
- Percona Everest
tag_slugs:
- database-migration
- percona-everest
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrate-a-Production-Database-to-Percona-Everest.jpg
image_count: 12
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Migrate a Production Database to Percona Everest (MySQL) Using Clone

Source: [Percona Blog](https://www.percona.com/blog/how-to-migrate-a-production-database-to-percona-everest-mysql-using-clone/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2024-09-10T13:45:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This long article aims to provide you with the instructions and tools to migrate your production database from your current environment to a solution based on Percona Everest (MySQL). Nice. You decided to test Percona Everest and found that it is the tool you were looking for to manage your private DBaaS. The easiest part … Continued

## Structure detectee

- H2: Create the new cluster
- H2: Align the system users
- H2: Let us go CLONING
- H3: Let us go…
- H2: Enable replication
- H2: Final touch
- H2: Post-migration actions
- H2: Summary of commands
- H3: References

## Images et graphiques reperes

- featured / image: [How to Migrate a Production Database to Percona Everest (MySQL) Using Clone](https://www.percona.com/wp-content/uploads/2026/03/Migrate-a-Production-Database-to-Percona-Everest.jpg)
- content / image: [everest1-a-1024x169.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest1-a-1024x169.jpg)
- content / image: [everest2-a-1024x478.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest2-a-1024x478.jpg)
- content / image: [everest3-a-1024x446.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest3-a-1024x446.jpg)
- content / image: [everest4-a-1024x481.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest4-a-1024x481.jpg)
- content / image: [everest5-a-1024x354.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest5-a-1024x354.jpg)
- content / image: [everest6-1024x158.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest6-1024x158.jpg)
- content / image: [everest8-1024x234.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest8-1024x234.jpg)
- content / image: [everest9.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest9.jpg)
- content / image: [everest10-a-1024x125.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest10-a-1024x125.jpg)
- content / image: [everest11-a-1024x416.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest11-a-1024x416.jpg)
- content / image: [everest12-a-1024x376.jpg](https://www.percona.com/wp-content/uploads/2026/03/everest12-a-1024x376.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
