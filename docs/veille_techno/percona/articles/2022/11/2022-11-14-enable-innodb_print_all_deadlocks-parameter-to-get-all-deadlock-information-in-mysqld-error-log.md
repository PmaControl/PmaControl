---
title: Enable innodb_print_all_deadlocks Parameter To Get All Deadlock Information in mysqld Error Log
source:
  name: Percona Blog
  url: https://www.percona.com/blog/enable-innodb_print_all_deadlocks-parameter-to-get-all-deadlock-information-in-mysqld-error-log/
  post_id: 26222
source_author:
  name: Larry Xia
  slug: larry-xia
  url: https://www.percona.com/blog/author/larry-xia/
  website: ''
published_at: '2022-11-14T14:11:08'
published_at_gmt: '2022-11-14T14:11:08'
modified_at: '2026-03-26T20:30:45'
modified_at_gmt: '2026-03-26T20:30:45'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Enable-innodb_print_all_deadlocks-Parameter.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Enable innodb_print_all_deadlocks Parameter To Get All Deadlock Information in mysqld Error Log

Source: [Percona Blog](https://www.percona.com/blog/enable-innodb_print_all_deadlocks-parameter-to-get-all-deadlock-information-in-mysqld-error-log/)

Auteur source: [Larry Xia](https://www.percona.com/blog/author/larry-xia/)

Publication: 2022-11-14T14:11:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

At Percona Managed Services, sometimes clients’ applications face deadlock situations and need all historic deadlock information for application tuning. We could get the LATEST DETECTED DEADLOCK from SHOW ENGINE INNODB STATUSG: Shell ….<br>------------------------<br>LATEST DETECTED DEADLOCK<br>------------------------<br>*** (1) WAITING FOR THIS LOCK TO BE GRANTED:<br>RECORD LOCKS space id 163 page no 3 n bits 72 index GEN_CLUST_INDEX of table `deadlock_test`.`t` trx id 78507 lock_mode X waiting<br>*** (2) TRANSACTION:<br>TRANSACTION 78508, ACTIVE 155 sec starting index read<br>mysql tables in use 1, locked 1<br>…. 1 … . < br > -- -- -- -- -- -- -- -- -- -- -- -- < br > LATEST DETECTED DEADLOCK < br > -- -- -- -- -- -- -- -- -- -- -- -- < br > * * * ( 1 ) WAITING FOR THIS LOCK TO BE GRANTED : < br > RECORD LOCKS space id 163 page no 3 n bits 72 index GEN_CLUST_INDEX of table ` deadlo...

## Structure detectee

- H2: Create the test database and insert some test data
- H2: Deadlock simulation on record 1 of table t (i=1)
- H2: Enable innodb_print_all_deadlocks dynamic parameter
- H2: Let’s simulate another deadlock by dealing with record 2 (i=2)
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Enable innodb_print_all_deadlocks Parameter To Get All Deadlock Information in mysqld Error Log](https://www.percona.com/wp-content/uploads/2026/03/Enable-innodb_print_all_deadlocks-Parameter.png)
- content / image: [Enable innodb_print_all_deadlocks Parameter](https://www.percona.com/wp-content/uploads/2026/03/Enable-innodb_print_all_deadlocks-Parameter-300x157.png)

## Auteur source

Larry is part of Percona's Managed Service team working as a Tier 1 MySQL DBA. Before joining Percona, Larry worked on different technologies for many years. He likes cooking and traveling.
