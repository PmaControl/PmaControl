---
title: Masquerade Your Backups To Build QA/Testing Environments With MyDumper
source:
  name: Percona Blog
  url: https://www.percona.com/blog/masquerade-your-backups-to-build-qa-testing-environments-with-mydumper/
  post_id: 26770
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2023-04-10T13:59:07'
published_at_gmt: '2023-04-10T13:59:07'
modified_at: '2026-03-26T20:29:52'
modified_at_gmt: '2026-03-26T20:29:52'
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
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Masquerade Your Backups To Build QA/Testing Environments With MyDumper

Source: [Percona Blog](https://www.percona.com/blog/masquerade-your-backups-to-build-qa-testing-environments-with-mydumper/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2023-04-10T13:59:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

For a long time, MyDumper has been the fastest tool to take Logical Backups. We have been adding several features to expand the use cases. Masquerade was one of these features, but it was only for integer and UUID values. In this blog post, I’m going to present a new functionality that is available in … Continued

## Structure detectee

- H2: How does it work?
- H2: How can we select the column to masquerade?
- H2: New random format function
- H2: Performance considerations
- H3: Baseline backup
- H3: One integer column
- H3: random_format with <number 11>
- H3: random_format with <file> with 100 lines file
- H2: Warning
- H2: Conclusion

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
