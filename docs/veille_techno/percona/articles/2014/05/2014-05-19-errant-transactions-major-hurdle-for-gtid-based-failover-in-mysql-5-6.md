---
title: 'Errant transactions: Major hurdle for GTID-based failover in MySQL 5.6'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/errant-transactions-major-hurdle-for-gtid-based-failover-in-mysql-5-6/
  post_id: 8124
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-05-19T07:00:36'
published_at_gmt: '2014-05-19T07:00:36'
modified_at: '2026-05-04T22:19:04'
modified_at_gmt: '2026-05-04T22:19:04'
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
- Errant transactions
- GTID
- mysqlfailover
- mysqlrpladmin
- Replication
- Stephane Combaudon
tag_slugs:
- errant-transactions
- gtid
- mysqlfailover
- mysqlrpladmin
- replication
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Errant transactions: Major hurdle for GTID-based failover in MySQL 5.6

Source: [Percona Blog](https://www.percona.com/blog/errant-transactions-major-hurdle-for-gtid-based-failover-in-mysql-5-6/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-05-19T07:00:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have previously written about the new replication protocol that comes with GTIDs in MySQL 5.6. Because of this new replication protocol, you can inadvertently create errant transactions that may turn any failover to a nightmare. Let’s see the problems and the potential solutions. In short Errant transactions may cause all kinds of … Continued

## Structure detectee

- H2: In short
- H2: What are errant transactions?
- H2: Why can they create problems that did not exist before GTIDs?
- H2: How to detect them?
- H2: How to get rid of them?
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
