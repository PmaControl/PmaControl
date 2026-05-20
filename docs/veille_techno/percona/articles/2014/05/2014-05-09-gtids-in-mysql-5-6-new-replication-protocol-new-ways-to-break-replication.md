---
title: 'GTIDs in MySQL 5.6: New replication protocol; new ways to break replication'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/gtids-in-mysql-5-6-new-replication-protocol-new-ways-to-break-replication/
  post_id: 8081
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-05-09T07:00:39'
published_at_gmt: '2014-05-09T07:00:39'
modified_at: '2026-05-04T22:18:10'
modified_at_gmt: '2026-05-04T22:18:10'
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
- Global Transactions IDs
- GTID
- MySQL 5.6
- Replication
- Stephane Combaudon
tag_slugs:
- global-transactions-ids
- gtid
- mysql-5-6
- replication
- stephane-combaudon
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/new_protocol.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# GTIDs in MySQL 5.6: New replication protocol; new ways to break replication

Source: [Percona Blog](https://www.percona.com/blog/gtids-in-mysql-5-6-new-replication-protocol-new-ways-to-break-replication/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-05-09T07:00:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the MySQL 5.6 features many people are interested in is Global Transactions IDs (GTIDs). This is for a good reason: Reconnecting a slave to a new master has always been a challenge while it is so trivial when GTIDs are enabled. However, using GTIDs is not only about replacing good old binlog file/position … Continued

## Structure detectee

- H2: Replication protocols: old vs new
- H2: Skipping transactions
- H2: Errant transactions
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [GTIDs in MySQL 5.6: New replication protocol; new ways to break replication](https://www.percona.com/wp-content/uploads/2026/03/new_protocol.png)
- content / image: [new_protocol2](https://www.percona.com/wp-content/uploads/2026/03/new_protocol2.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
