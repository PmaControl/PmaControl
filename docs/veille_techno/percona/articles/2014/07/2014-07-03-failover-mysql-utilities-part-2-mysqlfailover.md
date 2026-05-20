---
title: 'Failover with the MySQL Utilities: Part 2 – mysqlfailover'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/failover-mysql-utilities-part-2-mysqlfailover/
  post_id: 8360
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-07-03T07:00:56'
published_at_gmt: '2014-07-03T07:00:56'
modified_at: '2026-05-04T22:23:06'
modified_at_gmt: '2026-05-04T22:23:06'
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
- Failover
- GTID-replication
- MySQL Utilities
- mysqlfailover
tag_slugs:
- failover
- gtid-replication
- mysql-utilities
- mysqlfailover
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Failover with the MySQL Utilities: Part 2 – mysqlfailover

Source: [Percona Blog](https://www.percona.com/blog/failover-mysql-utilities-part-2-mysqlfailover/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-07-03T07:00:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the previous post of this series we saw how you could use mysqlrpladmin to perform manual failover/switchover when GTID replication is enabled in MySQL 5.6. Now we will review mysqlfailover (version 1.4.3), another tool from the MySQL Utilities that can be used for automatic failover. Summary mysqlfailover can perform automatic failover if … Continued

## Structure detectee

- H2: Summary
- H2: Setup
- H2: Failover
- H2: Tool registration
- H2: Running in the background
- H2: Errant transactions
- H2: Limitations
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
