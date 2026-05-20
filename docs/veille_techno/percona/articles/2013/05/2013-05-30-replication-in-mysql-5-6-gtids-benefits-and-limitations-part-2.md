---
title: 'Replication in MySQL 5.6: GTIDs benefits and limitations – Part 2'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/replication-in-mysql-5-6-gtids-benefits-and-limitations-part-2/
  post_id: 7011
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2013-05-30T10:00:52'
published_at_gmt: '2013-05-30T10:00:52'
modified_at: '2026-05-04T22:05:30'
modified_at_gmt: '2026-05-04T22:05:30'
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
- GTID
- MySQL
- Replication
tag_slugs:
- gtid
- mysql
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/repli_setup.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Replication in MySQL 5.6: GTIDs benefits and limitations – Part 2

Source: [Percona Blog](https://www.percona.com/blog/replication-in-mysql-5-6-gtids-benefits-and-limitations-part-2/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2013-05-30T10:00:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The main benefit of using GTIDs is to have much easier failover than with file-based replication. We will see how to change the replication topology when using GTID-based replication. That will show where GTIDs shine and where improvements are expected. This is the second post of a series of articles focused on MySQL 5.6 GTIDs. … Continued

## Structure detectee

- H2: Scenario #1: All slaves have processed all the writes
- H2: Scenario #2: One of the slaves is behind
- H2: Scenario #3: The master has crashed before sending all writes
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Replication in MySQL 5.6: GTIDs benefits and limitations – Part 2](https://www.percona.com/wp-content/uploads/2026/03/repli_setup.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
