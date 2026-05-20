---
title: How to Use Percona Toolkit’s pt-table-sync for Replica Tables With Triggers in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-use-percona-toolkits-pt-table-sync-for-replica-tables-with-triggers-in-mysql/
  post_id: 27786
source_author:
  name: Larry Xia
  slug: larry-xia
  url: https://www.percona.com/blog/author/larry-xia/
  website: ''
published_at: '2023-12-12T15:25:45'
published_at_gmt: '2023-12-12T15:25:45'
modified_at: '2026-03-26T20:26:52'
modified_at_gmt: '2026-03-26T20:26:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Toolkit
tag_slugs:
- mysql
- mysql-and-variants
- percona-toolkit
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pt-table-sync-for-Replica-Tables-With-Triggers.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Use Percona Toolkit’s pt-table-sync for Replica Tables With Triggers in MySQL

Source: [Percona Blog](https://www.percona.com/blog/how-to-use-percona-toolkits-pt-table-sync-for-replica-tables-with-triggers-in-mysql/)

Auteur source: [Larry Xia](https://www.percona.com/blog/author/larry-xia/)

Publication: 2023-12-12T15:25:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Percona Managed Services, we manage Percona for MySQL, Community MySQL, and MariaDB. Sometimes, the replica server might have replication errors, and the replica might be out of sync with the primary. In this case, we can use Percona Toolkit’s pt-table-checksum and pt-table-sync to check the data drift between primary and replica servers and make … Continued

## Structure detectee

- H4: 1. Creating the test tables and the AFTER INSERT trigger
- H4: 2. Let’s fill in some test data
- H4: 3. Let’s get percona.dsns ready for pt-table-checksum and pt-table-sync
- H4: 4. Simulate the out of sync on 192.168.56.190 by removing one row (id=1) in test_tab
- H4: 5. Run pt-table-checksum to report the difference
- H4: 6. Let’s try pt-table-sync to fix it; we will run pt-table-sync under user ‘larry’@’%’
- H4: 7. If we do not want that to happen (new row inserted in test_tab_log table)
- H4: 8. If we still insert other data into the table test_tab under another user (e.g. root@localhost), the trigger will still fire

## Images et graphiques reperes

- featured / image: [How to Use Percona Toolkit’s pt-table-sync for Replica Tables With Triggers in MySQL](https://www.percona.com/wp-content/uploads/2026/03/pt-table-sync-for-Replica-Tables-With-Triggers.jpg)

## Auteur source

Larry is part of Percona's Managed Service team working as a Tier 1 MySQL DBA. Before joining Percona, Larry worked on different technologies for many years. He likes cooking and traveling.
