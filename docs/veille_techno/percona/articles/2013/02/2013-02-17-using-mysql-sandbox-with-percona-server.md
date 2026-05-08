---
title: Using MySQL Sandbox with Percona Server
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-mysql-sandbox-with-percona-server/
  post_id: 6603
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2013-02-17T02:36:00'
published_at_gmt: '2013-02-17T02:36:00'
modified_at: '2026-05-04T21:59:17'
modified_at_gmt: '2026-05-04T21:59:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- MariaDB
- MySQL Sandbox
- Percona Server for MySQL
tag_slugs:
- mariadb
- mysql-sandbox
- percona-server
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using MySQL Sandbox with Percona Server

Source: [Percona Blog](https://www.percona.com/blog/using-mysql-sandbox-with-percona-server/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2013-02-17T02:36:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the most useful tools if you’re working with multiple versions of MySQL Servers is MySQL Sandbox which allows you to maintain many different versions of MySQL, Percona Server, MariaDB. If you’re just working with single sandbox you can just use MySQL Sandbox in its most basic way and it will work: root@smt2:~/sandboxes# make_sandbox /tmp/Percona-Server-5.5.29-rel29.4-401.Linux.x86_64.tar.gz unpacking /tmp/Percona-Server-5.5.29-rel29.4-401.Linux.x86_64.tar.gz Executing low_level_make_sandbox --basedir=/tmp/5.5.29 --sandbox_directory=msb_5_5_29 --install_version=5.5 --sandbox_port=5529 --no_ver_after_name --my_clause=log-error=msandbox.err ... no_run = no_show = do you agree? ([Y],n) Y loading grants ... sandbox server started Your sandbox server was installed in $HOME/sandboxes/msb_5_5_29 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 root @ smt2 : ~ / sandboxes # make_sandbox /tmp/Percona...

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
