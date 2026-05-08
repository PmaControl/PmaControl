---
title: Encrypted and incremental MySQL backups with Percona XtraBackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/encrypted-and-incremental-mysql-backups-with-percona-xtrabackup/
  post_id: 8080
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2014-04-24T07:00:07'
published_at_gmt: '2014-04-24T07:00:07'
modified_at: '2026-04-28T22:04:17'
modified_at_gmt: '2026-04-28T22:04:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
- tag:percona-xtrabackup:330
categories:
- MySQL
- Percona Services
- Percona Software
category_slugs:
- mysql
- percona-services
- percona-software
tags:
- Backups
- encryption
- incremental MySQL backups
- Jervin Real
- Percona XtraBackup
tag_slugs:
- backups
- encryption
- incremental-mysql-backups
- jervin-real
- percona-xtrabackup
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Encrypted and incremental MySQL backups with Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/encrypted-and-incremental-mysql-backups-with-percona-xtrabackup/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2014-04-24T07:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We’ve recently received a number of questions on how to implement incremental MySQL backups alongside encryption with Percona XtraBackup. Some users thought it was not initially possible because with the default -- encrypt options with XtraBackup, all files will be encrypted, but alas, that is not the case. This is where the option -- extra - lsn - dir becomes useful, … Continued

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
