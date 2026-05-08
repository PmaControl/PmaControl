---
title: Disconnecting a replication slave is easier with MySQL 5.5+ (RESET SLAVE vs. RESET SLAVE ALL)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/reset-slave-vs-reset-slave-all-disconnecting-a-replication-slave-is-easier-with-mysql-5-5/
  post_id: 6763
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2013-04-17T10:00:04'
published_at_gmt: '2013-04-17T10:00:04'
modified_at: '2026-05-04T22:02:53'
modified_at_gmt: '2026-05-04T22:02:53'
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
- data integrity
- disconnecting a replication slave
- RESET SLAVE command
tag_slugs:
- data-integrity
- disconnecting-a-replication-slave
- reset-slave-command
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Disconnecting a replication slave is easier with MySQL 5.5+ (RESET SLAVE vs. RESET SLAVE ALL)

Source: [Percona Blog](https://www.percona.com/blog/reset-slave-vs-reset-slave-all-disconnecting-a-replication-slave-is-easier-with-mysql-5-5/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2013-04-17T10:00:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It’s not uncommon to promote a server from slave to master. One of the key things to protect your data integrity is to make sure that the promoted slave is permanently disconnected from its old master. If not, it may get writes from the old master, which can cause all kinds of data corruption. MySQL … Continued

## Structure detectee

- H2: Disconnect a Replication Slave
- H2: MySQL 5.0/5.1
- H2: From MySQL 5.5

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
