---
title: Online DDL Tools and Metadata Locks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/online-ddl-tools-and-metadata-locks/
  post_id: 26283
source_author:
  name: Peter Sylvester
  slug: peter-sylvester
  url: https://www.percona.com/blog/author/peter-sylvester/
  website: ''
published_at: '2022-11-23T14:09:13'
published_at_gmt: '2022-11-23T14:09:13'
modified_at: '2026-03-26T20:30:40'
modified_at_gmt: '2026-03-26T20:30:40'
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
- gh-ost
- metadata locks
- MySQL
- mysql-and-variants
- Online DDL
- pt-online-schema-change
tag_slugs:
- gh-ost
- metadata-locks
- mysql
- mysql-and-variants
- online-ddl
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Online-DDL-Tools-and-Metadata-Locks.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Online DDL Tools and Metadata Locks

Source: [Percona Blog](https://www.percona.com/blog/online-ddl-tools-and-metadata-locks/)

Auteur source: [Peter Sylvester](https://www.percona.com/blog/author/peter-sylvester/)

Publication: 2022-11-23T14:09:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One thing I commonly hear when working with my clients is “I want to change my DDL strategy in order to avoid locking in my database! The last time I used the same old method I ended up in a metadata lock situation!” I agree that metadata locks can be painful, but unfortunately, it’s completely … Continued

## Structure detectee

- H2: Lab setup
- H2: Online DDL, Algorithm=INSTANT
- H3: Terminal One
- H3: Terminal Two
- H3: Terminal three
- H3: Terminal four
- H2: Terminal one
- H2: Terminal two
- H2: Terminal three
- H2: Terminal four
- H2: Terminal one
- H2: pt-online-schema-change
- H3: Terminal two
- H3: Terminal three
- H3: Terminal two
- H3: Terminal two
- H3: Terminal three
- H2: Terminal three
- H2: gh-ost
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Online DDL Tools and Metadata Locks](https://www.percona.com/wp-content/uploads/2026/03/Online-DDL-Tools-and-Metadata-Locks.png)
- content / image: [Online DDL Tools and Metadata Locks](https://www.percona.com/wp-content/uploads/2026/03/Online-DDL-Tools-and-Metadata-Locks-300x157.png)

## Auteur source

Peter Sylvester is one of the Senior MySQL Database Administrators within Percona's managed services team and has been with Percona since October of 2021.
