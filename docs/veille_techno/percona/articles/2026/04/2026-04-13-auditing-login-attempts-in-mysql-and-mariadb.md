---
title: Auditing Login Attempts in MySQL and MariaDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/auditing-login-attempts-in-mysql-and-mariadb/
  post_id: 43295
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2026-04-13T21:08:26'
published_at_gmt: '2026-04-13T21:08:26'
modified_at: '2026-04-16T18:00:51'
modified_at_gmt: '2026-04-16T18:00:51'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
- PMM
matched_filters:
- category:mariadb:1281
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MariaDB
- MySQL
- Security
category_slugs:
- insight-for-dbas
- mariadb
- mysql
- security
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/ACL.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Auditing Login Attempts in MySQL and MariaDB

Source: [Percona Blog](https://www.percona.com/blog/auditing-login-attempts-in-mysql-and-mariadb/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2026-04-13T21:08:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

My colleague Miguel wrote about ways to audit login attempts in MySQL over 13 years ago, and this is still a relevant subject. I decided to refresh this topic to include some important changes since then. Very often, it is important to track login attempts to our databases due to security reasons as well as … Continued

## Structure detectee

- H3: The Error Log
- H3: The Audit Log (old type)
- H2: The Audit Log Filter (new type)
- H2: Additional Instrumentation
- H3: Summary

## Images et graphiques reperes

- content / image: [ACL.png](https://www.percona.com/wp-content/uploads/2026/04/ACL.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
