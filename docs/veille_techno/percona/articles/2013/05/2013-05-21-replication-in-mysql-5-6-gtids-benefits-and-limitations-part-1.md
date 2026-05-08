---
title: 'Replication in MySQL 5.6: GTIDs benefits and limitations – Part 1'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/replication-in-mysql-5-6-gtids-benefits-and-limitations-part-1/
  post_id: 6989
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2013-05-21T10:00:42'
published_at_gmt: '2013-05-21T10:00:42'
modified_at: '2026-05-04T22:05:02'
modified_at_gmt: '2026-05-04T22:05:02'
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
category_slugs:
- mysql
tags:
- GTID
- MySQL 5.6
- Replication
tag_slugs:
- gtid
- mysql-5-6
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/data_replication.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Replication in MySQL 5.6: GTIDs benefits and limitations – Part 1

Source: [Percona Blog](https://www.percona.com/blog/replication-in-mysql-5-6-gtids-benefits-and-limitations-part-1/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2013-05-21T10:00:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Global Transactions Identifiers are one of the new features regarding replication in MySQL 5.6. They open up a lot of opportunities to make the life of DBAs much easier when having to maintain servers under a specific replication topology. However you should keep in mind some limitations of the current implementation. This post is the … Continued

## Structure detectee

- H2: First try: configure only one of the servers with GTIDs
- H2: Second try: GTIDs enabled, mixing regular replication and GTID replication
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Replication in MySQL 5.6: GTIDs benefits and limitations – Part 1](https://www.percona.com/wp-content/uploads/2026/03/data_replication.jpg)
- content / image: [GTID-based replication](https://www.percona.com/wp-content/uploads/2026/03/data_replication2-300x204.jpg)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
