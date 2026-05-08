---
title: Unexpected problem with triggers and mysqldump
source:
  name: Percona Blog
  url: https://www.percona.com/blog/unexpected-problem-with-triggers-and-mysqldump/
  post_id: 6590
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2013-02-11T14:40:09'
published_at_gmt: '2013-02-11T14:40:09'
modified_at: '2026-04-28T21:49:56'
modified_at_gmt: '2026-04-28T21:49:56'
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
- mysqldump
- trigger
tag_slugs:
- mysqldump
- trigger
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Unexpected problem with triggers and mysqldump

Source: [Percona Blog](https://www.percona.com/blog/unexpected-problem-with-triggers-and-mysqldump/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2013-02-11T14:40:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some time ago, I had to convert all tables of a database from MyISAM to InnoDB on a new server. The plan was to take a logical dump on the master, exporting separately the schema and the data, then edit the CREATE TABLE statements to ensure all tables are created with InnoDB, and reload everything … Continued

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
