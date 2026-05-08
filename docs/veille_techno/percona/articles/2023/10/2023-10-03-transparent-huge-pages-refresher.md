---
title: Transparent Huge Pages Refresher
source:
  name: Percona Blog
  url: https://www.percona.com/blog/transparent-huge-pages-refresher/
  post_id: 27259
source_author:
  name: Brian Sumpter
  slug: brian-sumpter
  url: https://www.percona.com/blog/author/brian-sumpter/
  website: https://www.percona.com
published_at: '2023-10-03T12:16:00'
published_at_gmt: '2023-10-03T12:16:00'
modified_at: '2026-03-26T20:27:09'
modified_at_gmt: '2026-03-26T20:27:09'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/transparent-huge-pages.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Transparent Huge Pages Refresher

Source: [Percona Blog](https://www.percona.com/blog/transparent-huge-pages-refresher/)

Auteur source: [Brian Sumpter](https://www.percona.com/blog/author/brian-sumpter/)

Publication: 2023-10-03T12:16:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Transparent Huge Pages (THP) is a memory management feature in Linux operating systems that aims to enhance system performance. While THP can be beneficial for many applications, enabling it on a database server could have unintended consequences. In this post, we will explore THP, its impact on database servers, and how to disable it for … Continued

## Structure detectee

- H3: What are Transparent Huge Pages?
- H3: Transparent Huge Pages and database servers
- H3: Disabling THP dynamically
- H3: Making the change persistent
- H4: Disabling THP with grub
- H4: Disabling THP with systemd in Ubuntu
- H3: Final thoughts
- H3: Further reading

## Images et graphiques reperes

- featured / image: [Transparent Huge Pages Refresher](https://www.percona.com/wp-content/uploads/2026/03/transparent-huge-pages.jpg)

## Auteur source

Brian joined Percona in 2016 after a successful tenure in the corporate enterprise sector. His professional background encompasses experience in managing sizable deployments of MySQL and Percona XTRADB Cluster. Outside of work, he enjoys spending time with family, playing guitar, and astrophotography. Brian and his wife reside in Tennessee with their miniature dachshund.
