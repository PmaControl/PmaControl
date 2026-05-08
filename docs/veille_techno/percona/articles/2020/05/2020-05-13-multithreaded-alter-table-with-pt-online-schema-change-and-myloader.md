---
title: Multithreaded ALTER TABLE with pt-online-schema-change and myloader
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multithreaded-alter-table-with-pt-online-schema-change-and-myloader/
  post_id: 22116
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2020-05-13T14:57:23'
published_at_gmt: '2020-05-13T14:57:23'
modified_at: '2026-05-05T21:01:33'
modified_at_gmt: '2026-05-05T21:01:33'
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
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/multithreaded-alter-table.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multithreaded ALTER TABLE with pt-online-schema-change and myloader

Source: [Percona Blog](https://www.percona.com/blog/multithreaded-alter-table-with-pt-online-schema-change-and-myloader/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2020-05-13T14:57:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

pt-online-schema-change emulates the way that MySQL alters tables internally, but it works on a copy of the table you wish to alter. It executes INSERT statements to import the data, that runs in a single connection to fill the new table. In this repository, there is a script called myloader_pt-osc.sh that uses myloader to execute … Continued

## Structure detectee

- H3: Patch pt-online-schema-change
- H3: Procedure
- H3: Timings
- H3: Use Cases
- H3: Conclusion and Expectation

## Images et graphiques reperes

- featured / image: [Multithreaded ALTER TABLE with pt-online-schema-change and myloader](https://www.percona.com/wp-content/uploads/2026/03/multithreaded-alter-table.png)
- content / image: [multithreaded alter table](https://www.percona.com/wp-content/uploads/2026/03/multithreaded-alter-table-300x168.png)
- content / image: [pt-online-schema-change MySQL](https://www.percona.com/wp-content/uploads/2026/03/first-graphic-scaled.png)
- content / image: [myloader mysql](https://www.percona.com/wp-content/uploads/2026/03/second-graphic-scaled.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
