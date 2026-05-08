---
title: Integrating pt-online-schema-change with a Scripted Deployment
source:
  name: Percona Blog
  url: https://www.percona.com/blog/integrating-pt-online-schema-change-scripted-deployment/
  post_id: 7550
source_author:
  name: Mike Benshoof
  slug: mbenshoof
  url: https://www.percona.com/blog/author/mbenshoof/
  website: ''
published_at: '2013-11-21T15:09:29'
published_at_gmt: '2013-11-21T15:09:29'
modified_at: '2026-04-28T21:57:58'
modified_at_gmt: '2026-04-28T21:57:58'
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
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Capistrano
- Liquibase
- Mike Benshoof
- Percona Toolkit
- pt-online-schema-change
- Scripted Deployment
tag_slugs:
- capistrano
- liquibase
- mike-benshoof
- percona-toolkit
- pt-online-schema-change
- scripted-deployment
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Integrating pt-online-schema-change with a Scripted Deployment

Source: [Percona Blog](https://www.percona.com/blog/integrating-pt-online-schema-change-scripted-deployment/)

Auteur source: [Mike Benshoof](https://www.percona.com/blog/author/mbenshoof/)

Publication: 2013-11-21T15:09:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I helped a client that was having issues with deployments causing locking in their production databases. At a high level, the two key components used in the environment were: Capistrano (scripted deployments) [website] Liquibase (database version control) [website] At a high level, they currently used a CLI call to Liquibase as … Continued

## Auteur source

Michael joined Percona in 2012 as a US based consultant and is currently a Technical Account Manager. Prior to joining Percona, Michael spent several years in a DevOps role maintaining a SaaS application specializing in social networking. His experiences include application development and scaling, systems administration, along with database administration and design. He enjoys designing extensible and flexible solutions to problems. When not working, he enjoys time outdoors, grilling, most sports, and spending time with the family.
