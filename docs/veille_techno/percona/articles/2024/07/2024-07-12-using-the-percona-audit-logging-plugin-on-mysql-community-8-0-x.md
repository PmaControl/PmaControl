---
title: Using the Percona Audit Logging Plugin on MySQL Community 8.0.x
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-the-percona-audit-logging-plugin-on-mysql-community-8-0-x/
  post_id: 28792
source_author:
  name: Larry Xia
  slug: larry-xia
  url: https://www.percona.com/blog/author/larry-xia/
  website: ''
published_at: '2024-07-12T12:43:07'
published_at_gmt: '2024-07-12T12:43:07'
modified_at: '2026-03-26T20:26:10'
modified_at_gmt: '2026-03-26T20:26:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Audit-Logging-Plugin-on-MySQL-Community.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using the Percona Audit Logging Plugin on MySQL Community 8.0.x

Source: [Percona Blog](https://www.percona.com/blog/using-the-percona-audit-logging-plugin-on-mysql-community-8-0-x/)

Auteur source: [Larry Xia](https://www.percona.com/blog/author/larry-xia/)

Publication: 2024-07-12T12:43:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

At Percona Managed Services, we manage Percona MySQL, Community MySQL, and MariaDB. Sometimes, we might need to enable audit logging and share the logs for client MySQL Community 8.0.x servers. There are several ways to enable audit logs. One is to use the MySQL Enterprise audit logging plugin (audit_log.so), but it only supports the MySQL … Continued

## Structure detectee

- H3: 1. Install MySQL Community server 8.0.x
- H3: 2. Get the Percona audit logging plugin from binary tarball
- H3: 3. Current plugin settings
- H3: 4. Copy the Percona audit logging plugin file audit_log.so to the plugin directory and install the plugin
- H3: 5. Add audit parameter and restart the database service to take effect

## Images et graphiques reperes

- featured / image: [Using the Percona Audit Logging Plugin on MySQL Community 8.0.x](https://www.percona.com/wp-content/uploads/2026/03/Percona-Audit-Logging-Plugin-on-MySQL-Community.jpg)

## Auteur source

Larry is part of Percona's Managed Service team working as a Tier 1 MySQL DBA. Before joining Percona, Larry worked on different technologies for many years. He likes cooking and traveling.
