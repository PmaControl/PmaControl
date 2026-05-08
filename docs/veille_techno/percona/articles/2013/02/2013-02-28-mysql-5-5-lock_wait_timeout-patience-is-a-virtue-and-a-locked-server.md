---
title: 'MySQL 5.5 lock_wait_timeout: patience is a virtue, and a locked server'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-5-5-lock_wait_timeout-patience-is-a-virtue-and-a-locked-server/
  post_id: 6645
source_author:
  name: Daniel Nichter
  slug: daniel
  url: https://www.percona.com/blog/author/daniel/
  website: http://www.percona.com
published_at: '2013-02-28T11:00:55'
published_at_gmt: '2013-02-28T11:00:55'
modified_at: '2026-03-25T16:44:13'
modified_at_gmt: '2026-03-25T16:44:13'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Daniel Nichter
- innodb_lock_wait_timeout
- lock_wait_timeout
- MySQL 5.5
- MySQL 5.6
- solstices
tag_slugs:
- daniel-nichter
- innodb_lock_wait_timeout
- lock_wait_timeout
- mysql-5-5
- mysql-5-6
- solstices
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/timeout.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 5.5 lock_wait_timeout: patience is a virtue, and a locked server

Source: [Percona Blog](https://www.percona.com/blog/mysql-5-5-lock_wait_timeout-patience-is-a-virtue-and-a-locked-server/)

Auteur source: [Daniel Nichter](https://www.percona.com/blog/author/daniel/)

Publication: 2013-02-28T11:00:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Like Ovais said in Implications of Metadata Locking Changes in MySQL 5.5, the hot topic these days is MySQL 5.6, but there was an important metadata locking change in MySQL 5.5. As I began to dig into the Percona Toolkit bug he reported concerning this change apropos pt-online-schema-change, I discovered something about lock_wait_timeout that shocked me. From the … Continued

## Images et graphiques reperes

- featured / image: [MySQL 5.5 lock_wait_timeout: patience is a virtue, and a locked server](https://www.percona.com/wp-content/uploads/2026/03/timeout.jpg)
- content / image: [MySQL 5.5 lock_wait_timeout: patience is a virtue, and a locked server](https://www.percona.com/wp-content/uploads/2026/03/timeout-200x300.jpg)
  Caption: MySQL 5.5 lock_wait_timeout: patience is a virtue, and a locked server
