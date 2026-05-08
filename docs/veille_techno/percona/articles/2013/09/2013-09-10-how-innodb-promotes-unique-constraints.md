---
title: How InnoDB promotes UNIQUE constraints
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-innodb-promotes-unique-constraints/
  post_id: 7311
source_author:
  name: Ryan Lowe
  slug: ryanalowe
  url: https://www.percona.com/blog/author/ryanalowe/
  website: http://www.percona.com/blog/
published_at: '2013-09-10T05:00:17'
published_at_gmt: '2013-09-10T05:00:17'
modified_at: '2026-05-05T21:53:00'
modified_at_gmt: '2026-05-05T21:53:00'
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
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- constraints
- InnoDB
- MySQL 5.6.11
- Percona Toolkit
tag_slugs:
- constraints
- innodb
- mysql-5-6-11
- percona-toolkit
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/chain.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How InnoDB promotes UNIQUE constraints

Source: [Percona Blog](https://www.percona.com/blog/how-innodb-promotes-unique-constraints/)

Auteur source: [Ryan Lowe](https://www.percona.com/blog/author/ryanalowe/)

Publication: 2013-09-10T05:00:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The other day I was running pt-duplicate-key-checker on behalf of a customer and noticed some peculiar recommendations on an InnoDB table with an odd structure (no PRIMARY key, but multiple UNIQUE constraints). This got me thinking about how InnoDB promotes UNIQUE constraints to the role of PRIMARY KEYs. The documentation is pretty clear: [DOCS] When … Continued

## Images et graphiques reperes

- featured / image: [How InnoDB promotes UNIQUE constraints](https://www.percona.com/wp-content/uploads/2026/03/chain.jpg)

## Auteur source

Ryan was a principal consultant and team manager at Percona until July 2014. He has experience with many database technologies in industries such as health care, telecommunications, and social networking.
