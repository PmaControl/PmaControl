---
title: 'When Manipulating MySQL User Tables Goes Wrong: Troubleshooting ERROR 1396'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-manipulating-mysql-user-tables-goes-wrong-troubleshooting-error-1396/
  post_id: 26078
source_author:
  name: Mauricio Cacho
  slug: mauricio-cacho
  url: https://www.percona.com/blog/author/mauricio-cacho/
  website: ''
published_at: '2022-10-05T12:56:27'
published_at_gmt: '2022-10-05T12:56:27'
modified_at: '2026-03-26T20:31:01'
modified_at_gmt: '2026-03-26T20:31:01'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-ERROR-1396-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When Manipulating MySQL User Tables Goes Wrong: Troubleshooting ERROR 1396

Source: [Percona Blog](https://www.percona.com/blog/when-manipulating-mysql-user-tables-goes-wrong-troubleshooting-error-1396/)

Auteur source: [Mauricio Cacho](https://www.percona.com/blog/author/mauricio-cacho/)

Publication: 2022-10-05T12:56:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A few weeks back, we faced an issue in a replication environment for a Managed Services client: LAST_ERROR_MESSAGE: Worker 2 failed executing transaction ‘UUID:GTID’ at master binlog.0012345, end_log_pos 98765; Error ‘Operation CREATE USER failed for ‘test_user’@’10.10.10.10” on query. Default database: ‘mysql’. Query: ‘CREATE USER ‘test_user’@’10.10.10.10’ IDENTIFIED WITH ‘mysql_native_password’ AS ‘************” After some initial investigation, we … Continued

## Structure detectee

- H2: A summary of MySQL privileges
- H3: Let’s go through the issue mentioned above
- H2: When FLUSH PRIVILEGES does the trick
- H2: When FLUSH PRIVILEGES isn’t enough
- H2: What about “IF NOT EXISTS” clause?
- H3: Final thoughts

## Images et graphiques reperes

- featured / image: [When Manipulating MySQL User Tables Goes Wrong: Troubleshooting ERROR 1396](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-ERROR-1396-MySQL.png)
- content / image: [Troubleshooting ERROR 1396](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-ERROR-1396-MySQL-300x157.png)

## Auteur source

Mauricio started working with MySQL back in 2014, and started with DBA tasks a few months later; quickly after that, he became passionate about troubleshooting MySQL issues and optimizing overall performance for databases.
