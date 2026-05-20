---
title: Making HAProxy 1.5 replication lag aware in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/making-haproxy-1-5-replication-lag-aware-in-mysql/
  post_id: 8890
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-12-18T15:48:21'
published_at_gmt: '2014-12-18T15:48:21'
modified_at: '2026-05-04T20:59:03'
modified_at_gmt: '2026-05-04T20:59:03'
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
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- agent checks
- haproxy
- MySQL
- MySQL server
- Percona XtraDB Cluster
- Peter Boros
- Primary
- replication lag
- Stephane Combaudon
tag_slugs:
- agent-checks
- haproxy
- mysql
- mysql-server
- percona-xtradb-cluster
- peter-boros
- primary
- replication-lag
- stephane-combaudon
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Making-HAProxy-1.5-replication-lag-aware-in-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Making HAProxy 1.5 replication lag aware in MySQL

Source: [Percona Blog](https://www.percona.com/blog/making-haproxy-1-5-replication-lag-aware-in-mysql/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-12-18T15:48:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

HAProxy is frequently used as a software load balancer in the MySQL world. Peter Boros, in a past post, explained how to set it up with Percona XtraDB Cluster (PXC) so that it only sends queries to available nodes. The same approach can be used in a regular master-slaves setup to spread the read load … Continued

## Structure detectee

- H2: Agent checks in HAProxy
- H2: Demo
- H3: No lag
- H3: Slave1 lagging
- H3: Slave2 down
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Making HAProxy 1.5 replication lag aware in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Making-HAProxy-1.5-replication-lag-aware-in-MySQL.jpg)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
