---
title: 'Failover with the MySQL Utilities – Part 1: mysqlrpladmin'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/failover-mysql-utilities-part1-mysqlrpladmin/
  post_id: 8200
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-06-27T15:41:46'
published_at_gmt: '2014-06-27T15:41:46'
modified_at: '2026-05-04T22:20:25'
modified_at_gmt: '2026-05-04T22:20:25'
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
- GTID-based replication
- MySQL Utilities
- mysqlreplicate
- mysqlrpladmin
- Stephane Combaudon
- switchover
tag_slugs:
- failover
- gtid-based-replication
- mysql-utilities
- mysqlreplicate
- mysqlrpladmin
- stephane-combaudon
- switchover
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Failover with the MySQL Utilities – Part 1: mysqlrpladmin

Source: [Percona Blog](https://www.percona.com/blog/failover-mysql-utilities-part1-mysqlrpladmin/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-06-27T15:41:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL Utilities are a set of tools provided by Oracle to perform many kinds of administrative tasks. When GTID-replication is enabled, 2 tools can be used for slave promotion: mysqlrpladmin and mysqlfailover. We will review mysqlrpladmin (version 1.4.3) in this post. Summary mysqlrpladmin can perform manual failover/switchover when GTID-replication is enabled. You … Continued

## Structure detectee

- H2: Summary
- H2: Failover vs switchover
- H2: Setup for this test
- H2: Simple failover scenario
- H2: Simple switchover scenario
- H2: Extension points
- H2: What about errant transactions?
- H2: Some limitations
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
