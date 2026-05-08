---
title: Impact of the number of idle connections in MySQL (version 2)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/impact-of-the-number-of-idle-connections-in-mysql-version-2/
  post_id: 2548
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2010-12-20T15:40:13'
published_at_gmt: '2010-12-20T15:40:13'
modified_at: '2026-03-23T21:48:35'
modified_at_gmt: '2026-03-23T21:48:35'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/NOTPM_vs_idle_conn_v2.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Impact of the number of idle connections in MySQL (version 2)

Source: [Percona Blog](https://www.percona.com/blog/impact-of-the-number-of-idle-connections-in-mysql-version-2/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2010-12-20T15:40:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last Friday I published results of DBT2 performance while varying the number of idle connections here, but I had compiled MySQL with the debugging code enabled. That completely screw up my results, be aware… debug options have a huge performance impact. So, I recompiled Percona-Server 11.2 without the debug options and did another benchmark run. … Continued

## Images et graphiques reperes

- featured / image: [Impact of the number of idle connections in MySQL (version 2)](https://www.percona.com/wp-content/uploads/2026/03/NOTPM_vs_idle_conn_v2.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
