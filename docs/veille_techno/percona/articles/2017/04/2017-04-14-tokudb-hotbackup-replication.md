---
title: TokuDB Hotbackup and Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-hotbackup-replication/
  post_id: 16694
source_author:
  name: Vlad Lesin
  slug: vlad-lesin
  url: https://www.percona.com/blog/author/vlad-lesin/
  website: ''
published_at: '2017-04-14T18:10:29'
published_at_gmt: '2017-04-14T18:10:29'
modified_at: '2026-03-20T21:23:10'
modified_at_gmt: '2026-03-20T21:23:10'
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
categories:
- MySQL
category_slugs:
- mysql
tags:
- backup
- hotbackup
- Replication
- TokuDB
tag_slugs:
- backup
- hotbackup
- replication
- tokudb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/TokuDB-Hotbackup.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB Hotbackup and Replication

Source: [Percona Blog](https://www.percona.com/blog/tokudb-hotbackup-replication/)

Auteur source: [Vlad Lesin](https://www.percona.com/blog/author/vlad-lesin/)

Publication: 2017-04-14T18:10:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TokuDB Hotbackup is a solution that allows you to do backups on the fly. It works as a library that intercepts certain system calls that duplicate data written to already copied parts of files, so that at the end of the backup process the copied files contain the same content as the original files. There are several … Continued

## Images et graphiques reperes

- featured / image: [TokuDB Hotbackup and Replication](https://www.percona.com/wp-content/uploads/2026/03/TokuDB-Hotbackup.jpg)

## Auteur source

Vladislav Lesin is a software engineer at Percona, where he joined in April 2012. Before coming to Percona he worked on improving performance and reliability of high load projects with LAMP architecture. His work consisted in developing fast servers and modules with C and C++, projects state monitoring, searching bottlenecks, open source projects patching including nginx, memcache, sphinx, php, ejabberd. He took part in developing not only server-side applications, but desktop and mobile ones too. Also he had experience in project/product management, hiring, partners negotiations. Before that he worked in several IT companies where he developed desktop applications on C++ for such areas as industrial automation, parallel computing, media production. He holds a Master's Degree in Technique and Technology from Tula State University. Now he lives in Tula city with his wife and daughter.
