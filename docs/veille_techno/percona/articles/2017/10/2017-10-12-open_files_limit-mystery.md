---
title: A Mystery with MySQL open_files_limit
source:
  name: Percona Blog
  url: https://www.percona.com/blog/open_files_limit-mystery/
  post_id: 17418
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2017-10-12T19:08:41'
published_at_gmt: '2017-10-12T19:08:41'
modified_at: '2026-05-05T20:16:57'
modified_at_gmt: '2026-05-05T20:16:57'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- LimitNOFILE
- MySQL
- mysqld
- open_files_limit
- Percona Server for MySQL
- service mysql start
- systemd
tag_slugs:
- limitnofile
- mysql
- mysqld
- open_files_limit
- percona-server
- service-mysql-start
- systemd
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/open_files_limit.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Mystery with MySQL open_files_limit

Source: [Percona Blog](https://www.percona.com/blog/open_files_limit-mystery/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2017-10-12T19:08:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll look at a mystery around setting the MySQL open_file_limit variable in MySQL and Percona Server for MySQL. MySQL Server needs file descriptors to run. It uses them to open new connections, store tables in the cache, create temporary tables to resolve complicated queries and access persistent ones. If mysqld is not … Continued

## Structure detectee

- H2: mysqld
- H2: mysqld_safe
- H2: init.d
- H2: MySQL Server
- H2: Percona Server for MySQL
- H2: SystemD
- H2: Both packages
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [A Mystery with MySQL open_files_limit](https://www.percona.com/wp-content/uploads/2026/03/open_files_limit.png)
- content / image: [MySQL open_files_limit](https://www.percona.com/wp-content/uploads/2026/03/open_files_limit-300x200.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
