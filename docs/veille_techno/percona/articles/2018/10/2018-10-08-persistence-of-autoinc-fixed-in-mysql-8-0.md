---
title: Persistence of autoinc fixed in MySQL 8.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/persistence-of-autoinc-fixed-in-mysql-8-0/
  post_id: 19265
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2018-10-08T16:00:22'
published_at_gmt: '2018-10-08T16:00:22'
modified_at: '2026-05-05T19:21:13'
modified_at_gmt: '2026-05-05T19:21:13'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- autoincr
- primary key
- unique keys
tag_slugs:
- autoincr
- primary-key
- unique-keys
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-autoinc.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Persistence of autoinc fixed in MySQL 8.0

Source: [Percona Blog](https://www.percona.com/blog/persistence-of-autoinc-fixed-in-mysql-8-0/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2018-10-08T16:00:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The release of MySQL 8.0 has brought a lot of bold implementations that touched on things that have been avoided before, such as added support for common table expressions and window functions. Another example is the change in how AUTO_INCREMENT (autoinc) sequences are persisted, and thus replicated.

## Structure detectee

- H3: Understanding the bug
- H3: Workarounds
- H4: a) Detect and fix
- H4: b) Include Primary Key in REPLACE statements
- H4: c) Make the target field the Primary Key and keep autoinc as a UNIQUE key
- H4: Co-Author: Trey Raymond
- H4: Co-Author: Fernando Laudares

## Images et graphiques reperes

- featured / image: [Persistence of autoinc fixed in MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-autoinc.jpg)
- content / image: [MySQL 8.0 autoinc persistence fixed](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-autoinc-300x199.jpg)
- content / image: [Trey Raymond](https://www.percona.com/wp-content/uploads/2026/03/wraymond_profile.png)
- content / image: [fernando laudares](https://www.percona.com/wp-content/uploads/2026/03/fernando-laudares-150x150.jpeg)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
