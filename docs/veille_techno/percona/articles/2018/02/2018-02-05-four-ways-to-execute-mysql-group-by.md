---
title: Four Ways MySQL Executes GROUP BY
source:
  name: Percona Blog
  url: https://www.percona.com/blog/four-ways-to-execute-mysql-group-by/
  post_id: 17993
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2018-02-05T17:37:07'
published_at_gmt: '2018-02-05T17:37:07'
modified_at: '2026-05-05T19:01:01'
modified_at_gmt: '2026-05-05T19:01:01'
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
tags:
- database
- filtering
- GROUP BY
- indexes
- InnoDB
- MySQL
- Open Source
- optimize
- queries
tag_slugs:
- database
- filtering
- group-by
- indexes
- innodb
- mysql
- open-source
- optimize
- queries
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-GROUP-BY.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Four Ways MySQL Executes GROUP BY

Source: [Percona Blog](https://www.percona.com/blog/four-ways-to-execute-mysql-group-by/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2018-02-05T17:37:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll look into four ways MySQL executes GROUP BY. In my previous blog post, we learned that indexes or other means of finding data might not be the most expensive part of query execution. For example, MySQL GROUP BY could potentially be responsible for 90% or more of the query execution … Continued

## Structure detectee

- H2: Handling MySQL GROUP BY

## Images et graphiques reperes

- featured / image: [Four Ways MySQL Executes GROUP BY](https://www.percona.com/wp-content/uploads/2026/03/MySQL-GROUP-BY.jpg)
- content / image: [MySQL GROUP BY](https://www.percona.com/wp-content/uploads/2026/03/MySQL-GROUP-BY-300x183.jpg)
- content / image: [Install Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/1345e6de-c1c7-426b-b711-fe0d2d70bca0.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
