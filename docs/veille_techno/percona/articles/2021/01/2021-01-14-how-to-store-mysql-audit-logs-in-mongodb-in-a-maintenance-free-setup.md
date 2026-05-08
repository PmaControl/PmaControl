---
title: How to Store MySQL Audit Logs in MongoDB in a Maintenance-Free Setup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-store-mysql-audit-logs-in-mongodb-in-a-maintenance-free-setup/
  post_id: 23732
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2021-01-14T20:17:15'
published_at_gmt: '2021-01-14T20:17:15'
modified_at: '2026-03-26T20:16:15'
modified_at_gmt: '2026-03-26T20:16:15'
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
- MongoDB
- MySQL
- Percona Software
- Security
category_slugs:
- insight-for-dbas
- mongodb
- mysql
- percona-software
- security
tags:
- MongoDB
- MySQL
- Percona Software
tag_slugs:
- mongodb
- mysql
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Store-MySQL-Audit-Logs-in-MongoDB-1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Store MySQL Audit Logs in MongoDB in a Maintenance-Free Setup

Source: [Percona Blog](https://www.percona.com/blog/how-to-store-mysql-audit-logs-in-mongodb-in-a-maintenance-free-setup/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2021-01-14T20:17:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I was once helping one of our customers on how to load MySQL audit logs into a MySQL database and analyze them. But immediately I thought: “Hey, this is not the most efficient solution! MySQL or typical RDBMS, in general, were not really meant to store logs after all.” So, I decided to explore an … Continued

## Structure detectee

- H3: Ad Hoc Import
- H3: Syslog
- H3: Fluentd For The Rescue!
- H3: References

## Images et graphiques reperes

- featured / image: [How to Store MySQL Audit Logs in MongoDB in a Maintenance-Free Setup](https://www.percona.com/wp-content/uploads/2026/03/Store-MySQL-Audit-Logs-in-MongoDB-1.png)
- content / image: [Store MySQL Audit Logs in MongoDB](https://www.percona.com/wp-content/uploads/2026/03/Store-MySQL-Audit-Logs-in-MongoDB-1-300x168.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
