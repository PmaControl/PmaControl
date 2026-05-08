---
title: MyDumper’s Stream Implementation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mydumpers-stream-implementation/
  post_id: 25647
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2022-06-03T13:35:10'
published_at_gmt: '2022-06-03T13:35:10'
modified_at: '2026-03-26T20:31:46'
modified_at_gmt: '2026-03-26T20:31:46'
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
- mydumper
- MySQL
- mysql-and-variants
tag_slugs:
- mydumper
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MyDumper-Stream-Implementation.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MyDumper’s Stream Implementation

Source: [Percona Blog](https://www.percona.com/blog/mydumpers-stream-implementation/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2022-06-03T13:35:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As you might know, mysqldump is single-threaded and STDOUT is its default output. As MyDumper is multithreaded, it has to write on different files. Since version 0.11.3 was released in Nov 2021, we have the possibility to stream our backup in MyDumper. We thought for several months until we decided what was the simplest way … Continued

## Structure detectee

- H2: How Can You Stream if MyDumper is Multithreaded?
- H2: Implementation Details
- H2: Simple Use Cases
- H2: Considerations
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [MyDumper’s Stream Implementation](https://www.percona.com/wp-content/uploads/2026/03/MyDumper-Stream-Implementation.png)
- content / image: [MyDumper Stream Implementation](https://www.percona.com/wp-content/uploads/2026/03/MyDumper-Stream-Implementation-300x168.png)
- content / image: [MyDumper](https://www.percona.com/wp-content/uploads/2026/03/MyDumper.drawio-8-1024x182.png)
- content / image: [pipe from a mydumper process to myloader](https://www.percona.com/wp-content/uploads/2026/03/MyDumper.drawio-7.png)
- content / image: [stream through the network using nc](https://www.percona.com/wp-content/uploads/2026/03/MyDumper.drawio-5.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
